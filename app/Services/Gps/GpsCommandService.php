<?php

namespace App\Services\Gps;

use App\Models\AuditLog;
use App\Models\GpsCommand;
use App\Models\GpsTracker;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GpsCommandService
{
    public function request(GpsTracker $tracker, User $user, string $commandName, array $parameters, string $reason): GpsCommand
    {
        if (!$tracker->integration?->is_active || blank($tracker->integration->commands_endpoint)) {
            throw new \RuntimeException('Perangkat belum memiliki endpoint perintah aktif.');
        }
        return GpsCommand::create([
            'gps_tracker_id' => $tracker->id, 'requested_by' => $user->id, 'command_name' => $commandName,
            'parameters' => $parameters, 'reason' => $reason, 'status' => 'pending_approval', 'idempotency_key' => (string) Str::uuid(),
        ]);
    }

    public function approve(GpsCommand $command, User $reviewer, ?string $note = null): GpsCommand
    {
        if (!in_array($reviewer->role, ['super_admin', 'admin', 'manager'], true)) throw new \RuntimeException('Role ini tidak berwenang menyetujui perintah GPS.');
        if ($command->requested_by === $reviewer->id) throw new \RuntimeException('Pemohon tidak boleh menyetujui perintahnya sendiri.');
        if ($command->status !== 'pending_approval') throw new \RuntimeException('Perintah sudah diproses.');

        return DB::transaction(function () use ($command, $reviewer, $note) {
            $command->update(['status' => 'approved', 'approved_by' => $reviewer->id, 'approved_at' => now(), 'review_note' => $note]);
            AuditLog::create(['user_id' => $reviewer->id, 'action' => 'gps_command_approved', 'auditable_type' => GpsCommand::class, 'auditable_id' => $command->id, 'new_values' => ['command_name' => $command->command_name, 'tracker_id' => $command->gps_tracker_id]]);
            return $command->fresh();
        });
    }

    public function reject(GpsCommand $command, User $reviewer, string $note): GpsCommand
    {
        if (!in_array($reviewer->role, ['super_admin', 'admin', 'manager'], true)) throw new \RuntimeException('Role ini tidak berwenang menolak perintah GPS.');
        $command->update(['status' => 'rejected', 'approved_by' => $reviewer->id, 'review_note' => $note]);
        AuditLog::create(['user_id' => $reviewer->id, 'action' => 'gps_command_rejected', 'auditable_type' => GpsCommand::class, 'auditable_id' => $command->id, 'new_values' => ['reason' => $note]]);
        return $command->fresh();
    }

    public function send(GpsCommand $command): GpsCommand
    {
        if ($command->status !== 'approved') throw new \RuntimeException('Hanya perintah yang disetujui dapat dikirim.');
        $integration = $command->tracker->integration;
        $provider = $integration?->provider;
        if (!$integration || !$provider || blank($provider->base_url) || blank($integration->commands_endpoint)) throw new \RuntimeException('Konfigurasi endpoint perintah tidak lengkap.');

        $request = Http::acceptJson()->timeout(30)->withHeaders($provider->extra_headers ?? [])->withHeader('Idempotency-Key', $command->idempotency_key);
        $secret = $integration->credential_secret ?: $provider->api_key;
        $name = $integration->credential_key_name ?: 'Authorization';
        if ($integration->auth_type === 'bearer') $request = $request->withToken((string) $secret);
        elseif ($integration->auth_type === 'basic') $request = $request->withBasicAuth((string) $name, (string) $secret);
        elseif ($integration->auth_type === 'header') $request = $request->withHeader($name, (string) $secret);

        $payload = ['device_id' => $command->tracker->external_device_id, 'command' => $command->command_name, 'parameters' => $command->parameters ?? []];
        if ($integration->auth_type === 'query') $payload[$name] = $secret;
        $url = rtrim($provider->base_url, '/').'/'.ltrim($integration->commands_endpoint, '/');
        $command->update(['status' => 'queued']);
        try {
            $response = $request->post($url, $payload);
            $response->throw();
            $command->update(['status' => 'sent', 'sent_at' => now(), 'response_body' => mb_substr($response->body(), 0, 65000)]);
        } catch (\Throwable $e) {
            $command->update(['status' => 'failed', 'response_body' => mb_substr($e->getMessage(), 0, 65000)]);
            throw $e;
        }
        return $command->fresh();
    }
}

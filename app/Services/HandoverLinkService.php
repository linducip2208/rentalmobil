<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\RentalOrder;
use App\Models\SecureToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Link tanda tangan kontrak & self check-in berbasis SecureToken (hash SHA-256,
 * kedaluwarsa, bisa dicabut). Customer tidak perlu akun — link dari WhatsApp/email.
 */
class HandoverLinkService
{
    public const SCOPE_CONTRACT_SIGN = 'contract_sign';
    public const SCOPE_ORDER_CHECKIN = 'order_checkin';

    public function issueContractSigning(Contract $contract, int $expiresInDays = 7): string
    {
        $token = SecureToken::generateToken(self::SCOPE_CONTRACT_SIGN, Contract::class, $contract->id, $expiresInDays * 1440, auth()->id());

        return url('/handover/kontrak/'.$token->raw_token);
    }

    public function issueCheckIn(RentalOrder $order, int $expiresInHours = 72): string
    {
        $token = SecureToken::generateToken(self::SCOPE_ORDER_CHECKIN, RentalOrder::class, $order->id, $expiresInHours * 60, auth()->id());

        return url('/handover/checkin/'.$token->raw_token);
    }

    public function findValidToken(string $scope, string $raw): ?SecureToken
    {
        return SecureToken::byScope($scope)
            ->valid()
            ->where('token_hash', hash('sha256', $raw))
            ->first();
    }

    public function signContract(SecureToken $token, string $signatureDataUrl, ?string $ip = null): Contract
    {
        /** @var Contract $contract */
        $contract = $token->reference;

        abort_if($contract->isSigned(), 422, 'Kontrak sudah ditandatangani.');

        return DB::transaction(function () use ($token, $contract, $signatureDataUrl, $ip) {
            $path = 'signatures/'.Str::uuid().'.png';
            Storage::disk('public')->put($path, base64_decode(preg_replace('#^data:image/\w+;base64,#', '', $signatureDataUrl)));

            $payload = collect([
                $contract->contract_number,
                $contract->customer_id,
                $contract->vehicle_id,
                $contract->start_date?->toDateString(),
                $contract->end_date?->toDateString(),
                $contract->total_amount,
                now()->toIso8601String(),
            ])->implode('|');

            $contract->update([
                'customer_signature_url' => $path,
                'document_hash' => hash('sha256', $payload),
                'signed_at' => now(),
                'status' => 'signed',
            ]);

            $token->revoke();

            \Illuminate\Support\Facades\Log::info('Kontrak ditandatangani elektronik', ['contract' => $contract->contract_number, 'ip' => $ip]);

            return $contract;
        });
    }

    public function submitCheckIn(SecureToken $token, array $validated, ?string $ip = null): \App\Models\VehicleInspection
    {
        /** @var RentalOrder $order */
        $order = $token->reference;

        abort_if(in_array($order->status, ['checked_out', 'active', 'completed', 'cancelled'], true), 422, 'Order sudah diserahkan atau selesai.');

        return DB::transaction(function () use ($token, $order, $validated, $ip) {
            $photos = [];

            foreach ($validated['photos'] ?? [] as $photo) {
                $photos[] = $photo->store('inspections', 'public');
            }

            $inspection = \App\Models\VehicleInspection::create([
                'rental_order_id' => $order->id,
                'vehicle_id' => $order->vehicle_id,
                'type' => 'checkout',
                'checklist' => [
                    'fuel_level' => $validated['fuel_level'],
                    'odometer_km' => $validated['odometer_km'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                    'submitted_by' => 'customer_self_service',
                    'ip' => $ip,
                ],
                'photos' => $photos,
                'result' => 'pass',
                'inspected_at' => now(),
            ]);

            $order->update(['status' => 'checked_out']);

            $token->revoke();

            return $inspection;
        });
    }
}

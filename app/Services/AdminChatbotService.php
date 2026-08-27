<?php

namespace App\Services;

use App\Models\AiChatMessage;
use App\Models\Provider;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Chatbot internal admin: jawab pertanyaan operasional dari ringkasan
 * data live (armada, booking, invoice). Provider AI dinamis via BYOK.
 */
class AdminChatbotService
{
    public function ask($user, string $question): AiChatMessage
    {
        $provider = $this->resolveProvider();
        $started = microtime(true);

        $userMessage = AiChatMessage::create([
            'user_id' => $user->id,
            'role' => 'user',
            'content' => $question,
        ]);

        try {
            $context = $this->buildContext();
            $history = AiChatMessage::where('user_id', $user->id)->where('created_at', '>=', now()->subHour())->orderBy('id')->limit(20)->get();

            $messages = [
                ['role' => 'system', 'content' => self::systemPrompt() . "\n\nDATA LIVE:\n" . $context],
            ];

            foreach ($history as $msg) {
                $messages[] = ['role' => $msg->role === 'assistant' ? 'assistant' : 'user', 'content' => str($msg->content)->limit(2000)];
            }

            $config = $provider->config ?? [];
            $url = rtrim((string) $provider->base_url, '/');

            if (!str_ends_with($url, '/chat/completions')) {
                $url .= '/chat/completions';
            }

            $headers = $provider->extra_headers ?? [];

            if ($provider->api_key) {
                $headers[$config['auth_header'] ?? 'Authorization'] = trim(($config['auth_scheme'] ?? 'Bearer') . ' ' . $provider->api_key);
            }

            $response = Http::withHeaders($headers)
                ->timeout((int) ($config['timeout_seconds'] ?? 45))
                ->asJson()
                ->post($url, [
                    'model' => $config['model'] ?? null,
                    'messages' => $messages,
                    'max_tokens' => (int) ($config['max_tokens'] ?? 800),
                    'temperature' => 0.2,
                ]);

            if ($response->failed()) {
                throw new RuntimeException("AI API error (HTTP {$response->status()}): " . str($response->body())->limit(200));
            }

            $answer = (string) data_get($response->json(), 'choices.0.message.content', '');
            $tokens = (int) data_get($response->json(), 'usage.total_tokens', 0);
        } catch (\Throwable $e) {
            return AiChatMessage::create([
                'user_id' => $user->id,
                'role' => 'assistant',
                'content' => '⚠️ Gagal menjawab: ' . str($e->getMessage())->limit(250),
                'provider_id' => $provider->id,
            ]);
        }

        return AiChatMessage::create([
            'user_id' => $user->id,
            'role' => 'assistant',
            'content' => $answer ?: '(jawaban kosong)',
            'tokens_used' => $tokens,
            'latency_ms' => (int) ((microtime(true) - $started) * 1000),
            'model' => $config['model'] ?? null,
            'provider_id' => $provider->id,
        ]);
    }

    protected function buildContext(): string
    {
        $vehicles = \App\Models\Vehicle::query()
            ->selectRaw("status, COUNT(*) as total")
            ->groupBy('status')
            ->pluck('total', 'status');

        $availableVehicles = \App\Models\Vehicle::available()->count();
        $activeOrders = \App\Models\RentalOrder::whereIn('status', ['checked_out', 'active'])->count();
        $overdueOrders = \App\Models\RentalOrder::where('status', 'overdue')->count();
        $pendingBookings = \App\Models\Booking::where('status', 'pending')->count();
        $unpaidInvoices = \App\Models\Invoice::whereIn('status', ['issued', 'partially_paid', 'overdue'])->sum('balance_due');
        $revenueMonth = (float) \App\Models\Payment::where('status', 'verified')
            ->whereBetween('payment_date', [now()->startOfMonth(), now()])
            ->sum('amount');
        $maintenanceDue = \App\Models\ServiceSchedule::whereIn('status', ['pending', 'scheduled'])
            ->where('scheduled_date', '<=', now()->addDays(7))
            ->count();

        return implode("\n", [
            "- Armada per status: " . $vehicles->map(fn ($t, $s) => "{$s}={$t}")->implode(', '),
            "- Unit tersedia: {$availableVehicles}",
            "- Order aktif: {$activeOrders}, overdue: {$overdueOrders}",
            "- Booking menunggu konfirmasi: {$pendingBookings}",
            "- Piutang belum dibayar: Rp" . number_format((float) $unpaidInvoices, 0, ',', '.'),
            "- Revenue bulan ini (verified): Rp" . number_format($revenueMonth, 0, ',', '.'),
            "- Servis jatuh tempo 7 hari ke depan: {$maintenanceDue}",
            "- Waktu server: " . now()->format('d/m/Y H:i'),
        ]);
    }

    public static function systemPrompt(): string
    {
        return <<<'PROMPT'
Kamu adalah asisten operasional sistem rental kendaraan Indonesia. Jawab PERTANYAAN USER
berdasarkan DATA LIVE yang diberikan di system message — jangan mengarang angka.
Jawab singkat, padat, bahasa Indonesia, format rapi. Jika data tidak tersedia untuk
pertanyaan user, katakan apa yang bisa kamu bantu dari data yang ada.
PROMPT;
    }

    protected function resolveProvider(): Provider
    {
        $settingId = SystemSetting::get('admin_chatbot_provider_id');

        $provider = $settingId
            ? Provider::find((int) $settingId)
            : Provider::where('type', 'ai')->where('is_active', true)->first();

        if (!$provider || !$provider->is_active) {
            throw new RuntimeException('Belum ada provider AI aktif. Atur di menu Provider (BYOK).');
        }

        return $provider;
    }
}

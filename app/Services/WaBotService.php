<?php

namespace App\Services;

use App\Models\WaConversation;
use App\Models\WaMessage;
use App\Models\Vehicle;
use App\Models\Location;
use App\Models\Category;
use App\Models\SystemSetting;

/**
 * WhatsApp booking bot: state machine sederhana
 * greeting → tanya tanggal → pilih unit → quote → serahkan ke CS.
 * Integrasi provider WA (webhook masuk) via route /webhooks/wa-bot.
 */
class WaBotService
{
    public function handleIncoming(string $phone, string $message, ?string $name = null, array $payload = []): array
    {
        $conversation = WaConversation::updateOrCreate(
            ['phone' => $this->normalizePhone($phone)],
            [
                'name' => $name,
                'last_message_at' => now(),
            ]
        );

        WaMessage::create([
            'wa_conversation_id' => $conversation->id,
            'direction' => 'inbound',
            'body' => $message,
            'payload' => $payload,
        ]);

        if ($conversation->is_handed_over) {
            return $this->reply($conversation, null); // Sudah ditangani CS manusia — bot diam.
        }

        $reply = $this->route($conversation, trim($message));

        if ($reply !== null) {
            WaMessage::create([
                'wa_conversation_id' => $conversation->id,
                'direction' => 'outbound',
                'body' => $reply,
            ]);
        }

        $conversation->touch();

        return [
            'conversation_id' => $conversation->id,
            'reply' => $reply,
            'state' => $conversation->state,
        ];
    }

    protected function route(WaConversation $conversation, string $message): ?string
    {
        return match ($conversation->state) {
            'greeting' => $this->handleGreeting($conversation, $message),
            'ask_dates' => $this->handleDates($conversation, $message),
            'show_options' => $this->handleOptionSelection($conversation, $message),
            default => $this->handleGreeting($conversation, $message),
        };
    }

    protected function handleGreeting(WaConversation $conversation, string $message): string
    {
        $greeting = SystemSetting::get('wa_bot_greeting', "Halo! Selamat datang di layanan rental kami. 🚗");

        if ($this->wantsHuman($message)) {
            $this->handOver($conversation);

            return "Baik, kami hubungkan dengan tim customer service kami. Mohon tunggu sebentar. 🙏";
        }

        $conversation->update(['state' => 'ask_dates', 'context' => []]);

        return "{$greeting}\n\nUntuk cek ketersediaan & harga, silakan tulis tanggal sewa Anda.\nContoh: 25/08 - 28/08";
    }

    protected function handleDates(WaConversation $conversation, string $message): string
    {
        if ($this->wantsHuman($message)) {
            $this->handOver($conversation);

            return "Baik, kami hubungkan dengan tim customer service kami. 🙏";
        }

        $dates = $this->parseDateRange($message);

        if (!$dates) {
            return "Format tanggal belum tepat. Coba tulis seperti ini ya:\n*25/08 - 28/08*\n\nAtau ketik *CS* untuk bicara dengan staf kami.";
        }

        [$start, $end] = $dates;
        $available = Vehicle::query()->active()->available()->limit(5)->get();
        $options = [];

        foreach ($available as $index => $vehicle) {
            $quote = app(PricingEngine::class)->calculateRentalPrice(
                $vehicle,
                $start->toDateString(),
                $end->toDateString()
            );

            $options[] = ($index + 1) . '. ' . $vehicle->name . ' — Rp' . number_format((float) $quote['total'], 0, ',', '.') . ' (Rp' . number_format((float) $quote['daily_rate'], 0, ',', '.') . '/hari)';
        }

        $conversation->update([
            'state' => 'show_options',
            'context' => [
                'start_date' => $start->format('Y-m-d'),
                'end_date' => $end->format('Y-m-d'),
                'option_vehicle_ids' => $available->pluck('id')->all(),
            ],
        ]);

        if ($options === []) {
            return "Mohon maaf, saat ini semua unit sedang terpakai untuk tanggal tersebut. 😢\nKetik *CS* jika ingin bantuan lebih lanjut.";
        }

        return "Berikut opsi unit tersedia {$start->format('d/m')} – {$end->format('d/m')}:\n\n" .
            implode("\n", $options) .
            "\n\nBalas nomor unit untuk detail & booking, atau ketik *CS*.";
    }

    protected function handleOptionSelection(WaConversation $conversation, string $message): string
    {
        $context = $conversation->context ?? [];
        $vehicleIds = $context['option_vehicle_ids'] ?? [];
        $choice = (int) preg_replace('/[^0-9]/', '', $message);

        if ($choice < 1 || $choice > count($vehicleIds)) {
            if ($this->wantsHuman($message)) {
                $this->handOver($conversation);

                return "Baik, kami hubungkan dengan tim customer service kami. 🙏";
            }

            return "Silakan balas dengan nomor unit (contoh: *1*), atau ketik *CS*.";
        }

        $vehicleId = $vehicleIds[$choice - 1];
        $bookingUrl = config('app.url') . '/booking?vehicle_id=' . $vehicleId .
            '&start=' . ($context['start_date'] ?? '') . '&end=' . ($context['end_date'] ?? '');

        $conversation->update(['state' => 'completed']);

        return "Pilihan bagus! ✨\n\nLanjutkan booking di sini:\n{$bookingUrl}\n\nAtau ketik ulang tanggal lain untuk cek unit lagi. Terima kasih! 🙏";
    }

    protected function handOver(WaConversation $conversation): void
    {
        $conversation->update(['is_handed_over' => true]);
    }

    protected function wantsHuman(string $message): bool
    {
        return (bool) preg_match('/^(cs|admin|orang|staf|staff|operator|manusia)$/i', trim($message));
    }

    /**
     * Parse rentang tanggal umum: "25/08 - 28/08", "2026-08-25 s/d 2026-08-28".
     */
    protected function parseDateRange(string $input): ?array
    {
        $patterns = [
            '/(\d{1,2}\/\d{1,2}(?:\/\d{2,4})?)\s*(?:-|–|s\/d|sampai|to)\s*(\d{1,2}\/\d{1,2}(?:\/\d{2,4})?)/i',
            '/(\d{4}-\d{2}-\d{2})\s*(?:-|–|s\/d|sampai|to)\s*(\d{4}-\d{2}-\d{2})/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input, $m)) {
                try {
                    [$startValue, $startFormat] = $this->normalizeDateToken($m[1]);
                    [$endValue, $endFormat] = $this->normalizeDateToken($m[2], $startValue);

                    $start = \Carbon\Carbon::createFromFormat($startFormat, trim($startValue));
                    $end = \Carbon\Carbon::createFromFormat($endFormat, trim($endValue));

                    if ($end->lessThanOrEqualTo($start)) {
                        return null;
                    }

                    return [$start->startOfDay(), $end->startOfDay()];
                } catch (\Throwable) {
                    continue;
                }
            }
        }

        return null;
    }

    /**
     * Token "25/08" → "25/08/2026" (d/m/Y); token lengkap dipass-through.
     */
    protected function normalizeDateToken(string $token, ?string $referenceToken = null): array
    {
        $token = trim($token);

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $token)) {
            return [$token, 'Y-m-d'];
        }

        $parts = explode('/', $token);

        if (count($parts) === 3) {
            return [$token, 'd/m/Y'];
        }

        if (count($parts) === 2) {
            $refYear = null;

            if ($referenceToken && preg_match('/^(\d{1,2})\/(\d{1,2})(?:\/(\d{2,4}))?$/', $referenceToken, $rm)) {
                // Bulan end < bulan start & tanpa tahun → kemungkinan lintas tahun.
                $refYear = now()->year;
            } else {
                $refYear = now()->year;
            }

            return ["{$parts[0]}/{$parts[1]}/{$refYear}", 'd/m/Y'];
        }

        return [$token, 'Y-m-d'];
    }

    protected function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/[^0-9+]/', '', $phone);

        if (str_starts_with($digits, '+62')) {
            return substr($digits, 1);
        }

        if (str_starts_with($digits, '0')) {
            return '62' . substr($digits, 1);
        }

        return $digits;
    }

    protected function reply(WaConversation $conversation, ?string $body): array
    {
        return [
            'conversation_id' => $conversation->id,
            'reply' => $body,
            'state' => $conversation->state,
        ];
    }
}

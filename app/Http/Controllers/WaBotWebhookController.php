<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use App\Services\WaBotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Webhook WhatsApp bot (BYOK): provider WA dinamis mengirim pesan masuk
 * ke endpoint ini. Format payload dipetakan lewat SystemSetting "wa_bot_webhook_map":
 * {"phone_path": "from", "message_path": "body", "name_path": "profile.name"}
 * Balasan dikembalikan sebagai JSON agar bisa diteruskan provider, atau
 * dikirim via NotificationDispatcher sesuai konfigurasi.
 */
class WaBotWebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $payload = $request->all();

        $map = config('wa_bot.webhook_map') ?? SystemSetting::get('wa_bot_webhook_map');
        $map = is_string($map) ? json_decode($map, true) : ($map ?? []);

        $phone = data_get($payload, $map['phone_path'] ?? 'from');
        $message = data_get($payload, $map['message_path'] ?? 'body');
        $name = data_get($payload, $map['name_path'] ?? 'profile.name');

        if (blank($phone) || blank($message)) {
            return response()->json(['ok' => false, 'error' => 'Payload tidak memuat nomor & pesan.'], 422);
        }

        // Idempotensi sederhana: event id dari provider.
        $eventId = data_get($payload, $map['event_id_path'] ?? 'id');
        $cacheKey = 'wa_bot_event_'.md5((string) $eventId);

        if ($eventId && cache()->has($cacheKey)) {
            return response()->json(['ok' => true, 'duplicate' => true]);
        }

        $result = app(WaBotService::class)->handleIncoming(
            (string) $phone,
            (string) $message,
            $name ? (string) $name : null,
            $payload
        );

        if ($eventId) {
            cache()->put($cacheKey, true, now()->addHours(24));
        }

        return response()->json([
            'ok' => true,
            'reply' => $result['reply'],
            'state' => $result['state'],
        ]);
    }
}

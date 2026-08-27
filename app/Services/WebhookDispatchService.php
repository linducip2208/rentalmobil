<?php

namespace App\Services;

use App\Models\Webhook;
use App\Models\WebhookDelivery;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookDispatchService
{
    /** Kirim event ke semua webhook yang berlangganan. Aman dipanggil di mana saja. */
    public function dispatch(string $event, array $payload): int
    {
        $webhooks = Webhook::forEvent($event)->get();
        $queued = 0;

        foreach ($webhooks as $webhook) {
            $delivery = WebhookDelivery::create([
                'webhook_id' => $webhook->id,
                'event' => $event,
                'payload' => $payload,
                'status' => 'pending',
                'next_retry_at' => now(),
            ]);

            $this->attempt($delivery);
            $queued++;
        }

        return $queued;
    }

    public function attempt(WebhookDelivery $delivery): bool
    {
        $webhook = $delivery->webhook;

        if (! $webhook || ! $webhook->is_active) {
            return false;
        }

        $body = json_encode([
            'event' => $delivery->event,
            'sent_at' => now()->toIso8601String(),
            'data' => $delivery->payload,
        ]);

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-Webhook-Event' => $delivery->event,
                'X-Webhook-Signature' => hash_hmac('sha256', $body, (string) $webhook->secret),
            ])
                ->timeout(10)
                ->throw()
                ->post($webhook->url, json_decode($body, true));

            $delivery->update([
                'status' => 'delivered',
                'attempts' => $delivery->attempts + 1,
                'response_code' => $response->status(),
                'response_body' => str($response->body())->limit(500),
                'delivered_at' => now(),
                'next_retry_at' => null,
            ]);

            $webhook->markTriggered();

            return true;
        } catch (\Throwable $e) {
            $attempts = $delivery->attempts + 1;
            $maxAttempts = max(1, (int) ($delivery->max_attempts ?: 5));
            $failed = $attempts >= $maxAttempts;

            $delivery->update([
                'status' => $failed ? 'failed' : 'pending',
                'attempts' => $attempts,
                'response_code' => null,
                'error_note' => str($e->getMessage())->limit(490),
                // Retry pertama boleh segera diproses oleh worker; percobaan berikutnya
                // memakai linear backoff agar endpoint yang down tidak dibanjiri request.
                'next_retry_at' => $failed ? null : ($attempts === 1 ? now() : now()->addMinutes(5 * $attempts)),
            ]);

            Log::warning("Webhook delivery #{$delivery->id} gagal (percobaan {$attempts}): ".str($e->getMessage())->limit(150));

            return false;
        }
    }

    public function retryFailed(): int
    {
        $retried = 0;

        WebhookDelivery::where('status', 'pending')
            ->whereNotNull('next_retry_at')
            ->where('next_retry_at', '<=', now()->addSecond())
            ->chunkById(50, function ($deliveries) use (&$retried) {
                foreach ($deliveries as $delivery) {
                    $this->attempt($delivery);
                    $retried++;
                }
            });

        return $retried;
    }
}

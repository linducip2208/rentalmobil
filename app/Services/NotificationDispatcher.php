<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\NotificationQueue;
use App\Models\NotificationTemplate;
use App\Models\Provider;
use App\Models\RentalOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class NotificationDispatcher
{
    public function dispatch(string $event, Model $notifiable, array $data = []): ?NotificationQueue
    {
        $template = NotificationTemplate::active()
            ->byEventType($event)
            ->first();

        if (!$template) {
            Log::warning("Notification template not found for event: {$event}");
            return null;
        }

        $provider = $template->provider;
        if (!$provider) {
            Log::warning("No provider configured for template: {$template->name}");
            return null;
        }

        $renderedSubject = $this->renderTemplate($template->subject, $data);
        $renderedBody = $this->renderTemplate($template->body, $data);

        $notification = NotificationQueue::create([
            'provider_id' => $provider->id,
            'template_id' => $template->id,
            'notifiable_type' => get_class($notifiable),
            'notifiable_id' => $notifiable->id,
            'channel' => $template->channel,
            'subject' => $renderedSubject,
            'body' => $renderedBody,
            'event_type' => $event,
            'payload' => array_merge($data, [
                'event' => $event,
                'notifiable_name' => $notifiable->name ?? $notifiable->email ?? 'Unknown',
            ]),
            'status' => 'pending',
            'scheduled_at' => now(),
            'attempts' => 0,
            'max_attempts' => 3,
        ]);

        return $notification;
    }

    public function dispatchBatch(string $event, array $notifiables, array $sharedData = []): int
    {
        $count = 0;

        foreach ($notifiables as $notifiable) {
            $result = $this->dispatch($event, $notifiable, $sharedData);
            if ($result) {
                $count++;
            }
        }

        return $count;
    }

    public function sendBookingConfirmation(\App\Models\Booking $booking): ?NotificationQueue
    {
        $customer = $booking->customer()->with('user')->first();

        if (!$customer || !$customer->phone) {
            Log::warning("Cannot send booking confirmation: no phone for customer #{$booking->customer_id}");
            return null;
        }

        return $this->dispatch('booking_confirmation', $customer, [
            'booking_number' => $booking->booking_number,
            'customer_name' => $customer->name,
            'vehicle_name' => $booking->vehicle->name ?? 'N/A',
            'start_date' => $booking->start_date->format('d M Y H:i'),
            'end_date' => $booking->end_date->format('d M Y H:i'),
            'total_amount' => number_format($booking->total_amount, 0, ',', '.'),
            'deposit_amount' => number_format($booking->deposit_amount, 0, ',', '.'),
            'pickup_location' => $booking->pickupLocation->name ?? 'N/A',
        ]);
    }

    public function sendReturnReminder(RentalOrder $order): ?NotificationQueue
    {
        $customer = $order->customer()->with('user')->first();

        if (!$customer || !$customer->phone) {
            Log::warning("Cannot send return reminder: no phone for customer #{$order->customer_id}");
            return null;
        }

        $hoursUntilReturn = now()->diffInHours($order->end_date, false);

        return $this->dispatch('return_reminder', $customer, [
            'order_number' => $order->order_number,
            'customer_name' => $customer->name,
            'vehicle_name' => $order->vehicle->name ?? 'N/A',
            'return_date' => $order->end_date->format('d M Y H:i'),
            'hours_until' => max(0, (int) $hoursUntilReturn),
            'late_fee_per_hour' => number_format((float) $order->vehicle->late_fee_per_hour, 0, ',', '.'),
        ]);
    }

    public function sendPaymentReminder(Invoice $invoice): ?NotificationQueue
    {
        $customer = $invoice->customer()->with('user')->first();

        if (!$customer || !$customer->phone) {
            Log::warning("Cannot send payment reminder: no phone for customer #{$invoice->customer_id}");
            return null;
        }

        $balanceDue = (float) $invoice->total_amount - (float) $invoice->amount_paid;

        return $this->dispatch('payment_reminder', $customer, [
            'invoice_number' => $invoice->invoice_number,
            'customer_name' => $customer->name,
            'order_number' => $invoice->rentalOrder->order_number ?? 'N/A',
            'total_amount' => number_format($invoice->total_amount, 0, ',', '.'),
            'amount_paid' => number_format($invoice->amount_paid, 0, ',', '.'),
            'balance_due' => number_format($balanceDue, 0, ',', '.'),
            'due_date' => $invoice->due_date->format('d M Y'),
        ]);
    }

    public function sendOverdueNotice(RentalOrder $order): ?NotificationQueue
    {
        $customer = $order->customer()->with('user')->first();

        if (!$customer || !$customer->phone) {
            Log::warning("Cannot send overdue notice: no phone for customer #{$order->customer_id}");
            return null;
        }

        $hoursLate = now()->diffInHours($order->end_date);
        $estimatedFee = $hoursLate * (float) $order->vehicle->late_fee_per_hour;

        return $this->dispatch('overdue_notice', $customer, [
            'order_number' => $order->order_number,
            'customer_name' => $customer->name,
            'vehicle_name' => $order->vehicle->name ?? 'N/A',
            'end_date' => $order->end_date->format('d M Y H:i'),
            'hours_late' => $hoursLate,
            'estimated_fee' => number_format($estimatedFee, 0, ',', '.'),
            'late_fee_per_hour' => number_format((float) $order->vehicle->late_fee_per_hour, 0, ',', '.'),
        ]);
    }

    public function processQueue(int $batchSize = 50): array
    {
        $pending = NotificationQueue::pending()
            ->where('scheduled_at', '<=', now())
            ->orderBy('scheduled_at')
            ->limit($batchSize)
            ->get();

        $results = ['sent' => 0, 'failed' => 0];

        foreach ($pending as $notification) {
            try {
                $this->processNotification($notification);
                $results['sent']++;
            } catch (\Exception $e) {
                Log::error("Failed to send notification #{$notification->id}: {$e->getMessage()}");
                $notification->update([
                    'status' => 'failed',
                    'failed_at' => now(),
                    'error_message' => $e->getMessage(),
                    'attempts' => $notification->attempts + 1,
                ]);
                $results['failed']++;
            }
        }

        return $results;
    }

    public function retryFailed(): int
    {
        $retryable = NotificationQueue::retryable()->get();
        $retried = 0;

        foreach ($retryable as $notification) {
            try {
                $this->processNotification($notification);
                $retried++;
            } catch (\Exception $e) {
                Log::error("Retry failed for notification #{$notification->id}: {$e->getMessage()}");
                $notification->update([
                    'attempts' => $notification->attempts + 1,
                    'error_message' => $e->getMessage(),
                ]);
            }
        }

        return $retried;
    }

    protected function processNotification(NotificationQueue $notification): void
    {
        $provider = $notification->provider;

        if (!$provider) {
            throw new \RuntimeException("Provider not found for notification #{$notification->id}");
        }

        $channel = $notification->channel;

        match ($channel) {
            'whatsapp' => $this->sendViaWhatsApp($notification, $provider),
            'sms' => $this->sendViaSms($notification, $provider),
            'email' => $this->sendViaEmail($notification, $provider),
            'telegram' => $this->sendViaTelegram($notification, $provider),
            default => throw new \RuntimeException("Unsupported channel: {$channel}"),
        };

        $notification->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    protected function sendViaWhatsApp(NotificationQueue $notification, Provider $provider): void
    {
        $notifiable = $notification->notifiable;
        $phone = $notifiable->phone ?? null;

        if (!$phone) {
            throw new \RuntimeException("No phone number for WhatsApp notification");
        }

        $payload = [
            'phone' => $phone,
            'message' => $notification->body,
        ];

        $this->callProviderApi($provider, $payload);
    }

    protected function sendViaSms(NotificationQueue $notification, Provider $provider): void
    {
        $notifiable = $notification->notifiable;
        $phone = $notifiable->phone ?? null;

        if (!$phone) {
            throw new \RuntimeException("No phone number for SMS notification");
        }

        $payload = [
            'phone' => $phone,
            'message' => $notification->body,
        ];

        $this->callProviderApi($provider, $payload);
    }

    protected function sendViaEmail(NotificationQueue $notification, Provider $provider): void
    {
        $notifiable = $notification->notifiable;
        $email = $notifiable->email ?? null;

        if (!$email) {
            throw new \RuntimeException("No email address for email notification");
        }

        $payload = [
            'email' => $email,
            'subject' => $notification->subject,
            'body' => $notification->body,
        ];

        $this->callProviderApi($provider, $payload);
    }

    protected function sendViaTelegram(NotificationQueue $notification, Provider $provider): void
    {
        $notifiable = $notification->notifiable;
        $chatId = $notifiable->telegram_chat_id ?? null;

        if (!$chatId) {
            throw new \RuntimeException("No Telegram chat ID for notification");
        }

        $payload = [
            'chat_id' => $chatId,
            'text' => $notification->body,
        ];

        $this->callProviderApi($provider, $payload);
    }

    protected function callProviderApi(Provider $provider, array $payload): void
    {
        $baseUrl = $provider->base_url;
        $apiKey = $provider->api_key;
        $config = $provider->config ?? [];

        if (blank($baseUrl)) {
            throw new \RuntimeException('Provider base URL belum dikonfigurasi.');
        }

        $headers = $provider->extra_headers ?? [];
        if (filled($apiKey)) {
            $authHeader = $config['auth_header'] ?? 'Authorization';
            $authScheme = $config['auth_scheme'] ?? 'Bearer';
            $headers[$authHeader] = trim("{$authScheme} {$apiKey}");
        }

        $fieldMap = $config['payload_map'] ?? [];
        if (is_array($fieldMap) && $fieldMap !== []) {
            $mapped = [];
            foreach ($fieldMap as $target => $source) {
                $mapped[$target] = $payload[$source] ?? $source;
            }
            $payload = $mapped;
        }

        $request = Http::withHeaders($headers)->timeout((int) ($config['timeout_seconds'] ?? 30));
        $response = ($provider->api_format === 'rest_form')
            ? $request->asForm()->post($baseUrl, $payload)
            : $request->asJson()->post($baseUrl, $payload);

        if ($response->failed()) {
            throw new \RuntimeException("API error (HTTP {$response->status()}): {$response->body()}");
        }
    }

    protected function renderTemplate(string $template, array $data): string
    {
        foreach ($data as $key => $value) {
            $template = str_replace("{{$key}}", (string) $value, $template);
        }
        return $template;
    }
}

<?php

namespace App\Services;

use App\Models\EinvoiceSubmission;
use App\Models\Invoice;
use App\Models\Provider;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * e-Faktur: format-based adapter via Provider dinamis (type 'einvoice').
 * Payload & endpoint dikonfigurasi pemilik aplikasi di admin Providers.
 * Tanpa provider aktif → submission tersimpan sebagai draft siap kirim manual.
 */
class EinvoiceService
{
    public function submit(Invoice $invoice, ?int $providerId = null): EinvoiceSubmission
    {
        $submission = EinvoiceSubmission::create([
            'invoice_id' => $invoice->id,
            'provider_id' => $providerId ?? $this->resolveProviderId(),
            'status' => 'draft',
            'payload' => $this->buildPayload($invoice),
        ]);

        if (! $submission->provider_id) {
            return $submission; // Draft tanpa provider — tim finance kirim manual ke DJP.
        }

        try {
            $provider = Provider::findOrFail($submission->provider_id);
            $config = $provider->config ?? [];

            $response = Http::withHeaders($this->headers($provider))
                ->timeout((int) ($config['timeout_seconds'] ?? 30))
                ->asJson()
                ->post($provider->base_url, $submission->payload);

            $body = $response->json() ?: [];

            $accepted = in_array(
                (string) data_get($body, $config['success_status_path'] ?? 'status'),
                array_map('strval', (array) ($config['success_values'] ?? ['accepted', 'success'])),
                true
            );

            $submission->update([
                'submission_ref' => data_get($body, $config['reference_path'] ?? 'ref'),
                'response' => $body,
                'status' => $accepted ? 'submitted' : 'rejected',
                'submitted_at' => now(),
            ]);
        } catch (\Throwable $e) {
            $submission->update([
                'status' => 'rejected',
                'response' => ['error' => str($e->getMessage())->limit(300)],
            ]);

            throw new RuntimeException('Gagal submit e-Faktur: '.str($e->getMessage())->limit(150));
        }

        return $submission->refresh();
    }

    protected function buildPayload(Invoice $invoice): array
    {
        return [
            'transaction_date' => $invoice->issue_date?->format('d/m/Y') ?? now()->format('d/m/Y'),
            'customer_npwp' => $invoice->customer?->npwp,
            'customer_name' => $invoice->customer?->company_name ?? $invoice->customer?->name,
            'amount_excl_tax' => round((float) $invoice->subtotal, 2),
            'tax_amount' => round((float) $invoice->tax_amount, 2),
            'total' => round((float) $invoice->total, 2),
            'currency' => $invoice->currency ?? 'IDR',
            'line_description' => 'Sewa kendaraan — '.($invoice->invoice_number ?? ''),
        ];
    }

    protected function resolveProviderId(): ?int
    {
        $setting = SystemSetting::get('einvoice_provider_id');

        return $setting ? (int) $setting : null;
    }

    protected function headers(Provider $p): array
    {
        $h = $p->extra_headers ?? [];

        if ($p->api_key) {
            $h[$p->config['auth_header'] ?? 'Authorization'] = trim(($p->config['auth_scheme'] ?? 'Bearer').' '.$p->api_key);
        }

        return $h;
    }
}

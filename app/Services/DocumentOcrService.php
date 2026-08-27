<?php

namespace App\Services;

use App\Models\DocumentOcrResult;
use App\Models\Provider;
use App\Models\SystemSetting;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * OCR dokumen (STNK, SIM, KTP): ekstrak field penting via provider
 * vision dinamis. Hasil bisa dipakai auto-fill tanggal kedaluwarsa.
 */
class DocumentOcrService
{
    public function extract(object $documentable, string $filePath, string $kind = 'stnk', ?int $providerId = null): DocumentOcrResult
    {
        $providerId ??= $this->resolveProviderId();
        $provider = $providerId ? Provider::find($providerId) : null;

        abort_unless($provider && $provider->is_active, 422, 'Provider AI untuk OCR belum dikonfigurasi.');

        $result = DocumentOcrResult::create([
            'documentable_type' => $documentable->getMorphClass(),
            'documentable_id' => $documentable->id,
            'provider_id' => $provider->id,
            'document_kind' => $kind,
            'status' => 'pending',
        ]);

        try {
            $analysis = app(AiVisionService::class)->analyzeImages(
                $provider,
                [$this->toStoragePath($filePath)],
                self::promptForKind($kind)
            );

            $extracted = is_array($analysis) ? ($analysis[0] ?? $analysis) : [];
            $confidence = (float) data_get($extracted, 'confidence', 0);

            $result->update([
                'extracted' => $extracted,
                'confidence' => round($confidence * 100, 2),
                'status' => 'completed',
                'raw_response' => $analysis,
            ]);
        } catch (\Throwable $e) {
            $result->update([
                'status' => 'failed',
                'raw_response' => ['error' => str($e->getMessage())->limit(300)],
            ]);

            throw $e;
        }

        return $result->refresh();
    }

    /**
     * Terapkan hasil OCR ke model target (mis. stnk_due_date di Vehicle).
     */
    public function applyExtracted(DocumentOcrResult $result): array
    {
        if ($result->status !== 'completed') {
            return ['applied' => false];
        }

        $target = $result->documentable;
        $data = $result->extracted ?? [];

        if (! $target || empty($data)) {
            return ['applied' => false];
        }

        $updates = [];

        foreach (['stnk_due_date', 'tax_due_date', 'kir_due_date'] as $field) {
            $value = data_get($data, str_replace('_due_date', '_expiry', $field));

            if ($value && strtotime((string) $value)) {
                $updates[$field] = Carbon::parse($value)->toDateString();
            }
        }

        if ($target instanceof Vehicle && $updates !== []) {
            $target->update($updates);
        }

        return ['applied' => true, 'fields_updated' => array_keys($updates)];
    }

    public static function promptForKind(string $kind): string
    {
        $fieldMap = [
            'stnk' => 'nomor_stnk, nama_pemilik, nomor_polisi, merek_tipe, berlaku_sampai (stnk_expiry), tahun',
            'sim' => 'nama, nomor_sim, jenis_sim, berlaku_sampai (sim_expiry)',
            'ktp' => 'nik, nama, tanggal_lahir, alamat',
        ];

        $fields = $fieldMap[$kind] ?? 'semua field penting yang terlihat';

        return <<<PROMPT
Baca dokumen Indonesia ini dan ekstrak datanya.
Ekstrak field: {$fields}.
Tanggal dalam format YYYY-MM-DD.

Balas HANYA JSON object tanpa teks lain:
{"nomor_stnk":"...", "stnk_expiry":"2027-05-31", ..., "confidence":0.92}
PROMPT;
    }

    protected function toStoragePath(string $path): string
    {
        $cleaned = ltrim(str_replace(['/storage/', config('app.url')], '', $path), '/');

        if (! Storage::disk('public')->exists($cleaned)) {
            abort(422, "File dokumen tidak ditemukan: {$cleaned}");
        }

        return $cleaned;
    }

    protected function resolveProviderId(): ?int
    {
        $setting = SystemSetting::get('ocr_provider_id');

        return $setting ? (int) $setting : null;
    }
}

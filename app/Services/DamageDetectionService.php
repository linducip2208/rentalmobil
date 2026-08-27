<?php

namespace App\Services;

use App\Models\DamageReport;
use App\Models\Provider;
use App\Models\VehicleInspection;
use Illuminate\Support\Facades\Log;

class DamageDetectionService
{
    public function __construct(protected AiVisionService $vision) {}

    public function analyze(VehicleInspection $inspection, ?Provider $provider = null): VehicleInspection
    {
        $provider ??= Provider::active()->byType('ai')->first();

        if (! $provider) {
            throw new \RuntimeException('Belum ada provider AI aktif. Tambahkan di menu Providers.');
        }

        $photos = $inspection->photos ?? [];

        if ($photos === []) {
            throw new \RuntimeException('Inspeksi ini belum punya foto untuk dianalisis.');
        }

        $inspection->update(['ai_status' => 'processing']);

        try {
            $findings = $this->vision->analyzeImages($provider, $photos, AiVisionService::damagePrompt());

            $inspection->update([
                'ai_status' => 'done',
                'ai_analysis' => $findings,
                'ai_analyzed_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error("AI damage analysis failed for inspection #{$inspection->id}: {$e->getMessage()}");

            $inspection->update(['ai_status' => 'failed', 'ai_analyzed_at' => now()]);

            throw $e;
        }

        return $inspection->fresh();
    }

    /** Buat draft DamageReport dari setiap temuan AI ber-severity moderate ke atas. */
    public function createDraftReportsFromAnalysis(VehicleInspection $inspection): int
    {
        abort_if($inspection->ai_status !== 'done', 422, 'Analisis AI belum selesai.');

        $created = 0;

        foreach (($inspection->ai_analysis ?? []) as $finding) {
            if (! is_array($finding) || isset($finding['raw_text'])) {
                continue;
            }

            $severity = in_array($finding['severity'] ?? '', ['minor', 'moderate', 'major', 'critical'], true)
                ? $finding['severity']
                : 'minor';

            DamageReport::create([
                'rental_order_id' => $inspection->rental_order_id,
                'vehicle_id' => $inspection->vehicle_id,
                'reported_by' => auth()->id(),
                'damage_type' => $finding['damage_type'] ?? 'scratch',
                'location_on_vehicle' => $finding['location_on_vehicle'] ?? 'Tidak dispesifikkan',
                'severity' => $severity,
                'description' => '[AI] '.($finding['description'] ?? ''),
                'estimated_cost' => (float) ($finding['estimated_cost_idr'] ?? 0),
                'photos' => $inspection->photos,
                'status' => 'reported',
                'notes' => 'Draft otomatis dari analisis AI inspeksi #'.$inspection->id,
            ]);

            $created++;
        }

        return $created;
    }
}

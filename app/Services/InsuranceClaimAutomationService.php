<?php

namespace App\Services;

use App\Models\DamageReport;
use App\Models\InsuranceClaim;
use App\Models\PoliceReport;

/**
 * Otomatisasi klaim asuransi: dari damage report (+ police report bila ada),
 * kumpulkan dokumen & hitung jumlah klaim, lalu siapkan draft klaim.
 */
class InsuranceClaimAutomationService
{
    public function fileFromDamageReport(DamageReport $report): InsuranceClaim
    {
        $existing = InsuranceClaim::where('damage_report_id', $report->id)->first();

        if ($existing) {
            return $existing;
        }

        $policy = \App\Models\InsurancePolicy::where('vehicle_id', $report->vehicle_id)
            ->where('status', 'active')
            ->orderByDesc('start_date')
            ->first();

        abort_unless($policy, 422, 'Tidak ada polis asuransi aktif untuk kendaraan ini.');

        $policeReport = PoliceReport::where('rental_order_id', $report->rental_order_id)->first();

        $documents = collect([
            'damage_report_photos' => $report->photos ?? [],
            'police_report_documents' => $policeReport?->documents ?? [],
        ])->filter()->all();

        $filedAmount = min(
            (float) ($report->actual_cost ?? $report->estimated_cost ?? 0),
            (float) $policy->max_claim
        );

        $claim = InsuranceClaim::create([
            'insurance_policy_id' => $policy->id,
            'damage_report_id' => $report->id,
            'police_report_id' => $policeReport?->id,
            'incident_date' => $report->created_at->toDateString(),
            'filed_amount' => round($filedAmount, 2),
            'status' => 'draft',
            'documents' => $documents,
            'notes' => "Auto-generated dari damage report #{$report->id}. Polis: {$policy->policy_number} ({$policy->provider_name}).",
        ]);

        app(NotificationDispatcher::class)->dispatch('insurance_claim_drafted', $claim, [
            'claim_number' => $claim->claim_number,
            'amount' => $claim->filed_amount,
            'policy_number' => $policy->policy_number,
        ]);

        return $claim;
    }

    public function submit(InsuranceClaim $claim): InsuranceClaim
    {
        abort_unless(in_array($claim->status, ['draft']), 422, 'Klaim sudah disubmit.');

        $claim->update(['status' => 'submitted', 'submitted_at' => now()]);

        return $claim->refresh();
    }

    public function decide(InsuranceClaim $claim, string $status, ?float $approvedAmount = null): InsuranceClaim
    {
        abort_unless(in_array($status, ['approved', 'rejected', 'paid']), 422, 'Status keputusan tidak valid.');

        $claim->update([
            'status' => $status,
            'approved_amount' => $approvedAmount ?? $claim->approved_amount,
            'decided_at' => now(),
        ]);

        return $claim->refresh();
    }
}

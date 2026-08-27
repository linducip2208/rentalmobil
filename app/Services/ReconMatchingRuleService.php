<?php

namespace App\Services;

use App\Models\BankStatementLine;
use App\Models\Payment;
use App\Models\ReconMatchingRule;
use App\Models\SystemSetting;
use Illuminate\Support\Collection;

/**
 * Matching rules untuk rekonsiliasi bank: aturan bisa diajar admin
 * (field + operator + nilai). Aturan auto_match menandai kecocokan
 * otomatis; sisanya jadi saran dengan confidence score.
 */
class ReconMatchingRuleService
{
    public function suggestMatches(): array
    {
        $rules = ReconMatchingRule::active()->get();
        $lines = BankStatementLine::whereNull('matched_payment_id')->where('match_status', 'unmatched')->limit(500)->get();
        $payments = Payment::where('status', 'verified')->get();

        $autoMatched = 0;
        $suggested = 0;

        foreach ($lines as $line) {
            foreach ($rules as $rule) {
                if (!$this->ruleApplies($line, $rule)) {
                    continue;
                }

                $candidate = $this->findCandidatePayment($line, $payments);

                if (!$candidate) {
                    continue;
                }

                if ($rule->auto_match) {
                    $line->update(['match_status' => 'auto_matched', 'match_confidence' => 100]);
                    $autoMatched++;
                } else {
                    $line->update(['match_status' => 'suggested', 'match_confidence' => $this->confidence($line, $candidate)]);
                    $suggested++;
                }

                break;
            }
        }

        return ['lines_scanned' => $lines->count(), 'auto_matched' => $autoMatched, 'suggested' => $suggested];
    }

    protected function ruleApplies(BankStatementLine $line, ReconMatchingRule $rule): bool
    {
        $haystack = match ($rule->match_field) {
            'description' => (string) $line->description,
            'reference' => (string) $line->reference,
            default => '',
        };

        return match ($rule->operator) {
            'contains' => str_contains(strtolower($haystack), strtolower((string) $rule->value)),
            'equals' => strtolower($haystack) === strtolower((string) $rule->value),
            'starts_with' => str_starts_with(strtolower($haystack), strtolower((string) $rule->value)),
            'regex' => @preg_match((string) $rule->value, $haystack) === 1,
            'amount_within' => true,
            default => false,
        };
    }

    protected function findCandidatePayment(BankStatementLine $line, Collection $payments): ?Payment
    {
        $amount = (float) ($line->amount_in ?: $line->amount_out);
        $tolerance = (float) SystemSetting::get('recon_amount_tolerance', 1000);

        return $payments
            ->first(fn (Payment $p) => abs((float) $p->amount - $amount) <= $tolerance);
    }

    protected function confidence(BankStatementLine $line, Payment $payment): float
    {
        $amount = (float) ($line->amount_in ?: $line->amount_out);
        $amountDiff = abs((float) $payment->amount - $amount);
        $amountScore = max(0, 1 - $amountDiff / max(1.0, $amount));
        $descScore = $payment->payment_number && str_contains(strtolower($line->description ?? ''), substr(strtolower($payment->payment_number), -6)) ? 1.0 : 0.4;

        return round(($amountScore * 0.7 + $descScore * 0.3) * 100, 1);
    }
}

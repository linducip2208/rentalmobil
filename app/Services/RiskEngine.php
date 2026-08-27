<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\RiskAssessment;
use App\Models\RiskRule;
use Illuminate\Database\Eloquent\Model;

class RiskEngine
{
    public function assess(array $context, ?Customer $customer = null, ?Model $assessable = null): RiskAssessment
    {
        $score = (int) ($customer?->trust_score ?? 50);
        $matched = [];
        $decision = 'allow';
        foreach (RiskRule::where('is_active', true)->orderBy('priority')->get() as $rule) {
            $actual = data_get($context, $rule->field);
            if (! $this->matches($actual, $rule->operator, $rule->comparison_value)) {
                continue;
            }$score += $rule->score_delta;
            $matched[] = ['id' => $rule->id, 'name' => $rule->name, 'action' => $rule->action, 'delta' => $rule->score_delta];
            if ($rule->action === 'block') {
                $decision = 'block';
            } elseif ($rule->action === 'review' && $decision !== 'block') {
                $decision = 'review';
            }
        } if ($score < 20) {
            $decision = 'block';
        } elseif ($score < 40 && $decision === 'allow') {
            $decision = 'review';
        }

return RiskAssessment::create(['customer_id' => $customer?->id, 'assessable_type' => $assessable?->getMorphClass(), 'assessable_id' => $assessable?->getKey(), 'fingerprint_hash' => $context['fingerprint_hash'] ?? null, 'score' => max(0, min(100, $score)), 'decision' => $decision, 'matched_rules' => $matched, 'context' => $context]);
    }

    private function matches(mixed $actual, string $operator, ?string $expected): bool
    {
        return match ($operator) {
            'equals' => (string) $actual === (string) $expected,'not_equals' => (string) $actual !== (string) $expected,'gt' => (float) $actual > (float) $expected,'gte' => (float) $actual >= (float) $expected,'lt' => (float) $actual < (float) $expected,'lte' => (float) $actual <= (float) $expected,'contains' => str_contains(mb_strtolower((string) $actual), mb_strtolower((string) $expected)),'in' => in_array((string) $actual, array_map('trim', explode(',', (string) $expected)), true),default => false
        };
    }
}

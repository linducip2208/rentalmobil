<?php

namespace App\Services;

use App\Models\BlacklistEntry;
use App\Models\Customer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BlacklistService
{
    public function __construct(
        protected NotificationDispatcher $notification,
    ) {}

    public function addEntry(array $data): BlacklistEntry
    {
        $existingActive = BlacklistEntry::where('customer_id', $data['customer_id'])
            ->active()
            ->notExpired()
            ->first();

        if ($existingActive) {
            throw new \RuntimeException(
                "Customer is already blacklisted (entry #{$existingActive->id})."
            );
        }

        $entry = BlacklistEntry::create([
            'customer_id' => $data['customer_id'],
            'reason' => $data['reason'],
            'severity' => $data['severity'] ?? 'medium',
            'evidence_path' => $data['evidence_path'] ?? null,
            'added_by' => auth()->id(),
            'is_active' => true,
            'expires_at' => $data['expires_at'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        $customer = Customer::find($data['customer_id']);
        if ($customer) {
            app(TrustScoreService::class)->updateScore(
                $data['customer_id'],
                -30,
                "Blacklisted: {$data['reason']}",
                BlacklistEntry::class,
                $entry->id
            );

            $this->notification->dispatch('blacklist_added', $customer, [
                'reason' => $data['reason'],
                'severity' => $data['severity'] ?? 'medium',
            ]);
        }

        return $entry;
    }

    public function checkBlacklist(?string $name = null, ?string $phone = null, ?string $idNumber = null): ?BlacklistEntry
    {
        if (! $name && ! $phone && ! $idNumber) {
            return null;
        }

        $query = BlacklistEntry::active()
            ->notExpired()
            ->with('customer');

        $query->where(function ($q) use ($name, $phone, $idNumber) {
            if ($name) {
                $q->orWhereHas('customer', function ($q2) use ($name) {
                    $q2->where('name', 'LIKE', "%{$name}%");
                });
            }

            if ($phone) {
                $q->orWhereHas('customer', function ($q2) use ($phone) {
                    $q2->where('phone', $phone);
                });
            }

            if ($idNumber) {
                $q->orWhereHas('customer', function ($q2) use ($idNumber) {
                    $q2->where('id_card_number', $idNumber);
                });
            }
        });

        return $query->first();
    }

    public function removeEntry(int $entryId, ?string $reason = null): BlacklistEntry
    {
        $entry = BlacklistEntry::findOrFail($entryId);

        if (! $entry->is_active) {
            throw new \RuntimeException('Blacklist entry is already inactive.');
        }

        $entry->update([
            'is_active' => false,
            'notes' => $reason
                ? ($entry->notes ? "{$entry->notes}\nRemoved: {$reason}" : "Removed: {$reason}")
                : $entry->notes,
        ]);

        $customer = Customer::find($entry->customer_id);
        if ($customer) {
            app(TrustScoreService::class)->updateScore(
                $entry->customer_id,
                15,
                "Blacklist removed: {$reason}",
                BlacklistEntry::class,
                $entry->id
            );
        }

        return $entry->fresh();
    }

    public function getLevelActions(string $level): array
    {
        return match ($level) {
            'low' => [
                'can_book' => true,
                'requires_deposit' => true,
                'deposit_multiplier' => 1.5,
                'max_duration_days' => 7,
                'can_use_self_drive' => true,
                'requires_additional_id' => false,
                'notify_manager' => false,
                'description' => 'Tingkat ringan: deposit lebih tinggi, durasi terbatas',
            ],
            'medium' => [
                'can_book' => true,
                'requires_deposit' => true,
                'deposit_multiplier' => 2.0,
                'max_duration_days' => 3,
                'can_use_self_drive' => false,
                'requires_additional_id' => true,
                'notify_manager' => true,
                'description' => 'Tingkat menengah: wajib supir, deposit 2x lipat',
            ],
            'high' => [
                'can_book' => false,
                'requires_deposit' => true,
                'deposit_multiplier' => 3.0,
                'max_duration_days' => 0,
                'can_use_self_drive' => false,
                'requires_additional_id' => true,
                'notify_manager' => true,
                'description' => 'Tingkat berat: pemesanan diblokir',
            ],
            'critical' => [
                'can_book' => false,
                'requires_deposit' => true,
                'deposit_multiplier' => 5.0,
                'max_duration_days' => 0,
                'can_use_self_drive' => false,
                'requires_additional_id' => true,
                'notify_manager' => true,
                'description' => 'Tingkat kritis: diblokir total, dilaporkan',
            ],
            default => [
                'can_book' => true,
                'requires_deposit' => false,
                'deposit_multiplier' => 1.0,
                'max_duration_days' => null,
                'can_use_self_drive' => true,
                'requires_additional_id' => false,
                'notify_manager' => false,
                'description' => 'Level tidak dikenali',
            ],
        };
    }

    public function getActiveEntries(?string $severity = null, int $limit = 50): Collection
    {
        $query = BlacklistEntry::active()
            ->notExpired()
            ->with('customer')
            ->orderByDesc('created_at');

        if ($severity) {
            $query->where('severity', $severity);
        }

        return $query->limit($limit)->get();
    }

    public function getEntryStats(): array
    {
        $total = BlacklistEntry::count();
        $active = BlacklistEntry::active()->notExpired()->count();
        $expired = BlacklistEntry::active()
            ->where('expires_at', '<', now())
            ->count();

        $bySeverity = BlacklistEntry::active()
            ->notExpired()
            ->select('severity', DB::raw('count(*) as total'))
            ->groupBy('severity')
            ->pluck('total', 'severity')
            ->toArray();

        return [
            'total_entries' => $total,
            'active_entries' => $active,
            'expired_entries' => $expired,
            'by_severity' => $bySeverity,
        ];
    }

    public function checkCustomerBeforeBooking(int $customerId): array
    {
        $customer = Customer::find($customerId);
        if (! $customer) {
            return ['blocked' => true, 'reason' => 'Customer not found'];
        }

        $entry = BlacklistEntry::where('customer_id', $customerId)
            ->active()
            ->notExpired()
            ->first();

        if ($entry) {
            $level = $entry->severity;
            $actions = $this->getLevelActions($level);

            return [
                'blocked' => ! $actions['can_book'],
                'reason' => $entry ? $entry->reason : 'Blacklisted',
                'level' => $level,
                'actions' => $actions,
                'deposit_multiplier' => $actions['deposit_multiplier'],
            ];
        }

        return ['blocked' => false];
    }
}

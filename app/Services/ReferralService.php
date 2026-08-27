<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Referral;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReferralService
{
    public function generateCode(Customer $customer): Referral
    {
        $code = strtoupper(Str::substr(md5($customer->id.config('app.key')), 0, 8));

        return Referral::firstOrCreate(
            ['referrer_customer_id' => $customer->id],
            [
                'code' => 'REF-'.$customer->id.'-'.$code,
                'reward_type' => 'points',
                'reward_value' => (int) SystemSetting::get('referral_reward_points', 100),
            ]
        );
    }

    public function getCode(Customer $customer): string
    {
        return $this->generateCode($customer)->code;
    }

    /**
     * Dipanggil saat customer baru mendaftar dengan kode referral.
     */
    public function trackSignup(string $code, Customer $newCustomer): ?Referral
    {
        $referral = Referral::where('code', strtoupper(trim($code)))->first();

        if (! $referral || $referral->referrer_customer_id === $newCustomer->id) {
            return null;
        }

        if ($referral->status !== 'pending') {
            return null;
        }

        DB::transaction(function () use ($referral, $newCustomer) {
            $referral->update([
                'referred_customer_id' => $newCustomer->id,
                'status' => 'registered',
            ]);

            if (filled($newCustomer->email) && blank($referral->referred_email)) {
                $referral->update(['referred_email' => $newCustomer->email]);
            }
        });

        app(NotificationDispatcher::class)->dispatch('referral_registered', $referral->referrerCustomer, [
            'code' => $referral->code,
            'referred_name' => $newCustomer->name,
        ]);

        return $referral->refresh();
    }

    /**
     * Reward diberikan setelah order pertama referred customer selesai.
     */
    public function rewardOnFirstCompletedOrder(Customer $newCustomer): ?Referral
    {
        $referral = Referral::where('referred_customer_id', $newCustomer->id)
            ->where('status', 'registered')
            ->first();

        if (! $referral) {
            return null;
        }

        DB::transaction(function () use ($referral, $newCustomer) {
            $referral->update([
                'status' => 'rewarded',
                'completed_at' => now(),
            ]);

            app(LoyaltyRedemptionService::class)->earn(
                $referral->referrerCustomer,
                (int) $referral->reward_value,
                "Reward referral: {$newCustomer->name}",
                $referral
            );
        });

        app(NotificationDispatcher::class)->dispatch('referral_rewarded', $referral->referrerCustomer, [
            'points' => $referral->reward_value,
        ]);

        return $referral;
    }

    public function statsFor(Customer $customer): array
    {
        $referrals = Referral::where('referrer_customer_id', $customer->id);

        return [
            'code' => $this->getCode($customer),
            'total_referred' => (clone $referrals)->whereIn('status', ['registered', 'first_order', 'rewarded'])->count(),
            'rewarded' => (clone $referrals)->where('status', 'rewarded')->count(),
            'pending' => (clone $referrals)->where('status', 'pending')->count(),
            'total_points_earned' => (clone $referrals)->where('status', 'rewarded')->sum('reward_value'),
        ];
    }
}

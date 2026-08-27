<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\NotificationTemplate;
use App\Models\RentalOrder;
use App\Services\HandoverLinkService;
use App\Services\NotificationDispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PublicHandoverController extends Controller
{
    public function __construct(protected HandoverLinkService $links) {}

    public function showContract(string $token)
    {
        $secure = $this->links->findValidToken(HandoverLinkService::SCOPE_CONTRACT_SIGN, $token);
        abort_unless($secure, 404);

        /** @var Contract $contract */
        $contract = $secure->reference()->first();

        return view('handover.contract-sign', [
            'contract' => $contract->load(['customer', 'vehicle', 'rentalOrder']),
            'token' => $token,
            'otpRequired' => $this->otpEnabled(),
        ]);
    }

    public function sendOtp(Request $request, string $token)
    {
        $secure = $this->links->findValidToken(HandoverLinkService::SCOPE_CONTRACT_SIGN, $token);
        abort_unless($secure && $this->otpEnabled(), 404);

        $code = (string) random_int(100000, 999999);
        Cache::put($this->otpKey($token), $code, now()->addMinutes(10));

        /** @var Contract $contract */
        $contract = $secure->reference()->first();
        $customer = $contract->customer;
        $sent = false;

        foreach (['whatsapp', 'sms'] as $channel) {
            if ($customer?->phone && NotificationTemplate::active()->byChannel($channel)->exists()) {
                try {
                    app(NotificationDispatcher::class)->dispatch('contract_otp', $customer, [
                        'otp_code' => $code,
                        'contract_number' => $contract->contract_number,
                        'customer_name' => $customer->name,
                    ]);
                    $sent = true;
                    break;
                } catch (\Throwable) {
                    continue;
                }
            }
        }

        if (! $sent && $customer?->email && NotificationTemplate::active()->byChannel('email')->exists()) {
            try {
                app(NotificationDispatcher::class)->dispatch('contract_otp', $customer, [
                    'otp_code' => $code,
                    'contract_number' => $contract->contract_number,
                    'customer_name' => $customer->name,
                ]);
                $sent = true;
            } catch (\Throwable) {
            }
        }

        if (! $sent) {
            // Fallback transparan untuk demo/dev tanpa provider: tampilkan kode di layar.
            session()->flash('otp_dev_code', $code);

            return back()->with('status', 'Mode demo (provider notifikasi belum diatur). Kode OTP ditampilkan di layar.');
        }

        return back()->with('status', 'Kode OTP telah dikirim ke nomor/email Anda.');
    }

    public function signContract(Request $request, string $token)
    {
        $secure = $this->links->findValidToken(HandoverLinkService::SCOPE_CONTRACT_SIGN, $token);

        abort_unless($secure, 410, 'Link tidak valid atau sudah kedaluwarsa.');

        $rules = ['signature' => ['required', 'string', 'starts_with:data:image/']];

        if ($this->otpEnabled()) {
            $rules['otp'] = ['required', 'digits:6'];
        }

        $data = $request->validate($rules);

        if ($this->otpEnabled()) {
            abort_unless(Cache::pull($this->otpKey($token)) === $data['otp'], 422, 'Kode OTP salah atau kedaluwarsa.');
        }

        try {
            $contract = $this->links->signContract($secure, $data['signature'], $request->ip());
        } catch (\Throwable $e) {
            abort(422, $e->getMessage());
        }

        return view('handover.contract-done', compact('contract'));
    }

    public function showCheckIn(string $token)
    {
        $secure = $this->links->findValidToken(HandoverLinkService::SCOPE_ORDER_CHECKIN, $token);
        abort_unless($secure, 404);

        /** @var RentalOrder $order */
        $order = $secure->reference()->first();

        return view('handover.checkin', ['order' => $order->load(['vehicle', 'customer']), 'token' => $token]);
    }

    public function submitCheckIn(Request $request, string $token)
    {
        $secure = $this->links->findValidToken(HandoverLinkService::SCOPE_ORDER_CHECKIN, $token);

        abort_unless($secure, 410, 'Link tidak valid atau sudah kedaluwarsa.');

        $data = $request->validate([
            'fuel_level' => ['required', 'in:full,three_quarter,half,quarter,empty'],
            'odometer_km' => ['nullable', 'numeric', 'min:0', 'max:2000000'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'photos' => ['required', 'array', 'min:1', 'max:8'],
            'photos.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:6144'],
        ]);

        try {
            app(HandoverLinkService::class)->submitCheckIn($secure, $data, $request->ip());
        } catch (\Throwable $e) {
            abort(422, $e->getMessage());
        }

        return view('handover.checkin-done');
    }

    private function otpEnabled(): bool
    {
        return NotificationTemplate::active()->byChannel('whatsapp')->exists()
            || NotificationTemplate::active()->byChannel('sms')->exists()
            || NotificationTemplate::active()->byChannel('email')->exists();
    }

    private function otpKey(string $token): string
    {
        return 'contract_otp.'.hash('sha256', $token);
    }
}

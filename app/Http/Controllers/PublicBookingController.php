<?php

namespace App\Http\Controllers;

use App\Exceptions\VehicleUnavailableException;
use App\Models\Addon;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\CustomerDocument;
use App\Models\Location;
use App\Models\Vehicle;
use App\Services\AvailabilityEngine;
use App\Services\BookingHoldService;
use App\Services\BookingService;
use App\Services\DiscountModifierService;
use App\Services\MultiCityRelocationService;
use App\Services\PricingEngine;
use App\Services\ReferralService;
use App\Services\RiskEngine;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class PublicBookingController extends Controller
{
    public function index(Request $r, AvailabilityEngine $availability)
    {
        $vehicles = Vehicle::with(['category', 'brand', 'photos'])
            ->where('is_active', true)
            ->where('status', 'available')
            ->orderBy('daily_rate')
            ->paginate(12)
            ->withQueryString();

        $pickupDate = $r->query('start_date') ?? $r->query('pickup_date');
        $returnDate = $r->query('end_date') ?? $r->query('return_date');

        // Bila kedua tanggal tersedia, hanya tampilkan unit bebas pada periode itu.
        if (strtotime((string) $pickupDate) !== false && strtotime((string) $returnDate) !== false && $returnDate > $pickupDate) {
            $blocked = $availability->blockedVehicleIds(
                CarbonImmutable::parse($pickupDate),
                CarbonImmutable::parse($returnDate)
            );
            $vehicles->setCollection($vehicles->getCollection()->reject(fn ($v) => $blocked->contains($v->id))->values());
        }

        return view('booking.index', [
            'vehicles' => $vehicles,
            'locations' => Location::active()->orderBy('name')->get(),
            'addons' => Addon::active()->orderBy('name')->get(),
            'preselectedVehicle' => $r->query('vehicle'),
            'prefill' => [
                'start_date' => $pickupDate,
                'end_date' => $returnDate,
                'pickup_location_id' => $r->query('pickup_location'),
                'rental_type' => $r->query('rental_type'),
            ],
        ]);
    }

    public function quote(Request $r, PricingEngine $pricing, DiscountModifierService $modifiers)
    {
        $d = $r->validate(['vehicle_id' => 'required|exists:vehicles,id', 'start_date' => 'required|date|after_or_equal:today', 'end_date' => 'required|date|after:start_date', 'rental_type' => 'nullable|string', 'addon_ids' => 'nullable|array', 'addon_ids.*' => 'integer|exists:addons,id', 'promo_code' => 'nullable|string|max:50', 'pickup_location_id' => 'nullable|exists:locations,id', 'return_location_id' => 'nullable|exists:locations,id']);
        $vehicle = Vehicle::findOrFail($d['vehicle_id']);
        $quote = $pricing->calculateRentalPrice($vehicle, $d['start_date'], $d['end_date'], $d['rental_type'] ?? 'self_drive', $d['addon_ids'] ?? [], $d['promo_code'] ?? null);
        if (! empty($d['pickup_location_id']) && ! empty($d['return_location_id']) && $d['pickup_location_id'] !== $d['return_location_id']) {
            $pickup = Location::find($d['pickup_location_id']);
            $returnLoc = Location::find($d['return_location_id']);
            if ($pickup && $returnLoc) {
                $fee = app(MultiCityRelocationService::class)->calculateFee($pickup, $returnLoc);
                if ($fee > 0) {
                    $quote['subtotal'] = round($quote['subtotal'] + $fee, 2);
                    $quote['breakdown']['relocation_fee'] = $fee;
                }
            }
        }

        return response()->json($quote);
    }

    public function hold(Request $r, BookingHoldService $holds)
    {
        $d = $r->validate(['vehicle_id' => 'required|exists:vehicles,id', 'start_date' => 'required|date|after_or_equal:today', 'end_date' => 'required|date|after:start_date']);
        try {
            $hold = $holds->createHold(Vehicle::findOrFail($d['vehicle_id']), $d['start_date'], $d['end_date'], $r->input('session_id'));
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }

        return response()->json(['hold_token' => $hold->hold_token, 'expires_at' => $hold->expires_at->toIso8601String(), 'minutes_left' => now()->diffInMinutes($hold->expires_at)]);
    }

    public function store(Request $r, BookingService $bookings, RiskEngine $risk, PricingEngine $pricing, BookingHoldService $holds)
    {
        $d = $r->validate([
            'name' => 'required|string|max:150', 'email' => 'required|email|max:150', 'phone' => 'required|string|max:30',
            'vehicle_id' => 'required|exists:vehicles,id', 'pickup_location_id' => 'required|exists:locations,id', 'return_location_id' => 'nullable|exists:locations,id',
            'start_date' => 'required|date|after_or_equal:today', 'end_date' => 'required|date|after:start_date',
            'rental_type' => 'required|in:self_drive,with_driver,airport_transfer,corporate',
            'addon_ids' => 'nullable|array', 'addon_ids.*' => 'integer|exists:addons,id', 'promo_code' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000', 'session_id' => 'nullable|string|max:64', 'referral_code' => 'nullable|string|max:32',
            'group_booking_id' => 'nullable|exists:group_bookings,id',
            'document_ktp' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'document_sim' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'document_selfie' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);
        $customer = Customer::firstOrCreate(['email' => $d['email']], ['name' => $d['name'], 'phone' => $d['phone'], 'customer_type' => 'individual', 'trust_score' => 50, 'verification_status' => 'submitted', 'is_active' => true]);
        // Track referral signup bila ada kode.
        if (! empty($d['referral_code'])) {
            try {
                app(ReferralService::class)->trackSignup($d['referral_code'], $customer);
            } catch (\Throwable $e) {
                report($e);
            }
        }
        $quote = $pricing->calculateRentalPrice(Vehicle::findOrFail($d['vehicle_id']), $d['start_date'], $d['end_date'], $d['rental_type'], $d['addon_ids'] ?? [], $d['promo_code'] ?? null);
        $assessment = $risk->assess(['ip' => $r->ip(), 'user_agent' => $r->userAgent(), 'fingerprint_hash' => hash('sha256', ($r->userAgent() ?? '').'|'.$r->ip()), 'booking_amount' => $quote['total'], 'vehicle_id' => $d['vehicle_id'], 'rental_type' => $d['rental_type']], $customer);
        abort_if($assessment->decision === 'block', 422, 'Permintaan memerlukan verifikasi manual.');
        $booking = null;
        $sessionId = $d['session_id'] ?? null;
        try {
            $booking = DB::transaction(function () use ($bookings, $d, $customer, $sessionId) {
                return $bookings->createBooking($d + ['customer_id' => $customer->id, 'source' => 'website', 'created_by' => null, 'session_id' => $sessionId]);
            });
        } catch (VehicleUnavailableException $e) {
            // Double-booking / hold conflict → 409 with a customer-friendly message.
            return response()->json(['message' => $e->getMessage()], 409);
        } catch (\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'di-hold') || str_contains($e->getMessage(), 'not available') || str_contains($e->getMessage(), 'tidak tersedia')) {
                return response()->json(['message' => $e->getMessage()], 409);
            }

            throw $e;
        }
        $assessment->update(['assessable_type' => $booking->getMorphClass(), 'assessable_id' => $booking->id]);

        $this->storeDocuments($r, $customer);

        return redirect(URL::temporarySignedRoute('booking.success', now()->addHours(24), ['booking' => $booking]));
    }

    /**
     * Persist optional identity documents (KTP/SIM/selfie) uploaded at checkout.
     * Files live on the private local disk — never publicly reachable.
     */
    private function storeDocuments(Request $r, Customer $customer): void
    {
        $map = [
            'document_ktp' => 'ktp',
            'document_sim' => 'sim_a',
            'document_selfie' => 'selfie',
        ];

        foreach ($map as $input => $type) {
            if (! $r->hasFile($input) || ! $r->file($input)->isValid()) {
                continue;
            }

            $path = $r->file($input)->store("customer-documents/{$customer->id}", 'local');

            CustomerDocument::create([
                'customer_id' => $customer->id,
                'document_type' => $type,
                'document_url' => $path,
                'status' => 'pending',
            ]);
        }
    }

    public function success(Booking $booking)
    {
        return view('booking.success', compact('booking'));
    }
}

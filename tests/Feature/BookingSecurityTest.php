<?php

namespace Tests\Feature;

use App\Exceptions\VehicleUnavailableException;
use App\Models\Booking;
use App\Models\Brand;
use App\Models\Customer;
use App\Models\CustomerDocument;
use App\Models\Deposit;
use App\Models\Invoice;
use App\Models\Vehicle;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class BookingSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected Customer $customerA;

    protected Customer $customerB;

    protected Vehicle $vehicle;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customerA = Customer::create([
            'name' => 'Customer A', 'email' => 'cust-a@test.local', 'phone' => '08133330001',
            'customer_type' => 'individual', 'verification_status' => 'verified', 'is_active' => true,
        ]);
        $this->customerB = Customer::create([
            'name' => 'Customer B', 'email' => 'cust-b@test.local', 'phone' => '08133330002',
            'customer_type' => 'individual', 'verification_status' => 'verified', 'is_active' => true,
        ]);

        $this->vehicle = Vehicle::create([
            'name' => 'Toyota Avanza Sec', 'slug' => 'toyota-avanza-sec',
            'category_id' => 1, 'brand_id' => Brand::firstOrCreate(['name' => 'Toyota'], ['slug' => 'toyota', 'is_active' => true])->id,
            'location_id' => 1, 'plate_number' => 'B 7000 SC', 'year' => 2024, 'color' => 'Putih',
            'transmission' => 'automatic', 'fuel_type' => 'pertalite', 'seat_count' => 7,
            'mileage' => 10000, 'daily_rate' => 350000, 'weekly_rate' => 2100000, 'monthly_rate' => 7500000,
            'deposit_amount' => 500000, 'status' => 'available', 'is_active' => true,
        ]);
    }

    private function bookingData(Customer $customer, array $extra = []): array
    {
        return array_merge([
            'customer_id' => $customer->id,
            'vehicle_id' => $this->vehicle->id,
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-05',
            'rental_type' => 'self_drive',
            'pickup_location_id' => 1,
            'source' => 'website',
        ], $extra);
    }

    public function test_double_booking_is_prevented(): void
    {
        $service = app(BookingService::class);

        $first = $service->createBooking($this->bookingData($this->customerA));
        $this->assertSame('pending_verification', $first->status);

        $this->expectException(VehicleUnavailableException::class);
        $service->createBooking($this->bookingData($this->customerB));
    }

    public function test_double_booking_returns_conflict_409_via_http(): void
    {
        // Booking pertama dibuat langsung lewat service.
        app(BookingService::class)->createBooking($this->bookingData($this->customerA));

        // Customer B mencoba booking rentang sama via HTTP → 409 ramah.
        $response = $this->post(route('booking.store'), [
            'name' => 'Customer B', 'email' => $this->customerB->email, 'phone' => $this->customerB->phone,
            'vehicle_id' => $this->vehicle->id, 'pickup_location_id' => 1,
            'start_date' => '2026-10-02', 'end_date' => '2026-10-04',
            'rental_type' => 'self_drive',
        ]);

        $response->assertStatus(409);
        $response->assertJsonPath('message', fn ($message) => str_contains($message, 'tidak tersedia') || str_contains($message, 'di-hold'));
    }

    public function test_unavailable_vehicle_cannot_be_booked(): void
    {
        $this->vehicle->update(['status' => 'maintenance']);

        $response = $this->post(route('booking.store'), [
            'name' => 'Customer A', 'email' => $this->customerA->email, 'phone' => $this->customerA->phone,
            'vehicle_id' => $this->vehicle->id, 'pickup_location_id' => 1,
            'start_date' => '2026-10-01', 'end_date' => '2026-10-05',
            'rental_type' => 'self_drive',
        ]);

        $response->assertStatus(409);
        $this->assertDatabaseMissing('bookings', ['vehicle_id' => $this->vehicle->id]);
    }

    public function test_price_tampering_is_ignored(): void
    {
        // Browser mencoba menempelkan total harganya sendiri.
        $booking = app(BookingService::class)->createBooking($this->bookingData($this->customerA, [
            'total_amount' => 1,
            'subtotal' => 1,
            'deposit_amount' => 0,
        ]));

        // Server menghitung ulang dari PricingEngine: 4 hari × 350.000 + PPN 11%.
        $expectedSubtotal = 350000 * 4;
        $expectedTax = round($expectedSubtotal * 0.11, 2);
        $this->assertEquals($expectedSubtotal, (float) $booking->subtotal);
        $this->assertEquals($expectedTax, (float) $booking->tax_amount);
        $this->assertEquals(round($expectedSubtotal + $expectedTax, 2), (float) $booking->total_amount);
        $this->assertEquals(500000, (float) $booking->deposit_amount);
    }

    public function test_customer_a_cannot_view_or_touch_customer_b_resources(): void
    {
        $invoiceB = Invoice::create([
            'customer_id' => $this->customerB->id, 'type' => 'rental',
            'subtotal' => 1000000, 'total_amount' => 1000000, 'balance_due' => 1000000, 'status' => 'issued',
        ]);

        // Invoice download milik B → 404 untuk A.
        $this->actingAs($this->customerA, 'customer')
            ->get(route('portal.invoices.download', $invoiceB))
            ->assertNotFound();

        // Upload bukti bayar ke invoice B → 404 untuk A.
        Storage::fake('local');
        $this->actingAs($this->customerA, 'customer')
            ->post(route('portal.invoices.payment-proof', $invoiceB), [
                'amount' => 100000, 'reference_number' => 'TRX-X', 'proof' => UploadedFile::fake()->image('bukti.jpg'),
            ])
            ->assertNotFound();
        $this->assertDatabaseMissing('payments', ['invoice_id' => $invoiceB->id]);
    }

    public function test_rejected_document_can_be_reuploaded_by_owner_only(): void
    {
        Storage::fake('local');

        $docB = CustomerDocument::create([
            'customer_id' => $this->customerB->id, 'document_type' => 'ktp',
            'document_url' => 'customer-documents/'.$this->customerB->id.'/ktp.jpg', 'status' => 'rejected',
            'rejection_reason' => 'Foto buram',
        ]);

        // Customer A mencoba re-upload dokumen milik B → 404.
        $this->actingAs($this->customerA, 'customer')
            ->post(route('portal.documents.reupload', $docB), [
                'document' => UploadedFile::fake()->image('ktp-a.jpg'),
            ])
            ->assertNotFound();

        // Owner (B) re-upload → redirect sukses + status kembali pending.
        $this->actingAs($this->customerB, 'customer')
            ->post(route('portal.documents.reupload', $docB), [
                'document' => UploadedFile::fake()->image('ktp-b.jpg'),
            ])
            ->assertRedirect();
        $this->assertSame('pending', $docB->fresh()->status);
    }

    public function test_deposit_is_tracked_separately_from_rental_revenue(): void
    {
        $booking = app(BookingService::class)->createBooking($this->bookingData($this->customerA));

        $deposit = Deposit::create([
            'customer_id' => $this->customerA->id,
            'booking_id' => $booking->id,
            'amount' => $booking->deposit_amount,
            'deposit_status' => 'held',
            'received_at' => now(),
        ]);

        // Deposit di-flag sebagai tanda jaminan (held), bukan pendapatan sewa.
        $this->assertSame('held', $deposit->deposit_status);
        $this->assertTrue($deposit->isHeld());
        $this->assertFalse($deposit->isRefunded());
        // Total booking tidak menambah deposit ke pendapatan (deposit terpisah di tabel deposits).
        $this->assertEquals($booking->deposit_amount, $deposit->amount);
    }

    public function test_booking_success_page_only_via_signed_url(): void
    {
        $booking = app(BookingService::class)->createBooking($this->bookingData($this->customerA));

        // Tanpa signature → 403.
        $this->get("/booking/{$booking->id}/berhasil")->assertForbidden();

        // Dengan signed URL → 200.
        $this->get(URL::temporarySignedRoute('booking.success', now()->addHour(), ['booking' => $booking]))
            ->assertOk();
    }
}

<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UiAndPortalSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_marketing_docs_login_and_sitemap_render(): void
    {
        $this->get('/')->assertOk()->assertSee('Temukan Mobil yang Tepat')->assertSee('Cari Mobil untuk Perjalanan Anda', false)->assertSee('/admin/login');
        $this->get('/booking')->assertOk()->assertSee('Ringkasan harga')->assertSee('Konfirmasi booking');
        $this->get('/corporate')->assertOk()->assertSee('Corporate mobility');
        $this->get('/tentang-kami')->assertOk()->assertSee('Standar operasional');
        $this->get('/docs')->assertOk()->assertSee('Dokumentasi RentalMobil');
        $this->get('/admin/login')->assertOk()->assertSee('Kendaraan bergerak')->assertSee('Akun demo');
        $this->get('/portal/login')->assertOk()->assertSee('Portal pelanggan');
        $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8')->assertSee('sitemapindex', false);
        $this->get('/sitemap/core-1.xml')->assertOk()->assertSee('urlset', false);
    }

    public function test_driver_cannot_open_system_or_finance_pages(): void
    {
        $driver = User::factory()->create(['role' => 'driver', 'is_active' => true]);
        $this->actingAs($driver)->get('/admin/users')->assertForbidden();
        $this->actingAs($driver)->get('/admin/laporan-keuangan')->assertForbidden();
    }

    public function test_customer_can_upload_proof_only_for_own_invoice(): void
    {
        Storage::fake('public');
        $customer = $this->customer('081200000001', 'customer-one@test.local');
        $other = $this->customer('081200000002', 'customer-two@test.local');
        $invoice = Invoice::create(['customer_id' => $customer->id, 'type' => 'rental', 'subtotal' => 500000, 'total_amount' => 500000, 'balance_due' => 500000, 'status' => 'issued']);
        $foreign = Invoice::create(['customer_id' => $other->id, 'type' => 'rental', 'subtotal' => 400000, 'total_amount' => 400000, 'balance_due' => 400000, 'status' => 'issued']);

        $this->actingAs($customer, 'customer')->post(route('portal.invoices.payment-proof', $invoice), [
            'amount' => 250000, 'reference_number' => 'TRX-001', 'proof' => UploadedFile::fake()->image('bukti.jpg'),
        ])->assertRedirect();
        $this->assertDatabaseHas('payments', ['invoice_id' => $invoice->id, 'customer_id' => $customer->id, 'status' => 'pending']);
        Storage::disk('public')->assertExists('payment-proofs/'.$customer->id.'/'.basename((string) Payment::first()->proof_url));

        $this->actingAs($customer, 'customer')->post(route('portal.invoices.payment-proof', $foreign), [
            'amount' => 100000, 'proof' => UploadedFile::fake()->image('asing.jpg'),
        ])->assertNotFound();
    }

    public function test_customer_cannot_download_another_customers_invoice(): void
    {
        $customer = $this->customer('081200000003', 'portal-owner@test.local');
        $other = $this->customer('081200000004', 'portal-other@test.local');
        $invoice = Invoice::create(['customer_id' => $other->id, 'type' => 'rental', 'subtotal' => 100000, 'total_amount' => 100000, 'balance_due' => 100000, 'status' => 'issued']);
        $this->actingAs($customer, 'customer')->get(route('portal.invoices.download', $invoice))->assertNotFound();
    }

    private function customer(string $phone, string $email): Customer
    {
        return Customer::create(['name' => 'Customer Test', 'phone' => $phone, 'email' => $email, 'password' => 'password', 'customer_type' => 'individual', 'verification_status' => 'verified', 'is_active' => true]);
    }
}

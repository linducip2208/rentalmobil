<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class StorefrontCategoryTest extends TestCase
{
    use RefreshDatabase;

    private function makeVehicle(string $name, string $plate, Category $category, array $extra = []): Vehicle
    {
        return Vehicle::create(array_merge([
            'name' => $name,
            'slug' => Str::slug($name).'-'.strtolower(str_replace(' ', '', $plate)),
            'category_id' => $category->id,
            'brand_id' => Brand::firstOrCreate(['name' => 'Toyota'], ['slug' => 'toyota', 'is_active' => true])->id,
            'location_id' => 1,
            'plate_number' => $plate,
            'year' => 2024,
            'color' => 'Putih',
            'transmission' => 'automatic',
            'fuel_type' => 'pertalite',
            'seat_count' => 7,
            'mileage' => 10000,
            'daily_rate' => 350000,
            'weekly_rate' => 2100000,
            'monthly_rate' => 7500000,
            'deposit_amount' => 500000,
            'status' => 'available',
            'is_active' => true,
        ], $extra));
    }

    public function test_category_slug_url_filters_only_that_category(): void
    {
        $mpv = Category::create(['name' => 'MPV', 'slug' => 'mpv', 'is_active' => true, 'sort_order' => 1]);
        $suv = Category::create(['name' => 'SUV', 'slug' => 'suv', 'is_active' => true, 'sort_order' => 2]);

        $innova = $this->makeVehicle('Toyota Innova', 'B 1001 AA', $mpv);
        $this->makeVehicle('Toyota Avanza', 'B 1002 BB', $mpv);
        $fortuner = $this->makeVehicle('Toyota Fortuner', 'B 1003 CC', $suv);

        $response = $this->get('/sewa-mobil/mpv');

        $response->assertOk()
            ->assertSee($innova->name, false)
            ->assertDontSee($fortuner->name, false);
    }

    public function test_each_category_route_works(): void
    {
        foreach (['mpv', 'suv', 'sedan', 'pickup', 'electric'] as $slug) {
            Category::firstOrCreate(['slug' => $slug], ['name' => strtoupper($slug), 'is_active' => true]);
        }

        foreach (['mpv', 'suv', 'sedan', 'pickup', 'electric'] as $slug) {
            $this->get("/sewa-mobil/{$slug}")->assertOk();
        }
    }

    public function test_query_param_category_still_works(): void
    {
        $mpv = Category::create(['name' => 'MPV', 'slug' => 'mpv', 'is_active' => true, 'sort_order' => 1]);
        $suv = Category::create(['name' => 'SUV', 'slug' => 'suv', 'is_active' => true, 'sort_order' => 2]);

        $innova = $this->makeVehicle('Toyota Innova', 'B 2001 AA', $mpv);
        $fortuner = $this->makeVehicle('Toyota Fortuner', 'B 2002 CC', $suv);

        $response = $this->get('/sewa-mobil?category=mpv');

        $response->assertOk()
            ->assertSee($innova->name, false)
            ->assertDontSee($fortuner->name, false);
    }

    public function test_unknown_category_slug_returns_404(): void
    {
        Category::create(['name' => 'MPV', 'slug' => 'mpv', 'is_active' => true, 'sort_order' => 1]);
        $this->makeVehicle('Toyota Avanza', 'B 3001 BB', Category::where('slug', 'mpv')->first());

        $this->get('/sewa-mobil/kategori-tidak-ada')->assertNotFound();
    }

    public function test_inactive_category_slug_returns_404(): void
    {
        Category::create(['name' => 'Truk', 'slug' => 'truk', 'is_active' => false, 'sort_order' => 9]);

        $this->get('/sewa-mobil/truk')->assertNotFound();
    }

    public function test_category_page_with_period_filter_shows_only_available(): void
    {
        $mpv = Category::create(['name' => 'MPV', 'slug' => 'mpv', 'is_active' => true, 'sort_order' => 1]);

        $free = $this->makeVehicle('Toyota Avanza', 'B 4001 BB', $mpv);
        $busy = $this->makeVehicle('Toyota Innova', 'B 4002 CC', $mpv);

        $customer = Customer::create([
            'name' => 'C', 'email' => 'cat@test.local', 'phone' => '08111110001',
            'customer_type' => 'individual', 'verification_status' => 'verified', 'is_active' => true,
        ]);

        Booking::create([
            'customer_id' => $customer->id, 'vehicle_id' => $busy->id,
            'start_date' => '2026-10-01', 'end_date' => '2026-10-05',
            'rental_type' => 'self_drive', 'duration_days' => 4,
            'subtotal' => 2000000, 'total_amount' => 2000000, 'status' => 'confirmed',
        ]);

        $response = $this->get('/sewa-mobil/mpv?pickup_date=2026-10-02&return_date=2026-10-04&available_only=1');

        $response->assertOk()
            ->assertSee($free->name, false)
            ->assertDontSee($busy->name, false);
    }
}

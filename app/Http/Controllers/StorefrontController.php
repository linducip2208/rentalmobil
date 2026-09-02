<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Faq;
use App\Models\Location;
use App\Models\PromoVoucher;
use App\Models\Testimonial;
use App\Models\Vehicle;
use App\Services\AvailabilityEngine;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Storefront controller for the customer-facing rental experience:
 * homepage, catalog with filters, and vehicle detail pages.
 * All data comes from the database — no hardcoded statistics.
 */
class StorefrontController extends Controller
{
    private const SORT_MAP = [
        'recommended' => ['is_active' => 'desc'],
        'price_low' => ['daily_rate' => 'asc'],
        'price_high' => ['daily_rate' => 'desc'],
        'newest' => ['year' => 'desc'],
    ];

    public function home()
    {
        $vehicles = Vehicle::query()
            ->with(['category', 'brand', 'location', 'photos'])
            ->where('is_active', true)
            ->whereIn('status', ['available', 'reserved', 'rented', 'preparing'])
            ->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', ['available'])
            ->orderBy('daily_rate')
            ->limit(6)
            ->get();

        // Eager-load active vehicles + photos for category cards (no N+1).
        $categories = Category::query()
            ->where('is_active', true)
            ->whereHas('vehicles', fn (Builder $q) => $q->where('is_active', true))
            ->with([
                'vehicles' => fn (Builder $q) => $q
                    ->where('is_active', true)
                    ->select(['id', 'category_id', 'name', 'slug', 'photo_url'])
                    ->with('photos'),
            ])
            ->withCount(['vehicles' => fn (Builder $q) => $q->where('is_active', true)])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->view('storefront.home', [
            'vehicles' => $vehicles,
            'categories' => $categories,
            // City cards read $location->vehicles (is_active + cover photos).
            'locations' => Location::query()
                ->active()
                ->orderBy('name')
                ->with([
                    'vehicles' => fn (Builder $q) => $q
                        ->where('is_active', true)
                        ->select(['id', 'location_id', 'name', 'slug', 'photo_url'])
                        ->with('photos'),
                ])
                ->get(),
            'promos' => PromoVoucher::query()
                ->where('is_active', true)
                ->where(fn (Builder $q) => $q->whereNull('end_date')->orWhere('end_date', '>=', today()))
                ->orderBy('end_date')
                ->limit(3)
                ->get(),
            'testimonials' => Testimonial::query()->where('is_active', true)->latest()->limit(6)->get(),
            'faqs' => Faq::query()->where('is_active', true)->orderBy('sort_order')->limit(6)->get(),
            'stats' => $this->realStats(),
        ]);
    }

    public function catalog(Request $request, AvailabilityEngine $availability, ?string $categorySlug = null)
    {
        $filters = $this->validatedFilters($request);

        // /sewa-mobil/{categorySlug} is the canonical category filter; the
        // ?category= query param stays supported. Unknown slugs → 404.
        $categorySlug = $filters['category'] ?? $categorySlug;
        $activeCategory = null;
        if ($categorySlug !== null) {
            $activeCategory = Category::query()->where('is_active', true)->where('slug', $categorySlug)->first();
            abort_unless($activeCategory !== null, 404, 'Kategori kendaraan tidak ditemukan.');
        }

        $query = Vehicle::query()
            ->with(['category', 'brand', 'location', 'photos'])
            ->where('is_active', true)
            ->when($activeCategory, fn (Builder $q) => $q->where('category_id', $activeCategory->id))
            ->when($filters['location'], fn (Builder $q, $slug) => $q->whereHas(
                'location',
                fn (Builder $l) => $l->where('slug', $slug)
            ))
            ->when($filters['transmission'], fn (Builder $q, $t) => $q->where('transmission', $t))
            ->when($filters['fuel'], fn (Builder $q, $f) => $q->where('fuel_type', $f))
            ->when($filters['min_price'] !== null, fn (Builder $q) => $q->where('daily_rate', '>=', $filters['min_price']))
            ->when($filters['max_price'] !== null, fn (Builder $q) => $q->where('daily_rate', '<=', $filters['max_price']))
            ->when($filters['seats'] !== null, fn (Builder $q) => $q->where('seat_count', '>=', $filters['seats']))
            ->when($filters['q'] !== null, fn (Builder $q, $term) => $q->where(fn (Builder $w) => $w
                ->where('name', 'like', "%{$term}%")
                ->orWhereHas('brand', fn (Builder $b) => $b->where('name', 'like', "%{$term}%"))));

        // Period availability: exclude vehicles with active bookings/orders or
        // maintenance/out-of-service status overlapping the requested window.
        if ($filters['period'] !== null) {
            [$start, $end] = $filters['period'];

            if ($filters['available_only']) {
                $blockedIds = $availability->blockedVehicleIds($start, $end);

                $query->whereNotIn('id', $blockedIds)
                    ->where('status', 'available');
            }
        } elseif ($filters['available_only']) {
            $query->where('status', 'available');
        }

        $sort = $this->sortClause($filters['sort']);

        $vehicles = $query
            ->orderByRaw($sort['sql'], $sort['bindings'])
            ->paginate(12)
            ->withQueryString();

        return response()->view('storefront.catalog', [
            'vehicles' => $vehicles,
            'categories' => Category::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'locations' => Location::active()->orderBy('name')->get(),
            'activeCategory' => $activeCategory,
            'filters' => $filters,
            'priceBounds' => $this->priceBounds(),
        ]);
    }

    public function show(Request $request, Vehicle $vehicle, AvailabilityEngine $availability)
    {
        abort_unless($vehicle->is_active, 404);

        $vehicle->load(['category', 'brand', 'location', 'photos']);

        $filters = $this->validatedFilters($request);
        [$start, $end] = $filters['period'] ?? [today(), today()->addDays(2)];

        $check = $availability->checkAvailability(
            $vehicle,
            CarbonImmutable::parse($start)->toDateString(),
            CarbonImmutable::parse($end)->toDateString()
        );

        $related = Vehicle::query()
            ->with(['category', 'brand', 'location', 'photos'])
            ->where('is_active', true)
            ->where('id', '!=', $vehicle->id)
            ->where(function (Builder $q) use ($vehicle) {
                $q->where('category_id', $vehicle->category_id)
                    ->orWhereBetween('daily_rate', [
                        (float) $vehicle->daily_rate * 0.7,
                        (float) $vehicle->daily_rate * 1.3,
                    ]);
            })
            ->orderByRaw('CASE WHEN category_id = ? THEN 0 ELSE 1 END', [$vehicle->category_id])
            ->limit(3)
            ->get();

        return response()->view('storefront.vehicle-detail', [
            'vehicle' => $vehicle,
            'gallery' => $vehicle->photos->values(),
            'cover' => $vehicle->photos->firstWhere('is_primary') ?? $vehicle->photos->first(),
            'check' => $check,
            'related' => $related,
            'locations' => Location::active()->orderBy('name')->get(),
            'search' => [
                'pickup_date' => CarbonImmutable::parse($start)->toDateString(),
                'return_date' => CarbonImmutable::parse($end)->toDateString(),
                'pickup_time' => $filters['pickup_time'],
                'return_time' => $filters['return_time'],
                'rental_type' => $filters['rental_type'],
                'location' => $filters['location'],
            ],
        ]);
    }

    /**
     * Cars available for the requested period with precomputed booking URLs,
     * used by the homepage search bar result flow.
     */
    public function search(Request $request)
    {
        return redirect()->route('storefront.catalog', array_filter([
            'location' => $request->query('location'),
            'pickup_date' => $request->query('pickup_date'),
            'return_date' => $request->query('return_date'),
            'pickup_time' => $request->query('pickup_time'),
            'return_time' => $request->query('return_time'),
            'rental_type' => $request->query('rental_type'),
            'available_only' => 1,
        ], fn ($v) => filled($v)));
    }

    public function locations()
    {
        return response()->view('storefront.locations', [
            'locations' => Location::query()
                ->active()
                ->with(['vehicles' => fn (Builder $q) => $q->where('is_active', true)->with('photos')])
                ->orderBy('city')
                ->get(),
        ]);
    }

    private function validatedFilters(Request $request): array
    {
        $validated = $request->validate([
            'location' => ['nullable', 'string', 'max:60'],
            'category' => ['nullable', 'string', 'max:60'],
            'transmission' => ['nullable', 'in:manual,automatic'],
            'fuel' => ['nullable', 'in:pertalite,pertamax,premium,diesel,electric'],
            'min_price' => ['nullable', 'integer', 'min:0'],
            'max_price' => ['nullable', 'integer', 'min:0'],
            'seats' => ['nullable', 'integer', 'in:2,4,5,6,7,10'],
            'sort' => ['nullable', 'in:'.implode(',', array_keys(self::SORT_MAP))],
            'q' => ['nullable', 'string', 'max:80'],
            'available_only' => ['nullable', 'boolean'],
            'pickup_date' => ['nullable', 'date', 'after_or_equal:today'],
            'return_date' => ['nullable', 'date', 'after:today'],
            'pickup_time' => ['nullable', 'date_format:H:i'],
            'return_time' => ['nullable', 'date_format:H:i'],
            'rental_type' => ['nullable', 'in:self_drive,with_driver'],
        ], [], [
            'pickup_date' => 'tanggal ambil',
            'return_date' => 'tanggal kembali',
        ]);

        $pickupDate = filled($validated['pickup_date'] ?? null) ? CarbonImmutable::parse($validated['pickup_date']) : null;
        $returnDate = filled($validated['return_date'] ?? null) ? CarbonImmutable::parse($validated['return_date']) : null;

        if ($pickupDate && $returnDate && $returnDate->lessThanOrEqualTo($pickupDate)) {
            $returnDate = $pickupDate->addDays(2);
        }

        $period = ($pickupDate && $returnDate) ? [$pickupDate, $returnDate] : null;

        return [
            'location' => $validated['location'] ?? null,
            'category' => $validated['category'] ?? null,
            'transmission' => $validated['transmission'] ?? null,
            'fuel' => $validated['fuel'] ?? null,
            'min_price' => $validated['min_price'] ?? null,
            'max_price' => $validated['max_price'] ?? null,
            'seats' => $validated['seats'] ?? null,
            'sort' => $validated['sort'] ?? 'recommended',
            'q' => $validated['q'] ?? null,
            'available_only' => (bool) ($validated['available_only'] ?? false),
            'pickup_time' => $validated['pickup_time'] ?? '08:00',
            'return_time' => $validated['return_time'] ?? '18:00',
            'rental_type' => $validated['rental_type'] ?? 'self_drive',
            'period' => $period,
        ];
    }

    private function sortClause(string $sort): array
    {
        return match ($sort) {
            'price_low' => ['sql' => 'daily_rate asc', 'bindings' => []],
            'price_high' => ['sql' => 'daily_rate desc', 'bindings' => []],
            'newest' => ['sql' => 'year desc, id desc', 'bindings' => []],
            default => ['sql' => 'CASE WHEN status = ? THEN 0 ELSE 1 END, daily_rate asc', 'bindings' => ['available']],
        };
    }

    private function priceBounds(): array
    {
        return Vehicle::query()
            ->where('is_active', true)
            ->selectRaw('MIN(daily_rate) as min_price, MAX(daily_rate) as max_price')
            ->first()
            ?->toArray() ?? ['min_price' => 0, 'max_price' => 0];
    }

    /**
     * Real, database-backed counters for the homepage. Returns null values
     * when there is not enough data to display honestly.
     */
    private function realStats(): array
    {
        $vehicles = Vehicle::query()->where('is_active', true)->count();
        $locations = Location::query()->where('is_active', true)->count();

        return [
            'vehicles' => $vehicles > 0 ? $vehicles : null,
            'locations' => $locations > 0 ? $locations : null,
            'categories' => Category::query()->where('is_active', true)->count() ?: null,
        ];
    }
}

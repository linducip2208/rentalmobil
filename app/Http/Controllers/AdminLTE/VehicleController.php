<?php

namespace App\Http\Controllers\AdminLTE;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Location;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class VehicleController extends Controller
{
    public function index(Request $request): View
    {
        $vehicles = Vehicle::query()
            ->with(['category', 'brand', 'location', 'photos'])
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$request->string('q')}%")
                ->orWhere('plate_number', 'like', "%{$request->string('q')}%")))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('lte.vehicles.index', [
            'vehicles' => $vehicles,
            'statuses' => ['available' => 'Tersedia', 'reserved' => 'Direservasi', 'preparing' => 'Disiapkan', 'rented' => 'Disewa', 'overdue' => 'Terlambat', 'inspection' => 'Inspeksi', 'cleaning' => 'Pembersihan', 'maintenance' => 'Servis', 'damaged' => 'Rusak', 'inactive' => 'Tidak Aktif'],
        ]);
    }

    public function create(): View
    {
        return view('lte.vehicles.create', $this->formOptions());
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = Str::slug($data['name']).'-'.Str::random(4);
        $data['photo_url'] = $this->storeCover($request);

        Vehicle::create($data);

        return redirect()->route('lte.vehicles.index')->with('status', 'Kendaraan ditambahkan.');
    }

    public function edit(Vehicle $vehicle): View
    {
        return view('lte.vehicles.edit', array_merge(
            ['vehicle' => $vehicle->load('photos')],
            $this->formOptions()
        ));
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $data = $this->validated($request);
        unset($data['slug']);

        if ($cover = $this->storeCover($request)) {
            $data['photo_url'] = $cover;
        }

        $vehicle->update($data);

        return redirect()->route('lte.vehicles.index')->with('status', 'Kendaraan diperbarui.');
    }

    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();

        return redirect()->route('lte.vehicles.index')->with('status', 'Kendaraan dihapus.');
    }

    private function formOptions(): array
    {
        return [
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
            'brands' => Brand::where('is_active', true)->orderBy('name')->get(),
            'locations' => Location::active()->orderBy('name')->get(),
        ];
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'category_id' => ['required', 'exists:categories,id'],
            'brand_id' => ['required', 'exists:brands,id'],
            'location_id' => ['required', 'exists:locations,id'],
            'plate_number' => ['required', 'string', 'max:20'],
            'year' => ['required', 'integer', 'min:1990', 'max:'.(now()->year + 1)],
            'color' => ['nullable', 'string', 'max:50'],
            'transmission' => ['required', 'in:manual,automatic'],
            'fuel_type' => ['required', 'in:pertalite,pertamax,premium,diesel,electric'],
            'seat_count' => ['required', 'integer', 'min:1', 'max:60'],
            'mileage' => ['nullable', 'integer', 'min:0'],
            'daily_rate' => ['required', 'numeric', 'min:0'],
            'weekly_rate' => ['nullable', 'numeric', 'min:0'],
            'monthly_rate' => ['nullable', 'numeric', 'min:0'],
            'deposit_amount' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:available,reserved,preparing,rented,overdue,inspection,cleaning,maintenance,damaged,inactive'],
            'is_active' => ['nullable', 'boolean'],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);
    }

    private function storeCover(Request $request): ?string
    {
        if (! $request->hasFile('cover') || ! $request->file('cover')->isValid()) {
            return null;
        }

        return $request->file('cover')->store('vehicles/'.now()->format('Y/m'), 'public');
    }
}

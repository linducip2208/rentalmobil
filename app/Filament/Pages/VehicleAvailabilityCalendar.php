<?php

namespace App\Filament\Pages;

use App\Models\Booking;
use App\Models\Category;
use App\Models\Location;
use App\Models\MaintenanceLog;
use App\Models\RentalOrder;
use App\Models\Vehicle;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;

class VehicleAvailabilityCalendar extends Page
{
    protected string $view = 'filament.pages.vehicle-availability';

    protected static \UnitEnum|string|null $navigationGroup = '🚗 Master Data';
    protected static ?string $navigationLabel = 'Kalender Ketersediaan';
    protected static ?int $navigationSort = 15;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $title = 'Kalender Ketersediaan Kendaraan';

    public string $currentMonth;
    public ?int $locationId = null;
    public ?int $categoryId = null;
    public ?string $selectedDate = null;

    public function mount(): void
    {
        $this->currentMonth = request()->query('month', now()->format('Y-m'));
        $this->selectedDate = request()->query('date');
    }

    public function getDaysInMonthProperty(): array
    {
        $start = Carbon::parse($this->currentMonth . '-01')->startOfMonth();
        $end = $start->copy()->endOfMonth();
        $days = [];

        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $days[] = $d->format('Y-m-d');
        }

        return $days;
    }

    public function getVehiclesProperty(): array
    {
        $query = Vehicle::query()->where('is_active', true);

        if ($this->locationId) {
            $query->where('location_id', $this->locationId);
        }
        if ($this->categoryId) {
            $query->where('category_id', $this->categoryId);
        }

        return $query->orderBy('name')->get()->toArray();
    }

    public function getAvailabilityMapProperty(): array
    {
        $start = Carbon::parse($this->currentMonth . '-01')->startOfMonth();
        $end = $start->copy()->endOfMonth();
        $map = [];

        $vehicles = $this->getVehiclesProperty();

        foreach ($vehicles as $vehicle) {
            $map[$vehicle['id']] = [
                'name' => $vehicle['name'],
                'plate' => $vehicle['plate_number'],
                'status' => $vehicle['status'],
                'days' => [],
            ];

            for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                $map[$vehicle['id']]['days'][$d->format('Y-m-d')] = [
                    'status' => 'available',
                    'info' => null,
                ];
            }
        }

        $vehicleIds = array_column($vehicles, 'id');

        if (empty($vehicleIds)) {
            return $map;
        }

        $bookings = Booking::query()
            ->whereIn('vehicle_id', $vehicleIds)
            ->whereIn('status', ['confirmed', 'active'])
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_date', [$start, $end])
                    ->orWhereBetween('end_date', [$start, $end])
                    ->orWhere(function ($sub) use ($start, $end) {
                        $sub->where('start_date', '<', $start)
                            ->where('end_date', '>', $end);
                    });
            })
            ->get();

        foreach ($bookings as $booking) {
            $bookingStart = Carbon::parse($booking->start_date)->startOfDay();
            $bookingEnd = Carbon::parse($booking->end_date)->startOfDay();

            for ($d = $bookingStart->copy(); $d->lte($bookingEnd); $d->addDay()) {
                $dayKey = $d->format('Y-m-d');
                if (isset($map[$booking->vehicle_id]['days'][$dayKey])) {
                    $map[$booking->vehicle_id]['days'][$dayKey] = [
                        'status' => 'booked',
                        'info' => [
                            'type' => 'booking',
                            'number' => $booking->booking_number,
                            'customer' => $booking->customer?->name ?? '-',
                        ],
                    ];
                }
            }
        }

        $maintLogs = MaintenanceLog::query()
            ->whereIn('vehicle_id', $vehicleIds)
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_date', [$start, $end])
                    ->orWhere(function ($sub) use ($start, $end) {
                        $sub->where('start_date', '<', $start)
                            ->where(function ($inner) use ($end) {
                                $inner->whereNull('end_date')
                                    ->orWhere('end_date', '>', $end);
                            });
                    });
            })
            ->get();

        foreach ($maintLogs as $log) {
            $logStart = Carbon::parse($log->start_date)->startOfDay();
            $logEnd = $log->end_date
                ? Carbon::parse($log->end_date)->startOfDay()
                : $end->copy();

            for ($d = $logStart->copy(); $d->lte($logEnd); $d->addDay()) {
                $dayKey = $d->format('Y-m-d');
                if (isset($map[$log->vehicle_id]['days'][$dayKey])) {
                    if ($map[$log->vehicle_id]['days'][$dayKey]['status'] !== 'booked') {
                        $map[$log->vehicle_id]['days'][$dayKey] = [
                            'status' => 'maintenance',
                            'info' => [
                                'type' => 'maintenance',
                                'title' => $log->title,
                                'status' => $log->status,
                            ],
                        ];
                    }
                }
            }
        }

        return $map;
    }

    public function getSelectedDateBookingsProperty(): array
    {
        if (! $this->selectedDate) {
            return [];
        }

        return Booking::with(['customer', 'vehicle'])
            ->where('status', '!=', 'cancelled')
            ->whereDate('start_date', '<=', $this->selectedDate)
            ->whereDate('end_date', '>=', $this->selectedDate)
            ->get()
            ->toArray();
    }

    public function getPrevMonthProperty(): string
    {
        return Carbon::parse($this->currentMonth . '-01')->subMonth()->format('Y-m');
    }

    public function getNextMonthProperty(): string
    {
        return Carbon::parse($this->currentMonth . '-01')->addMonth()->format('Y-m');
    }

    public function getCurrentCarbonProperty(): Carbon
    {
        return Carbon::parse($this->currentMonth . '-01');
    }

    public function getLocationsProperty(): array
    {
        return Location::where('is_active', true)->orderBy('name')->pluck('name', 'id')->toArray();
    }

    public function getCategoriesProperty(): array
    {
        return Category::where('is_active', true)->orderBy('name')->pluck('name', 'id')->toArray();
    }
}

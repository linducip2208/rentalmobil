<?php

namespace App\Filament\Pages;

use App\Models\MaintenanceLog;
use App\Models\RentalOrder;
use App\Models\ServiceSchedule;
use App\Models\Vehicle;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class LaporanOperasional extends Page
{
    protected string $view = 'filament.pages.laporan-operasional';

    protected static \UnitEnum|string|null $navigationGroup = '📊 Laporan';
    protected static ?string $navigationLabel = 'Operasional';
    protected static ?int $navigationSort = 3;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $title = 'Laporan Operasional';

    public string $dateFrom;
    public string $dateTo;

    public function mount(): void
    {
        $this->dateFrom = now()->startOfMonth()->toDateString();
        $this->dateTo = now()->toDateString();
    }

    public function getVehicleStats(): array
    {
        $total = Vehicle::withTrashed()->count();
        $active = Vehicle::where('is_active', true)->count();
        $rented = Vehicle::where('status', 'rented')->count();
        $available = Vehicle::where('status', 'available')->count();
        $maintenance = Vehicle::where('status', 'maintenance')->count();

        return [
            'total' => $total,
            'active' => $active,
            'rented' => $rented,
            'available' => $available,
            'maintenance' => $maintenance,
            'utilization_pct' => $active > 0 ? round(($rented / $active) * 100, 1) : 0,
        ];
    }

    public function getRentalActivity(): array
    {
        return [
            'active_rentals' => RentalOrder::whereIn('status', ['active', 'checked_out'])->count(),
            'overdue_count' => RentalOrder::overdue()->count(),
            'completed_today' => RentalOrder::where('status', 'completed')
                ->whereDate('completed_at', now()->toDateString())->count(),
            'new_orders_today' => RentalOrder::whereDate('created_at', now()->toDateString())->count(),
            'return_due_today' => RentalOrder::where('status', 'active')
                ->whereDate('end_date', now()->toDateString())->count(),
        ];
    }

    public function getMaintenanceStats(): array
    {
        return [
            'scheduled' => MaintenanceLog::where('status', 'scheduled')->count(),
            'in_progress' => MaintenanceLog::where('status', 'in_progress')->count(),
            'completed_period' => MaintenanceLog::where('status', 'completed')
                ->whereBetween('end_date', [$this->dateFrom, $this->dateTo])->count(),
            'total_cost' => (float) MaintenanceLog::whereBetween('start_date', [$this->dateFrom, $this->dateTo])->sum('cost'),
        ];
    }

    public function getUtilizationByCategory(): array
    {
        $data = DB::table('categories')
            ->join('vehicles', 'categories.id', '=', 'vehicles.category_id')
            ->select(
                'categories.name',
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN vehicles.status = 'rented' THEN 1 ELSE 0 END) as rented"),
                DB::raw("SUM(CASE WHEN vehicles.status = 'available' THEN 1 ELSE 0 END) as available"),
                DB::raw("SUM(CASE WHEN vehicles.status = 'maintenance' THEN 1 ELSE 0 END) as in_maintenance")
            )
            ->groupBy('categories.id', 'categories.name')
            ->get();

        return [
            'labels' => $data->pluck('name')->toArray(),
            'rented' => $data->pluck('rented')->toArray(),
            'available' => $data->pluck('available')->toArray(),
            'maintenance' => $data->pluck('in_maintenance')->toArray(),
        ];
    }

    public function getMaintenanceSchedule(): array
    {
        return MaintenanceLog::with('vehicle')
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->orderBy('start_date')
            ->limit(15)
            ->get()
            ->toArray();
    }

    public function getOverdueVehicles(): array
    {
        return RentalOrder::with(['customer', 'vehicle'])
            ->overdue()
            ->orderBy('end_date')
            ->limit(15)
            ->get()
            ->toArray();
    }

    public function getUpcomingService(): array
    {
        return ServiceSchedule::with('vehicle')
            ->where('is_active', true)
            ->where('next_service_date', '<=', now()->addDays(14))
            ->orderBy('next_service_date')
            ->limit(10)
            ->get()
            ->toArray();
    }

    public function getVehicleProfitability(): array
    {
        return Vehicle::query()->select(['id', 'name', 'plate_number'])
            ->withSum(['rentalOrders as revenue' => fn ($q) => $q->whereBetween('start_date', [$this->dateFrom, $this->dateTo])->whereNotIn('status', ['cancelled'])], 'final_amount')
            ->withSum(['maintenanceLogs as maintenance_cost' => fn ($q) => $q->whereBetween('start_date', [$this->dateFrom, $this->dateTo])], 'cost')
            ->withSum(['fuelLogs as fuel_cost' => fn ($q) => $q->whereBetween('fuel_date', [$this->dateFrom, $this->dateTo])], 'cost')
            ->get()->map(function ($vehicle) {
                $revenue = (float) ($vehicle->revenue ?? 0); $cost = (float) ($vehicle->maintenance_cost ?? 0) + (float) ($vehicle->fuel_cost ?? 0);
                return ['name' => $vehicle->name, 'plate_number' => $vehicle->plate_number, 'revenue' => $revenue, 'cost' => $cost, 'profit' => $revenue - $cost, 'margin' => $revenue > 0 ? round((($revenue - $cost) / $revenue) * 100, 1) : 0];
            })->sortByDesc('profit')->values()->take(20)->all();
    }

    public function exportExcel()
    {
        return app(\App\Services\ReportExcelService::class)->download('laporan-operasional-'.$this->dateFrom.'-'.$this->dateTo, ['Kendaraan','Plat','Pendapatan','Biaya','Laba','Margin (%)'], $this->getVehicleProfitability());
    }
}

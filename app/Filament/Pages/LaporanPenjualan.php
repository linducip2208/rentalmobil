<?php

namespace App\Filament\Pages;

use App\Models\Category;
use App\Models\Location;
use App\Models\RentalOrder;
use App\Models\Vehicle;
use App\Services\ReportExcelService;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class LaporanPenjualan extends Page
{
    protected string $view = 'filament.pages.laporan-penjualan';

    protected static \UnitEnum|string|null $navigationGroup = 'Reports';

    protected static ?string $navigationLabel = 'Penjualan';

    protected static ?int $navigationSort = 1;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $title = 'Laporan Penjualan';

    public string $dateFrom;

    public string $dateTo;

    public string $groupBy = 'monthly';

    public ?int $locationId = null;

    public ?int $categoryId = null;

    public function mount(): void
    {
        $this->dateFrom = now()->startOfYear()->toDateString();
        $this->dateTo = now()->toDateString();
    }

    public function getSummary(): array
    {
        $query = RentalOrder::query()
            ->whereNotIn('status', ['draft', 'cancelled'])
            ->whereBetween('created_at', [$this->dateFrom, $this->dateTo]);

        if ($this->locationId) {
            $query->where('location_id', $this->locationId);
        }
        if ($this->categoryId) {
            $query->whereHas('vehicle', fn ($q) => $q->where('category_id', $this->categoryId));
        }

        $totalVehicles = Vehicle::where('is_active', true)->count();
        $rentedDuringPeriod = (clone $query)->distinct('vehicle_id')->count('vehicle_id');
        $occupancy = $totalVehicles > 0 ? round(($rentedDuringPeriod / $totalVehicles) * 100, 1) : 0;

        $clone = clone $query;

        return [
            'total_revenue' => (float) (clone $query)->sum('final_amount'),
            'total_orders' => (clone $clone)->count(),
            'avg_order_value' => (clone $clone)->count() > 0 ? round((float) (clone $clone)->avg('final_amount'), 0) : 0,
            'occupancy_rate' => $occupancy,
        ];
    }

    public function getRevenueByPeriod(): array
    {
        $format = match ($this->groupBy) {
            'daily' => '%Y-%m-%d',
            'weekly' => '%Y-%u',
            default => '%Y-%m',
        };

        $query = RentalOrder::query()
            ->whereNotIn('status', ['draft', 'cancelled'])
            ->whereBetween('created_at', [$this->dateFrom, $this->dateTo]);

        if ($this->locationId) {
            $query->where('location_id', $this->locationId);
        }
        if ($this->categoryId) {
            $query->whereHas('vehicle', fn ($q) => $q->where('category_id', $this->categoryId));
        }

        $data = $query
            ->select(
                DB::raw("DATE_FORMAT(created_at, '{$format}') as period"),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(final_amount) as revenue')
            )
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        return [
            'labels' => $data->pluck('period')->map(fn ($p) => $this->formatPeriodLabel($p))->toArray(),
            'revenue' => $data->pluck('revenue')->map(fn ($v) => (float) $v)->toArray(),
            'orders' => $data->pluck('order_count')->toArray(),
        ];
    }

    public function getOrders(): array
    {
        $query = RentalOrder::query()
            ->with(['customer', 'vehicle', 'location'])
            ->whereNotIn('status', ['draft', 'cancelled'])
            ->whereBetween('created_at', [$this->dateFrom, $this->dateTo]);

        if ($this->locationId) {
            $query->where('location_id', $this->locationId);
        }
        if ($this->categoryId) {
            $query->whereHas('vehicle', fn ($q) => $q->where('category_id', $this->categoryId));
        }

        return $query->latest()
            ->limit(50)
            ->get()
            ->toArray();
    }

    public function getLocations(): array
    {
        return Location::where('is_active', true)->orderBy('name')->pluck('name', 'id')->toArray();
    }

    public function getCategories(): array
    {
        return Category::where('is_active', true)->orderBy('name')->pluck('name', 'id')->toArray();
    }

    protected function formatPeriodLabel(string $period): string
    {
        if ($this->groupBy === 'daily') {
            return Carbon::parse($period)->translatedFormat('d M');
        }
        if ($this->groupBy === 'weekly') {
            [$year, $week] = explode('-', $period);

            return "W{$week} {$year}";
        }

        return Carbon::parse($period.'-01')->translatedFormat('M Y');
    }

    public function getFilters(): array
    {
        return [
            'daily' => 'Harian',
            'weekly' => 'Mingguan',
            'monthly' => 'Bulanan',
        ];
    }

    public function exportExcel()
    {
        $rows = collect($this->getOrders())->map(fn (array $order) => [$order['order_number'], $order['customer']['name'] ?? '-', $order['vehicle']['name'] ?? '-', $order['start_date'], $order['end_date'], $order['status'], (float) $order['final_amount']]);

        return app(ReportExcelService::class)->download('laporan-penjualan-'.$this->dateFrom.'-'.$this->dateTo, ['No. Order', 'Customer', 'Kendaraan', 'Mulai', 'Selesai', 'Status', 'Nilai'], $rows);
    }
}

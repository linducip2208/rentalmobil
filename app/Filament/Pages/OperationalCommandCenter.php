<?php

namespace App\Filament\Pages;

use App\Models\ApprovalWorkflow;
use App\Models\CustomerDocument;
use App\Models\GpsTracker;
use App\Models\Invoice;
use App\Models\RentalOrder;
use App\Models\ServiceSchedule;
use Filament\Pages\Page;

class OperationalCommandCenter extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-command-line';

    protected static \UnitEnum|string|null $navigationGroup = 'Dashboard';

    protected static ?string $navigationLabel = 'Pusat Kendali';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.operational-command-center';

    public function alerts(): array
    {
        $dots = [
            'red' => 'bg-red-500',
            'amber' => 'bg-amber-500',
            'blue' => 'bg-blue-500',
            'slate' => 'bg-slate-400',
            'emerald' => 'bg-emerald-500',
        ];

        $items = [
            ['label' => 'Rental terlambat', 'value' => RentalOrder::where('status', 'overdue')->count(), 'tone' => 'red', 'url' => '/admin/rental-orders?tableFilters[status][value]=overdue', 'hint' => 'Perlu penagihan / penarikan'],
            ['label' => 'Tracker offline', 'value' => GpsTracker::where('is_active', true)->where(fn ($q) => $q->whereNull('last_update_at')->orWhere('last_update_at', '<', now()->subMinutes(10)))->count(), 'tone' => 'amber', 'url' => '/admin/gps-trackers', 'hint' => 'Tidak update > 10 menit'],
            ['label' => 'Invoice jatuh tempo', 'value' => Invoice::overdue()->count(), 'tone' => 'red', 'url' => '/admin/invoices', 'hint' => 'Segera follow-up'],
            ['label' => 'Approval tertunda', 'value' => ApprovalWorkflow::where('status', 'pending')->count(), 'tone' => 'blue', 'url' => '/admin', 'hint' => 'Menunggu persetujuan'],
            ['label' => 'Servis 14 hari', 'value' => ServiceSchedule::where('is_active', true)->whereBetween('next_service_date', [today(), today()->addDays(14)])->count(), 'tone' => 'amber', 'url' => '/admin/service-schedules', 'hint' => 'Jadwalkan bengkel'],
            ['label' => 'Dokumen kedaluwarsa', 'value' => CustomerDocument::whereDate('expiry_date', '<=', today()->addDays(30))->whereNotNull('expiry_date')->count(), 'tone' => 'slate', 'url' => '/admin/customer-documents', 'hint' => '≤ 30 hari'],
        ];

        return array_map(fn ($a) => $a + ['dot' => $dots[$a['tone']] ?? 'bg-slate-400'], $items);
    }

    public function totalUrgent(): int
    {
        return collect($this->alerts())->sum('value');
    }
}

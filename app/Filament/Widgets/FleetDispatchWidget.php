<?php

namespace App\Filament\Widgets;

use App\Models\ApprovalWorkflow;
use App\Models\Booking;
use App\Models\GpsTracker;
use App\Models\RentalOrder;
use App\Models\ServiceSchedule;
use Filament\Widgets\Widget;

class FleetDispatchWidget extends Widget
{
    protected static ?int $sort = -20;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.fleet-dispatch';

    public function data(): array
    {
        $today = today();

        return [
            'user' => auth()->user(),
            'departures' => RentalOrder::with(['vehicle', 'customer'])->whereDate('start_date', $today)->whereNotIn('status', ['cancelled'])->orderBy('start_date')->limit(6)->get(),
            'returns' => RentalOrder::with(['vehicle', 'customer'])->whereDate('end_date', $today)->whereNotIn('status', ['cancelled', 'completed'])->orderBy('end_date')->limit(6)->get(),
            'overdue' => RentalOrder::where('status', 'overdue')->count(),
            'offline' => GpsTracker::where('is_active', true)->where(fn ($q) => $q->whereNull('last_update_at')->orWhere('last_update_at', '<', now()->subMinutes(10)))->count(),
            'approvals' => in_array(auth()->user()?->role, ['super_admin', 'owner', 'manager', 'admin'], true) ? ApprovalWorkflow::where('status', 'pending')->count() : null,
            'serviceDue' => ServiceSchedule::where('is_active', true)->whereBetween('next_service_date', [$today, $today->copy()->addDays(14)])->count(),
            'pipeline' => Booking::whereIn('status', ['inquiry', 'quoted', 'hold', 'pending_verification', 'pending_payment'])->count(),
        ];
    }
}

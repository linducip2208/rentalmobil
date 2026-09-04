<?php

namespace App\Http\Controllers\AdminLTE;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\RentalOrder;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $monthStart = now()->startOfMonth();

        return view('lte.dashboard', [
            'fleetTotal' => Vehicle::count(),
            'fleetAvailable' => Vehicle::where('status', 'available')->where('is_active', true)->count(),
            'fleetOut' => Vehicle::whereIn('status', ['maintenance', 'damaged', 'inspection', 'cleaning'])->count(),
            'bookingsPending' => Booking::whereIn('status', ['pending_verification', 'pending_payment'])->count(),
            'bookingsActive' => Booking::where('status', 'confirmed')->count(),
            'ordersActive' => RentalOrder::whereIn('status', ['ready_for_preparation', 'preparing', 'ready_for_handover', 'checked_out', 'active', 'overdue'])->count(),
            'ordersOverdue' => RentalOrder::where('status', 'overdue')->count(),
            'customersTotal' => Customer::where('is_active', true)->count(),
            'revenueThisMonth' => (float) Invoice::where('status', 'paid')->where('created_at', '>=', $monthStart)->sum('total_amount'),
            'outstanding' => (float) Invoice::whereIn('status', ['issued', 'partially_paid'])->sum('balance_due'),
            'recentBookings' => Booking::with(['vehicle', 'customer'])->latest()->limit(8)->get(),
            'upcomingPickups' => RentalOrder::with(['vehicle', 'customer'])
                ->whereIn('status', ['ready_for_preparation', 'preparing', 'ready_for_handover'])
                ->where('start_date', '>=', today())
                ->orderBy('start_date')
                ->limit(8)
                ->get(),
        ]);
    }
}

<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        $customer = auth('customer')->user()->customer;

        $stats = [
            'active_orders' => $customer->orders()->whereIn('status', ['confirmed', 'active', 'dispatched'])->count(),
            'total_spent' => $customer->total_spent,
            'trust_score' => $customer->trust_score,
            'loyalty_tier' => ucfirst($customer->loyalty_tier),
        ];

        $recentOrders = $customer->orders()
            ->with('vehicle')
            ->latest()
            ->take(5)
            ->get();

        return view('portal.dashboard', compact('customer', 'stats', 'recentOrders'));
    }
}

<?php

namespace App\Http\Controllers\AdminLTE;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\RentalOrder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DriverController extends Controller
{
    public function index(Request $request): View
    {
        $drivers = Driver::query()
            ->with('location')
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', '%'.$request->string('q').'%')
                ->orWhere('phone', 'like', '%'.$request->string('q').'%')
                ->orWhere('sim_number', 'like', '%'.$request->string('q').'%')))
            ->when($request->filled('availability'), fn ($q) => $q->where('is_available', $request->string('availability') === '1'))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('lte.drivers.index', ['drivers' => $drivers]);
    }

    public function show(Driver $driver): View
    {
        return view('lte.drivers.show', [
            'driver' => $driver->load('location'),
            'orders' => RentalOrder::with(['vehicle', 'customer'])
                ->where('driver_id', $driver->id)
                ->latest()
                ->limit(10)
                ->get(),
        ]);
    }

    public function toggleAvailability(Driver $driver)
    {
        $driver->update(['is_available' => ! $driver->is_available]);

        return back()->with('status', $driver->is_available
            ? "{$driver->name} ditandai tersedia."
            : "{$driver->name} ditandai tidak tersedia.");
    }
}

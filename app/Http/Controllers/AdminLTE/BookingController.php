<?php

namespace App\Http\Controllers\AdminLTE;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(Request $request): View
    {
        $bookings = Booking::query()
            ->with(['vehicle', 'customer'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('q'), fn ($q) => $q->where('booking_number', 'like', '%'.$request->string('q').'%'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('lte.bookings.index', [
            'bookings' => $bookings,
            'statuses' => [
                'hold' => 'Ditahan Sementara',
                'pending_verification' => 'Menunggu Verifikasi',
                'pending_payment' => 'Menunggu Pembayaran',
                'confirmed' => 'Dikonfirmasi',
                'converted' => 'Menjadi Order',
                'expired' => 'Kedaluwarsa',
                'cancelled' => 'Dibatalkan',
            ],
        ]);
    }

    public function show(Booking $booking): View
    {
        return view('lte.bookings.show', [
            'booking' => $booking->load(['vehicle.category', 'vehicle.brand', 'customer', 'pickupLocation', 'returnLocation']),
        ]);
    }

    public function confirm(Booking $booking, BookingService $service)
    {
        try {
            $service->confirmBooking($booking);

            return back()->with('status', "Booking {$booking->booking_number} dikonfirmasi.");
        } catch (\RuntimeException $e) {
            return back()->with('status', 'Gagal: '.$e->getMessage())->with('alert', 'danger');
        }
    }

    public function cancel(Request $request, Booking $booking, BookingService $service)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:500']]);

        try {
            $service->cancelBooking($booking, $data['reason']);

            return back()->with('status', "Booking {$booking->booking_number} dibatalkan.");
        } catch (\RuntimeException $e) {
            return back()->with('status', 'Gagal: '.$e->getMessage())->with('alert', 'danger');
        }
    }

    public function convert(Booking $booking, BookingService $service)
    {
        try {
            $order = $service->convertToOrder($booking);

            return redirect()
                ->route('lte.orders.show', $order)
                ->with('status', "Booking dikonversi menjadi order {$order->order_number}.");
        } catch (\RuntimeException $e) {
            return back()->with('status', 'Gagal: '.$e->getMessage())->with('alert', 'danger');
        }
    }
}

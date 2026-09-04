<?php

namespace App\Http\Controllers\AdminLTE;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $customers = Customer::query()
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', '%'.$request->string('q').'%')
                ->orWhere('email', 'like', '%'.$request->string('q').'%')
                ->orWhere('phone', 'like', '%'.$request->string('q').'%')))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('lte.customers.index', ['customers' => $customers]);
    }

    public function show(Customer $customer): View
    {
        return view('lte.customers.show', [
            'customer' => $customer->loadCount(['rentalOrders', 'invoices']),
            'orders' => $customer->rentalOrders()->with('vehicle')->latest()->limit(10)->get(),
            'invoices' => $customer->invoices()->latest()->limit(10)->get(),
            'documents' => $customer->documents()->latest()->get(),
        ]);
    }
}

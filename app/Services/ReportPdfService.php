<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Location;
use App\Models\RentalOrder;
use App\Models\DamageReport;
use App\Models\Vehicle;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReportPdfService
{
    public function generateSalesReport(Carbon $startDate, Carbon $endDate, ?int $locationId = null): Pdf
    {
        $orders = RentalOrder::query()
            ->whereBetween('start_date', [$startDate, $endDate])
            ->whereIn('status', ['completed', 'active'])
            ->with(['customer', 'vehicle', 'location']);

        if ($locationId) {
            $orders->where('location_id', $locationId);
        }

        $orders = $orders->get();

        $totalRevenue = (float) $orders->sum('total_amount');
        $totalPaid = (float) $orders->sum('amount_paid');
        $totalDeposits = (float) $orders->sum('deposit_amount');
        $orderCount = $orders->count();
        $avgOrderValue = $orderCount > 0 ? $totalRevenue / $orderCount : 0;

        $dailyBreakdown = $orders->groupBy(function ($order) {
            return $order->start_date->format('Y-m-d');
        })->map(function ($dayOrders) {
            return [
                'count' => $dayOrders->count(),
                'revenue' => round((float) $dayOrders->sum('total_amount'), 2),
            ];
        })->sortKeys();

        $topVehicles = $orders->groupBy('vehicle_id')
            ->map(function ($vehicleOrders) {
                $vehicle = $vehicleOrders->first()->vehicle;
                return [
                    'name' => $vehicle->name ?? 'N/A',
                    'plate' => $vehicle->license_plate ?? 'N/A',
                    'orders' => $vehicleOrders->count(),
                    'revenue' => round((float) $vehicleOrders->sum('total_amount'), 2),
                ];
            })
            ->sortByDesc('revenue')
            ->take(10)
            ->values();

        $locationName = $locationId
            ? Location::find($locationId)?->name ?? 'Semua Lokasi'
            : 'Semua Lokasi';

        $data = [
            'title' => 'Laporan Penjualan',
            'period' => "{$startDate->format('d M Y')} - {$endDate->format('d M Y')}",
            'location' => $locationName,
            'generated_at' => now()->format('d M Y H:i'),
            'summary' => [
                'total_revenue' => $totalRevenue,
                'total_paid' => $totalPaid,
                'total_deposits' => $totalDeposits,
                'order_count' => $orderCount,
                'avg_order_value' => round($avgOrderValue, 2),
            ],
            'daily_breakdown' => $dailyBreakdown,
            'top_vehicles' => $topVehicles,
            'orders' => $orders->map(fn($o) => [
                'order_number' => $o->order_number,
                'customer_name' => $o->customer->name ?? 'N/A',
                'vehicle_name' => $o->vehicle->name ?? 'N/A',
                'start_date' => $o->start_date->format('d/m/Y'),
                'end_date' => $o->end_date->format('d/m/Y'),
                'total_amount' => (float) $o->total_amount,
                'amount_paid' => (float) $o->amount_paid,
                'status' => $o->status,
            ]),
        ];

        return Pdf::loadView('pdf.laporan-penjualan', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('isRemoteEnabled', true);
    }

    public function generateFinancialReport(Carbon $startDate, Carbon $endDate): Pdf
    {
        $invoices = Invoice::whereBetween('created_at', [$startDate, $endDate])->get();

        $totalIncome = (float) $invoices->where('status', 'paid')->sum('total_amount');
        $totalPartial = (float) $invoices->where('status', 'partial')->sum('total_amount');
        $totalOutstanding = (float) $invoices->where('status', '!=', 'paid')->sum('total_amount')
            - (float) $invoices->where('status', '!=', 'paid')->sum('amount_paid');

        $expenses = Expense::whereBetween('expense_date', [$startDate, $endDate])
            ->where('status', 'approved')
            ->with('category')
            ->get();

        $totalExpenses = (float) $expenses->sum('amount');
        $netIncome = $totalIncome - $totalExpenses;

        $expensesByCategory = $expenses->groupBy(function ($expense) {
            return $expense->category->name ?? 'Lainnya';
        })->map(function ($group) {
            return [
                'count' => $group->count(),
                'total' => round((float) $group->sum('amount'), 2),
            ];
        })->sortByDesc('total');

        $accountsReceivable = $invoices->where('status', '!=', 'paid')->map(function ($inv) {
            return [
                'invoice_number' => $inv->invoice_number,
                'customer_name' => $inv->customer->name ?? 'N/A',
                'total_amount' => (float) $inv->total_amount,
                'amount_paid' => (float) $inv->amount_paid,
                'balance_due' => round((float) $inv->total_amount - (float) $inv->amount_paid, 2),
                'due_date' => $inv->due_date->format('d/m/Y'),
                'is_overdue' => $inv->due_date->isPast(),
            ];
        });

        $data = [
            'title' => 'Laporan Keuangan',
            'period' => "{$startDate->format('d M Y')} - {$endDate->format('d M Y')}",
            'generated_at' => now()->format('d M Y H:i'),
            'profit_loss' => [
                'revenue' => $totalIncome,
                'partial_income' => $totalPartial,
                'total_expenses' => $totalExpenses,
                'net_income' => $netIncome,
                'margin' => $totalIncome > 0 ? round(($netIncome / $totalIncome) * 100, 1) : 0,
            ],
            'accounts_receivable' => [
                'total' => $totalOutstanding,
                'count' => $accountsReceivable->count(),
                'overdue_count' => $accountsReceivable->filter(fn($i) => $i['is_overdue'])->count(),
                'items' => $accountsReceivable,
            ],
            'expenses_by_category' => $expensesByCategory,
            'cash_flow' => [
                'collected' => (float) $invoices->sum('amount_paid'),
                'spent' => $totalExpenses,
            ],
        ];

        return Pdf::loadView('pdf.laporan-keuangan', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('isRemoteEnabled', true);
    }

    public function generateOperationalReport(Carbon $startDate, Carbon $endDate): Pdf
    {
        $orders = RentalOrder::whereBetween('start_date', [$startDate, $endDate])
            ->whereIn('status', ['completed', 'active', 'overdue'])
            ->with(['vehicle', 'customer'])
            ->get();

        $vehicles = Vehicle::where('is_active', true)->get();
        $totalVehicles = $vehicles->count();

        $ordersPerDay = $orders->groupBy(fn($o) => $o->start_date->format('Y-m-d'));
        $avgDailyOrders = $ordersPerDay->count() > 0
            ? round($orders->count() / $ordersPerDay->count(), 1)
            : 0;

        $overdueOrders = $orders->where('status', 'overdue');
        $damageReports = DamageReport::whereBetween('created_at', [$startDate, $endDate])->get();

        $avgDuration = $orders->count() > 0
            ? round($orders->avg('duration_days'), 1)
            : 0;

        $totalDays = max(1, $startDate->diffInDays($endDate));
        $utilization = $totalVehicles > 0
            ? round(($orders->count() / ($totalVehicles * $totalDays)) * 100, 1)
            : 0;

        $vehiclePerformance = $vehicles->map(function ($vehicle) use ($orders) {
            $vehicleOrders = $orders->where('vehicle_id', $vehicle->id);
            return [
                'name' => $vehicle->name,
                'plate' => $vehicle->license_plate,
                'total_orders' => $vehicleOrders->count(),
                'total_revenue' => round((float) $vehicleOrders->sum('total_amount'), 2),
                'avg_duration' => $vehicleOrders->count() > 0
                    ? round($vehicleOrders->avg('duration_days'), 1)
                    : 0,
                'status' => $vehicle->status,
            ];
        })->sortByDesc('total_orders')->take(15)->values();

        $data = [
            'title' => 'Laporan Operasional',
            'period' => "{$startDate->format('d M Y')} - {$endDate->format('d M Y')}",
            'generated_at' => now()->format('d M Y H:i'),
            'summary' => [
                'total_vehicles' => $totalVehicles,
                'total_orders' => $orders->count(),
                'avg_daily_orders' => $avgDailyOrders,
                'avg_duration' => $avgDuration,
                'utilization_rate' => $utilization,
                'overdue_count' => $overdueOrders->count(),
                'damage_count' => $damageReports->count(),
            ],
            'vehicle_performance' => $vehiclePerformance,
            'damage_summary' => [
                'total_reports' => $damageReports->count(),
                'by_severity' => $damageReports->groupBy('severity')->map(fn($g) => $g->count()),
                'total_estimated_cost' => round((float) $damageReports->sum('estimated_cost'), 2),
            ],
            'overdue_orders' => $overdueOrders->map(fn($o) => [
                'order_number' => $o->order_number,
                'customer_name' => $o->customer->name ?? 'N/A',
                'vehicle_name' => $o->vehicle->name ?? 'N/A',
                'end_date' => $o->end_date->format('d/m/Y'),
                'days_overdue' => now()->diffInDays($o->end_date),
            ]),
        ];

        return Pdf::loadView('pdf.laporan-operasional', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('isRemoteEnabled', true);
    }

    public function generateInvoicePdf(Invoice $invoice): Pdf
    {
        $invoice->load(['rentalOrder.vehicle', 'customer', 'payments.paymentMethod', 'rentalOrder.location']);

        $data = [
            'invoice' => $invoice,
            'order' => $invoice->rentalOrder,
            'vehicle' => $invoice->rentalOrder?->vehicle,
            'customer' => $invoice->customer,
            'payments' => $invoice->payments,
            'balance_due' => round((float) $invoice->total_amount - (float) $invoice->amount_paid, 2),
            'generated_at' => now()->format('d M Y H:i'),
        ];

        $pdf = Pdf::loadView('pdf.invoice', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('isRemoteEnabled', true);

        $filename = "Invoice-{$invoice->invoice_number}.pdf";
        $path = storage_path("app/invoices/{$filename}");

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $pdf->save($path);
        $invoice->update(['pdf_path' => "invoices/{$filename}"]);

        return $pdf;
    }
}

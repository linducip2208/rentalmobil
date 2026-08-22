<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Vehicle;
use App\Services\NotificationDispatcher;
use Illuminate\Console\Command;

class RemindVehicleDocuments extends Command
{
    protected $signature = 'vehicles:remind-documents';

    protected $description = 'Kirim reminder dokumen kendaraan (STNK/pajak/KIR) yang mendekati jatuh tempo H-30 / H-7';

    public function handle(NotificationDispatcher $dispatcher): int
    {
        $targets = [
            '30' => now()->addDays(30),
            '7' => now()->addDays(7),
        ];

        $total = 0;

        foreach ($targets as $label => $date) {
            $vehicles = Vehicle::active()
                ->where(function ($q) use ($date) {
                    $q->whereDate('stnk_due_date', $date->toDateString())
                        ->orWhereDate('tax_due_date', $date->toDateString())
                        ->orWhereDate('tax_5y_due_date', $date->toDateString())
                        ->orWhereDate('kir_due_date', $date->toDateString());
                })
                ->get();

            foreach ($vehicles as $vehicle) {
                $docs = collect($vehicle->expiredDocuments($date->copy()->addDay()))
                    ->map(fn ($d) => "• {$d}")
                    ->implode("\n");

                if ($docs === '') {
                    continue;
                }

                User::whereIn('role', ['owner', 'admin', 'manager'])
                    ->each(function ($user) use ($dispatcher, $vehicle, $docs, $label) {
                        $dispatcher->dispatch('vehicle_document_reminder', $user, [
                            'vehicle_name' => $vehicle->name,
                            'plate_number' => $vehicle->plate_number,
                            'document_list' => $docs,
                            'days_remaining' => $label,
                        ]);
                    });

                $total++;
            }
        }

        $this->info("Reminder dikirim untuk {$total} kendaraan.");

        return Command::SUCCESS;
    }
}

<?php

namespace App\Filament\Widgets;

use App\Models\ServiceSchedule;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class MaintenanceAlertsWidget extends BaseWidget
{
    protected static ?string $heading = 'Peringatan Maintenance';

    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return in_array(auth()->user()?->role, ['owner', 'manager', 'fleet_manager']);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ServiceSchedule::query()
                    ->with(['vehicle'])
                    ->active()
                    ->where(function ($q) {
                        $q->where('next_service_date', '<=', now()->addDays(7))
                            ->orWhereNull('next_service_date');
                    })
                    ->orderBy('next_service_date', 'asc')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('vehicle.name')
                    ->label('Kendaraan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('vehicle.plate_number')
                    ->label('Plat Nomor'),
                Tables\Columns\TextColumn::make('service_type')
                    ->label('Tipe Service')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'oil_change' => 'Ganti Oli',
                        'tire_rotation' => 'Rotasi Ban',
                        'brake_service' => 'Servis Rem',
                        'general' => 'Umum',
                        default => ucfirst($state),
                    }),
                Tables\Columns\TextColumn::make('last_service_date')
                    ->label('Terakhir Servis')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('next_service_date')
                    ->label('Servis Berikutnya')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('urgency')
                    ->label('Urgensi')
                    ->state(fn (ServiceSchedule $record): string => match (true) {
                        ! $record->next_service_date => 'overdue',
                        $record->next_service_date->lte(now()) => 'overdue',
                        $record->next_service_date->lte(now()->addDays(3)) => 'critical',
                        $record->next_service_date->lte(now()->addDays(7)) => 'warning',
                        default => 'normal',
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'overdue' => 'danger',
                        'critical' => 'danger',
                        'warning' => 'warning',
                        default => 'success',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'overdue' => 'Terlambat',
                        'critical' => 'Kritis',
                        'warning' => 'Mendekati',
                        default => 'Normal',
                    }),
            ])
            ->poll('60s');
    }
}

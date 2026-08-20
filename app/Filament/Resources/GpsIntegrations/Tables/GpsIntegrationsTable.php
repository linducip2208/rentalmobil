<?php

namespace App\Filament\Resources\GpsIntegrations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GpsIntegrationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('provider.name')->label('Provider')->searchable()->sortable(),
                TextColumn::make('adapter_format')->label('Format')->badge(),
                TextColumn::make('auth_type')->label('Auth')->badge()->color('gray'),
                TextColumn::make('trackers_count')->label('Tracker')->counts('trackers'),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
                TextColumn::make('health_status')->label('Health')->badge()->color(fn ($state) => match ($state) {'healthy' => 'success', 'degraded' => 'warning', 'down' => 'danger', default => 'gray'}),
                TextColumn::make('failure_count')->label('Gagal')->numeric()->sortable(),
                TextColumn::make('last_success_at')->label('Sinkron berhasil')->since()->placeholder('Belum pernah'),
                TextColumn::make('last_error')->label('Error')->limit(45)->color('danger')->toggleable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('test')->label('Tes koneksi')->icon('heroicon-o-signal')->action(function ($record) {
                    try {
                        $result = app(\App\Services\Gps\GpsAdapterManager::class)->for($record)->test($record);
                        Notification::make()->title($result['message'])->color($result['ok'] ? 'success' : 'danger')->send();
                    } catch (\Throwable $e) { Notification::make()->title('Koneksi gagal')->body($e->getMessage())->danger()->send(); }
                })->visible(fn ($record) => in_array($record->adapter_format, ['rest_polling','traccar_compatible'], true)),
                Action::make('sync')->label('Sinkronkan')->icon('heroicon-o-arrow-path')->action(function ($record) {
                    try { $r = app(\App\Services\Gps\GpsSyncService::class)->sync($record); Notification::make()->title("{$r['saved']} posisi disimpan")->success()->send(); }
                    catch (\Throwable $e) { Notification::make()->title('Sinkronisasi gagal')->body($e->getMessage())->danger()->send(); }
                })->visible(fn ($record) => in_array($record->adapter_format, ['rest_polling','traccar_compatible'], true)),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

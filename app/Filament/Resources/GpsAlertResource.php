<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnterpriseResource as Resource;
use App\Filament\Resources\GpsAlertResource\Pages;
use App\Models\GpsAlert;
use Filament\Actions;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class GpsAlertResource extends Resource
{
    protected static ?string $model = GpsAlert::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-bell-alert';

    protected static \UnitEnum|string|null $navigationGroup = 'GPS & Monitoring';

    protected static ?string $navigationLabel = 'Peringatan GPS';

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\Section::make('Penanganan Alert')->schema([
                Forms\Components\TextInput::make('title')->disabled(),
                Forms\Components\Textarea::make('message')->disabled(),
                Forms\Components\DateTimePicker::make('acknowledged_at')->label('Diakui pada')->disabled(),
                Forms\Components\Textarea::make('acknowledgement_note')->label('Catatan penanganan'),
                Forms\Components\DateTimePicker::make('resolved_at')->label('Selesai pada'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('occurred_at')->label('Waktu')->dateTime('d M Y H:i')->sortable(),
            Tables\Columns\TextColumn::make('vehicle.plate_number')->label('Kendaraan')->searchable(),
            Tables\Columns\TextColumn::make('type')->label('Jenis')->badge(),
            Tables\Columns\TextColumn::make('severity')->label('Prioritas')->badge()->color(fn ($state) => match ($state) {
                'critical' => 'danger', 'warning' => 'warning', default => 'info'
            }),
            Tables\Columns\TextColumn::make('message')->label('Detail')->limit(60)->wrap(),
            Tables\Columns\IconColumn::make('acknowledged_at')->label('Diakui')->getStateUsing(fn ($record) => filled($record->acknowledged_at))->boolean(),
        ])->defaultSort('occurred_at', 'desc')->recordActions([
            Actions\Action::make('ack')->label('Akui')->icon('heroicon-o-check-circle')->color('success')
                ->schema([Forms\Components\Textarea::make('note')->label('Catatan tindakan')->required()])
                ->action(fn (GpsAlert $record, array $data) => $record->update(['acknowledged_at' => now(), 'acknowledged_by' => auth()->id(), 'acknowledgement_note' => $data['note']]))
                ->visible(fn (GpsAlert $record) => ! $record->acknowledged_at),
            Actions\EditAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListGpsAlerts::route('/'), 'edit' => Pages\EditGpsAlert::route('/{record}/edit')];
    }
}

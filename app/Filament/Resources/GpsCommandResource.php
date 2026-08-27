<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnterpriseResource as Resource;
use App\Filament\Resources\GpsCommandResource\Pages;
use App\Jobs\SendGpsCommand;
use App\Models\GpsCommand;
use App\Services\Gps\GpsCommandService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class GpsCommandResource extends Resource
{
    protected static ?string $model = GpsCommand::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-command-line';

    protected static \UnitEnum|string|null $navigationGroup = 'GPS & Monitoring';

    protected static ?string $navigationLabel = 'Perintah GPS';

    protected static ?int $navigationSort = 7;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\Select::make('gps_tracker_id')->label('Perangkat')->relationship('tracker', 'device_name')->searchable()->preload()->required(),
            Forms\Components\TextInput::make('command_name')->label('Nama perintah sesuai API provider')->required(),
            Forms\Components\KeyValue::make('parameters')->label('Parameter API'),
            Forms\Components\Textarea::make('reason')->label('Alasan bisnis')->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('created_at')->label('Diajukan')->dateTime('d M H:i')->sortable(),
            Tables\Columns\TextColumn::make('tracker.device_name')->label('Perangkat')->searchable(),
            Tables\Columns\TextColumn::make('command_name')->label('Perintah')->badge(),
            Tables\Columns\TextColumn::make('requestedBy.name')->label('Pemohon'),
            Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                'sent' => 'success', 'failed', 'rejected' => 'danger', 'approved', 'queued' => 'info', default => 'warning'
            }),
        ])->recordActions([
            Actions\Action::make('approve')->label('Setujui')->icon('heroicon-o-check')->color('success')->requiresConfirmation()
                ->action(function (GpsCommand $record) {
                    app(GpsCommandService::class)->approve($record, auth()->user());
                    SendGpsCommand::dispatch($record->id);
                    Notification::make()->title('Disetujui dan masuk antrean')->success()->send();
                })
                ->visible(fn (GpsCommand $record) => $record->status === 'pending_approval' && $record->requested_by !== auth()->id()),
            Actions\Action::make('reject')->label('Tolak')->icon('heroicon-o-x-mark')->color('danger')
                ->schema([Forms\Components\Textarea::make('note')->label('Alasan penolakan')->required()])
                ->action(fn (GpsCommand $record, array $data) => app(GpsCommandService::class)->reject($record, auth()->user(), $data['note']))
                ->visible(fn (GpsCommand $record) => $record->status === 'pending_approval'),
        ])->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListGpsCommands::route('/'), 'create' => Pages\CreateGpsCommand::route('/create')];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaintenanceLogResource\Pages;
use App\Models\MaintenanceLog;
use Filament\Forms;
use Filament\Schemas;
use Filament\Schemas\Schema;
use App\Filament\Resources\EnterpriseResource as Resource;
use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;
use UnitEnum;
use BackedEnum;

class MaintenanceLogResource extends Resource
{
    protected static ?string $model = MaintenanceLog::class;

    protected static BackedEnum | string | null $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static string | UnitEnum | null $navigationGroup = 'Maintenance';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Log Perawatan';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Schemas\Components\Section::make('Informasi Perawatan')->schema([
                Forms\Components\Select::make('vehicle_id')
                    ->label('Kendaraan')
                    ->relationship('vehicle', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('type')
                    ->label('Jenis Perawatan')
                    ->options([
                        'scheduled' => 'Terjadwal',
                        'unscheduled' => 'Tidak Terjadwal',
                        'repair' => 'Perbaikan',
                        'inspection' => 'Inspeksi',
                        'oil_change' => 'Ganti Oli',
                        'tire_replacement' => 'Ganti Ban',
                        'brake_service' => 'Servis Rem',
                        'engine_service' => 'Servis Mesin',
                        'ac_service' => 'Servis AC',
                        'body_work' => 'Body Repair',
                        'other' => 'Lainnya',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('title')
                    ->label('Judul')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(3),
                Forms\Components\TextInput::make('cost')
                    ->label('Biaya (Rp)')
                    ->numeric()
                    ->prefix('Rp'),
                Forms\Components\DateTimePicker::make('performed_at')
                    ->label('Tanggal Dikerjakan')
                    ->required(),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'scheduled' => 'Dijadwalkan',
                        'in_progress' => 'Dikerjakan',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ])
                    ->default('scheduled')
                    ->required(),
            ])->columns(2),

            Schemas\Components\Section::make('Detail Servis')->schema([
                Forms\Components\TextInput::make('km_at_service')
                    ->label('KM Saat Servis')
                    ->numeric(),
                Forms\Components\TextInput::make('next_service_km')
                    ->label('Servis Berikutnya (KM)')
                    ->numeric(),
                Forms\Components\DatePicker::make('next_service_date')
                    ->label('Servis Berikutnya (Tanggal)'),
                Forms\Components\TextInput::make('workshop_name')
                    ->label('Nama Bengkel')
                    ->maxLength(255),
                Forms\Components\TextInput::make('workshop_phone')
                    ->label('Telp Bengkel')
                    ->maxLength(20),
                Forms\Components\FileUpload::make('receipt_path')
                    ->label('Bukti/Faktur')
                    ->disk('public')
                    ->directory('maintenance')
                    ->maxSize(5120),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('vehicle.name')
                    ->label('Kendaraan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'scheduled' => 'info',
                        'repair' => 'danger',
                        'inspection' => 'warning',
                        'oil_change' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('cost')
                    ->label('Biaya')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('performed_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'scheduled' => 'info',
                        'in_progress' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'scheduled' => 'Dijadwalkan',
                        'in_progress' => 'Dikerjakan',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                        default => ucfirst($state),
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'scheduled' => 'Dijadwalkan',
                        'in_progress' => 'Dikerjakan',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ]),
                Tables\Filters\SelectFilter::make('type')
                    ->label('Jenis')
                    ->options([
                        'scheduled' => 'Terjadwal',
                        'repair' => 'Perbaikan',
                        'inspection' => 'Inspeksi',
                        'oil_change' => 'Ganti Oli',
                    ]),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMaintenanceLogs::route('/'),
            'create' => Pages\CreateMaintenanceLog::route('/create'),
            'edit' => Pages\EditMaintenanceLog::route('/{record}/edit'),
            'view' => Pages\ViewMaintenanceLog::route('/{record}'),
        ];
    }
}

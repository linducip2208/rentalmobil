<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReturnRecordResource\Pages;
use App\Models\ReturnRecord;
use Filament\Forms;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;
use UnitEnum;
use BackedEnum;
use App\Services\ReturnProcessingService;

class ReturnRecordResource extends Resource
{
    protected static ?string $model = ReturnRecord::class;

    protected static BackedEnum | string | null $navigationIcon = 'heroicon-o-arrow-uturn-left';

    protected static string | UnitEnum | null $navigationGroup = '📅 Reservasi & Rental';

    protected static ?int $navigationSort = 9;

    protected static ?string $navigationLabel = 'Pengembalian';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Schemas\Components\Section::make('Informasi Pengembalian')->schema([
                Forms\Components\Select::make('rental_order_id')
                    ->label('Order Sewa')
                    ->relationship('rentalOrder', 'order_number')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\DatePicker::make('actual_return_date')
                    ->label('Tanggal Kembali')
                    ->required(),
                Forms\Components\TimePicker::make('actual_return_time')->label('Jam Kembali'),
                Forms\Components\TextInput::make('return_km')
                    ->label('KM Saat Kembali')
                    ->numeric(),
                Forms\Components\TextInput::make('return_fuel_level')
                    ->label('Level Bensin (%)')->numeric()->minValue(0)->maxValue(100),
            ])->columns(2),

            Schemas\Components\Section::make('Kondisi Kendaraan')->schema([
                Forms\Components\Select::make('body_condition')
                    ->label('Kondisi Body')
                    ->options([
                        'excellent' => 'Sangat Baik',
                        'good' => 'Baik',
                        'fair' => 'Cukup',
                        'poor' => 'Buruk',
                    ]),
                Forms\Components\Select::make('interior_condition')
                    ->label('Kondisi Interior')
                    ->options([
                        'excellent' => 'Sangat Baik',
                        'good' => 'Baik',
                        'fair' => 'Cukup',
                        'poor' => 'Buruk',
                    ]),
                Forms\Components\Select::make('tire_condition')
                    ->label('Kondisi Ban')
                    ->options([
                        'excellent' => 'Sangat Baik',
                        'good' => 'Baik',
                        'fair' => 'Cukup',
                        'poor' => 'Buruk',
                    ]),
                Forms\Components\Textarea::make('notes')
                    ->label('Catatan Kondisi')
                    ->rows(2),
                Forms\Components\Toggle::make('has_damage')
                    ->label('Ada Kerusakan')
                    ->default(false),
                Forms\Components\Textarea::make('damage_description')
                    ->label('Deskripsi Kerusakan')
                    ->rows(2),
            ])->columns(2),

            Schemas\Components\Section::make('Biaya Tambahan')->schema([
                Forms\Components\TextInput::make('other_charges')
                    ->label('Biaya Tambahan (Rp)')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0),
                Forms\Components\TextInput::make('late_minutes')
                    ->label('Keterlambatan (Menit)')
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('late_charge')
                    ->label('Denda Keterlambatan (Rp)')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending_review' => 'Menunggu Review',
                        'approved' => 'Disetujui',
                        'disputed' => 'Disengketakan',
                    ])
                    ->default('pending_review')->disabled()->dehydrated(false),
                Forms\Components\Textarea::make('rejection_reason')
                    ->label('Alasan Penolakan')
                    ->rows(2),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('rentalOrder.order_number')
                    ->label('No. Order')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('rentalOrder.vehicle.name')
                    ->label('Kendaraan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('actual_return_date')
                    ->label('Tanggal Kembali')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                Tables\Columns\IconColumn::make('has_damage')
                    ->label('Kerusakan')
                    ->boolean(),
                Tables\Columns\TextColumn::make('other_charges')
                    ->label('Biaya Tambahan')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('late_charge')
                    ->label('Denda')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending_review' => 'warning',
                        'approved' => 'success',
                        'disputed' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending_review' => 'Menunggu Review',
                        'approved' => 'Disetujui',
                        'disputed' => 'Disengketakan',
                        default => ucfirst($state),
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending_review' => 'Menunggu Review',
                        'approved' => 'Disetujui',
                        'disputed' => 'Disengketakan',
                    ]),
            ])
            ->actions([
                Actions\Action::make('approve')->label('Setujui')->icon('heroicon-o-check-badge')->color('success')->requiresConfirmation()
                    ->action(fn (ReturnRecord $record) => app(ReturnProcessingService::class)->approveReturn($record, auth()->id()))
                    ->visible(fn (ReturnRecord $record): bool => $record->status === 'pending_review'),
                Actions\Action::make('dispute')->label('Sengketakan')->icon('heroicon-o-exclamation-triangle')->color('danger')
                    ->modalForm([Forms\Components\Textarea::make('reason')->label('Alasan')->required()])
                    ->action(fn (ReturnRecord $record, array $data) => app(ReturnProcessingService::class)->disputeReturn($record, $data['reason']))
                    ->visible(fn (ReturnRecord $record): bool => $record->status === 'pending_review'),
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
            'index' => Pages\ListReturnRecords::route('/'),
            'create' => Pages\CreateReturnRecord::route('/create'),
            'edit' => Pages\EditReturnRecord::route('/{record}/edit'),
            'view' => Pages\ViewReturnRecord::route('/{record}'),
        ];
    }
}

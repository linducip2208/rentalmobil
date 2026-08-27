<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HandoverRecordResource\Pages;
use App\Models\HandoverRecord;
use Filament\Forms;
use Filament\Schemas;
use Filament\Schemas\Schema;
use App\Filament\Resources\EnterpriseResource as Resource;
use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;
use UnitEnum;
use BackedEnum;

class HandoverRecordResource extends Resource
{
    protected static ?string $model = HandoverRecord::class;

    protected static BackedEnum | string | null $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static string | UnitEnum | null $navigationGroup = 'Rental';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Serah Terima';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Schemas\Components\Section::make('Informasi Serah Terima')->schema([
                Forms\Components\Select::make('rental_order_id')
                    ->label('Rental Order')
                    ->relationship('rentalOrder', 'order_number')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('vehicle_id')
                    ->label('Kendaraan')
                    ->relationship('vehicle', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('customer_id')
                    ->label('Customer')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('staff_id')
                    ->label('Staff')
                    ->relationship('staff', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('type')
                    ->label('Tipe')
                    ->options([
                        'outbound' => 'Outbound (Serah)',
                        'inbound' => 'Inbound (Terima)',
                    ])
                    ->required(),
                Forms\Components\DateTimePicker::make('recorded_at')
                    ->label('Waktu Pencatatan')
                    ->required(),
            ])->columns(2),

            Schemas\Components\Section::make('Kondisi Kendaraan')->schema([
                Forms\Components\TextInput::make('fuel_level')
                    ->label('Level Bahan Bakar (%)')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100),
                Forms\Components\Textarea::make('body_condition')
                    ->label('Kondisi Body')
                    ->rows(2),
                Forms\Components\Textarea::make('interior_condition')
                    ->label('Kondisi Interior')
                    ->rows(2),
                Forms\Components\KeyValue::make('odometer_readings')->label('Odometer')->keyLabel('Tahap')->valueLabel('KM'),
                Forms\Components\CheckboxList::make('accessories')->label('Aksesoris')->options(['stnk' => 'STNK', 'kunci_cadangan' => 'Kunci cadangan', 'dongkrak' => 'Dongkrak', 'ban_serep' => 'Ban serep', 'segitiga' => 'Segitiga pengaman']),
                Forms\Components\CheckboxList::make('checklist')->label('Checklist kondisi')->options(['body' => 'Body terdokumentasi', 'interior' => 'Interior terdokumentasi', 'ban' => 'Ban diperiksa', 'lampu' => 'Lampu diperiksa', 'ac' => 'AC diperiksa', 'dokumen' => 'Dokumen lengkap']),
                Forms\Components\FileUpload::make('photos')->label('Foto serah-terima')->image()->multiple()->directory('handovers')->minFiles(4)->maxFiles(12)->helperText('Minimal 4 foto: depan, belakang, sisi kiri, sisi kanan.'),
            ])->columns(2),

            Schemas\Components\Section::make('Tanda Tangan & Catatan')->schema([
                Forms\Components\TextInput::make('customer_signature_url')
                    ->label('TTD Customer')
                    ->maxLength(500),
                Forms\Components\TextInput::make('staff_signature_url')
                    ->label('TTD Staff')
                    ->maxLength(500),
                Forms\Components\Textarea::make('notes')
                    ->label('Catatan')
                    ->rows(2),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('rentalOrder.order_number')
                    ->label('Order')
                    ->searchable(),
                Tables\Columns\TextColumn::make('vehicle.name')
                    ->label('Kendaraan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'outbound' => 'info',
                        'inbound' => 'success',
                    }),
                Tables\Columns\TextColumn::make('fuel_level')
                    ->label('BBM (%)')
                    ->sortable(),
                Tables\Columns\TextColumn::make('staff.name')
                    ->label('Staff'),
                Tables\Columns\TextColumn::make('recorded_at')
                    ->label('Waktu')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipe')
                    ->options([
                        'outbound' => 'Outbound',
                        'inbound' => 'Inbound',
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
            'index' => Pages\ListHandoverRecords::route('/'),
            'create' => Pages\CreateHandoverRecord::route('/create'),
            'edit' => Pages\EditHandoverRecord::route('/{record}/edit'),
            'view' => Pages\ViewHandoverRecord::route('/{record}'),
        ];
    }
}

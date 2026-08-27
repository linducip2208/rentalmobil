<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TripPermitResource\Pages;
use App\Models\TripPermit;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions;
use Filament\Forms;
use App\Filament\Resources\EnterpriseResource as Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class TripPermitResource extends Resource
{
    protected static ?string $model = TripPermit::class;
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-map';
    protected static \UnitEnum|string|null $navigationGroup = 'Rental';
    protected static ?string $navigationLabel = 'SPJ Digital';
    protected static ?int $navigationSort = 7;

    public static function form(Schema $s): Schema
    {
        return $s->components([
            Forms\Components\Select::make('rental_order_id')
                ->relationship('rentalOrder', 'order_number', fn ($q) => $q->where('rental_type', 'with_driver'))
                ->label('Order sewa (dengan supir)')
                ->required()
                ->searchable()
                ->helperText('Hanya order bertipe "dengan supir" yang memerlukan SPJ.'),
            Forms\Components\Select::make('driver_id')->relationship('driver', 'name')->required()->searchable()->preload(),
            Forms\Components\TextInput::make('route_from')->label('Rute awal')->required()->maxLength(120)->default(fn ($record) => null)->placeholder('Jakarta'),
            Forms\Components\TextInput::make('route_to')->label('Rute tujuan')->required()->maxLength(120)->placeholder('Bandung'),
            Forms\Components\Select::make('fuel_start_level')->options(TripPermit::fuelLevels())->required()->default('full')->label('BBM saat berangkat'),
            Forms\Components\Select::make('fuel_end_level')->options(TripPermit::fuelLevels())->label('BBM saat kembali'),
            Forms\Components\TextInput::make('odometer_start')->numeric()->minValue(0)->label('KM awal'),
            Forms\Components\TextInput::make('odometer_end')->numeric()->minValue(0)->label('KM akhir'),
            Forms\Components\TextInput::make('toll_cost')->numeric()->prefix('Rp')->default(0)->label('Biaya tol'),
            Forms\Components\TextInput::make('parking_cost')->numeric()->prefix('Rp')->default(0)->label('Parkir'),
            Forms\Components\TextInput::make('accommodation_cost')->numeric()->prefix('Rp')->default(0)->label('Akomodasi / uang makan luar kota'),
            Forms\Components\Textarea::make('notes')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $t): Table
    {
        return $t->columns([
            Tables\Columns\TextColumn::make('spj_number')->searchable()->weight('bold')->fontFamily('mono'),
            Tables\Columns\TextColumn::make('rentalOrder.order_number')->label('Order'),
            Tables\Columns\TextColumn::make('driver.name')->label('Supir'),
            Tables\Columns\TextColumn::make('route_from')->label('Dari'),
            Tables\Columns\TextColumn::make('route_to')->label('Ke'),
            Tables\Columns\TextColumn::make('totalOperationalCost')->money('IDR')->label('Biaya Ops.'),
            Tables\Columns\TextColumn::make('status')->badge()->formatStateUsing(fn ($s) => $s === 'open' ? 'Berjalan' : 'Selesai')->color(fn ($s) => $s === 'open' ? 'warning' : 'success'),
            Tables\Columns\TextColumn::make('started_at')->dateTime('d/m H:i')->sortable(),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('status')->options(['open' => 'Berjalan', 'closed' => 'Selesai']),
        ])
        ->recordActions([
            Actions\Action::make('printPdf')
                ->label('Cetak PDF')
                ->icon('heroicon-o-printer')
                ->color('info')
                ->action(function (TripPermit $record) {
                    $pdf = Pdf::loadView('pdf.spj', ['permit' => $record->load(['rentalOrder.customer', 'driver'])]);

                    return response()->streamDownload(fn () => print ($pdf->output()), "{$record->spj_number}.pdf");
                }),
            Actions\Action::make('closeTrip')
                ->label('Tutup Perjalanan')
                ->icon('heroicon-o-flag')
                ->color('danger')
                ->visible(fn (TripPermit $r) => $r->status === 'open')
                ->form([
                    Forms\Components\Select::make('fuel_end_level')->options(TripPermit::fuelLevels())->required()->label('BBM saat kembali'),
                    Forms\Components\TextInput::make('odometer_end')->numeric()->minValue(0)->label('KM akhir'),
                    Forms\Components\TextInput::make('toll_cost')->numeric()->prefix('Rp')->label('Total tol aktual'),
                    Forms\Components\TextInput::make('parking_cost')->numeric()->prefix('Rp')->label('Total parkir aktual'),
                ])
                ->action(function (TripPermit $r, array $data) {
                    $r->update(array_merge($data, ['status' => 'closed', 'finished_at' => now()]));
                }),
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTripPermits::route('/'),
            'create' => Pages\CreateTripPermit::route('/create'),
            'edit' => Pages\EditTripPermit::route('/{record}/edit'),
        ];
    }
}

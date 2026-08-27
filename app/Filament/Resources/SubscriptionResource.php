<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriptionResource\Pages;
use App\Models\Subscription;
use App\Services\SubscriptionBillingService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use App\Filament\Resources\EnterpriseResource as Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-arrow-path-rounded-square';
    protected static \UnitEnum|string|null $navigationGroup = 'Rental';
    protected static ?string $navigationLabel = 'Langganan Bulanan';
    protected static ?int $navigationSort = 30;

    public static function form(Schema $s): Schema
    {
        return $s->components([
            Forms\Components\Select::make('customer_id')->relationship('customer', 'name')->required()->searchable()->preload(),
            Forms\Components\Select::make('vehicle_id')->relationship('vehicle', 'name')->required()->searchable()->preload(),
            Forms\Components\TextInput::make('plan_name')->required()->maxLength(100)->default('Langganan Bulanan'),
            Forms\Components\Select::make('billing_cycle')->options([
                'monthly' => 'Bulanan',
                'quarterly' => 'Per 3 Bulan',
                'yearly' => 'Tahunan',
            ])->required()->default('monthly'),
            Forms\Components\TextInput::make('price_per_cycle')->numeric()->prefix('Rp')->required(),
            Forms\Components\DatePicker::make('start_date')->required()->default(now()),
            Forms\Components\Toggle::make('auto_renew')->label('Perpanjang otomatis')->default(true),
            Forms\Components\TextInput::make('included_km_per_cycle')->numeric()->label('Kuota KM / periode')->nullable(),
            Forms\Components\TextInput::make('overage_km_rate')->numeric()->prefix('Rp')->label('Tarif per KM kelebihan')->nullable(),
            Forms\Components\Textarea::make('notes')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $t): Table
    {
        return $t->columns([
            Tables\Columns\TextColumn::make('customer.name')->searchable(),
            Tables\Columns\TextColumn::make('vehicle.name'),
            Tables\Columns\TextColumn::make('plan_name'),
            Tables\Columns\TextColumn::make('billing_cycle')->badge()->formatStateUsing(fn (string $state) => ['monthly' => 'Bulanan', 'quarterly' => '3 Bulanan', 'yearly' => 'Tahunan'][$state] ?? $state),
            Tables\Columns\TextColumn::make('price_per_cycle')->money('IDR')->label('Harga'),
            Tables\Columns\TextColumn::make('current_period_end')->date()->label('Periode s/d')->placeholder('Belum ditagih'),
            Tables\Columns\TextColumn::make('status')->badge()->color(fn (string $state) => match ($state) {
                'active' => 'success',
                'paused' => 'warning',
                'cancelled' => 'danger',
                default => 'gray',
            })->formatStateUsing(fn (string $state) => ['active' => 'Aktif', 'paused' => 'Jeda', 'cancelled' => 'Batal', 'expired' => 'Berakhir'][$state] ?? $state),
        ])
        ->recordActions([
            Tables\Actions\Action::make('billNow')
                ->label('Tagih sekarang')
                ->icon('heroicon-o-banknotes')
                ->color('info')
                ->visible(fn (Subscription $r) => $r->status === 'active')
                ->requiresConfirmation()
                ->modalDescription('Terbitkan invoice untuk periode berikutnya dan majukan tanggal periode?')
                ->action(function (Subscription $r) {
                    app(SubscriptionBillingService::class)->generateInvoice($r);
                    Notification::make()->title('Invoice langganan diterbitkan')->success()->send();
                }),
            Tables\Actions\Action::make('cancelSub')
                ->label('Batalkan')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn (Subscription $r) => in_array($r->status, ['active', 'paused']))
                ->form([Forms\Components\Textarea::make('reason')->label('Alasan')->required()])
                ->action(function (Subscription $r, array $data) {
                    app(SubscriptionBillingService::class)->cancel($r, $data['reason']);
                    Notification::make()->title('Langganan dibatalkan')->warning()->send();
                }),
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('status')->options(['active' => 'Aktif', 'paused' => 'Jeda', 'cancelled' => 'Batal', 'expired' => 'Berakhir']),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubscriptions::route('/'),
            'create' => Pages\CreateSubscription::route('/create'),
            'edit' => Pages\EditSubscription::route('/{record}/edit'),
        ];
    }
}

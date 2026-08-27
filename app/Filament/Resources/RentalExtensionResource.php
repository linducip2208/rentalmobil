<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnterpriseResource as Resource;
use App\Filament\Resources\RentalExtensionResource\Pages;
use App\Models\RentalExtension;
use App\Services\RentalExtensionService;
use Filament\Actions;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class RentalExtensionResource extends Resource
{
    protected static ?string $model = RentalExtension::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-arrow-path-rounded-square';

    protected static \UnitEnum|string|null $navigationGroup = 'Rental';

    protected static ?string $navigationLabel = 'Perpanjangan';

    protected static ?int $navigationSort = 12;

    public static function form(Schema $s): Schema
    {
        return $s->components([]);
    }

    public static function table(Table $t): Table
    {
        return $t->columns([Tables\Columns\TextColumn::make('rentalOrder.order_number')->label('Order'), Tables\Columns\TextColumn::make('customer.name'), Tables\Columns\TextColumn::make('requested_end_date')->date(), Tables\Columns\TextColumn::make('additional_amount')->money('IDR'), Tables\Columns\TextColumn::make('status')->badge()])->recordActions([Actions\Action::make('approve')->label('Setujui')->color('success')->requiresConfirmation()->action(fn ($r) => app(RentalExtensionService::class)->approve($r, auth()->id()))->visible(fn ($r) => $r->status === 'pending'), Actions\Action::make('reject')->label('Tolak')->color('danger')->modalForm([Forms\Components\Textarea::make('reason')->required()])->action(fn ($r, $d) => app(RentalExtensionService::class)->reject($r, auth()->id(), $d['reason']))->visible(fn ($r) => $r->status === 'pending')]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListRentalExtensions::route('/')];
    }
}

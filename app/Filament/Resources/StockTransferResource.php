<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockTransferResource\Pages;
use App\Models\StockTransfer;
use App\Services\StockTransferService;
use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class StockTransferResource extends EnterpriseResource
{
    protected static ?string $model = StockTransfer::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-truck';

    protected static string|UnitEnum|null $navigationGroup = 'Procurement & Inventory';

    protected static ?string $navigationLabel = 'Transfer Stok';

    protected static ?int $navigationSort = 8;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([Forms\Components\TextInput::make('transfer_number')->disabled()->dehydrated(false), Forms\Components\Select::make('source_warehouse_id')->label('Gudang Asal')->relationship('sourceWarehouse', 'name')->searchable()->required(), Forms\Components\Select::make('destination_warehouse_id')->label('Gudang Tujuan')->relationship('destinationWarehouse', 'name')->searchable()->required()->different('source_warehouse_id'), Forms\Components\DatePicker::make('transfer_date')->default(now())->required(), Forms\Components\Textarea::make('notes')->columnSpanFull(), Forms\Components\Repeater::make('items')->relationship()->schema([Forms\Components\Select::make('spare_part_id')->relationship('sparePart', 'name')->searchable()->required(), Forms\Components\TextInput::make('quantity')->numeric()->minValue(.001)->required()])->columns(2)->columnSpanFull()->minItems(1)])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([Tables\Columns\TextColumn::make('transfer_number')->searchable(), Tables\Columns\TextColumn::make('sourceWarehouse.name')->label('Asal'), Tables\Columns\TextColumn::make('destinationWarehouse.name')->label('Tujuan'), Tables\Columns\TextColumn::make('transfer_date')->date('d/m/Y'), Tables\Columns\TextColumn::make('status')->badge()])->actions([Actions\Action::make('submit')->visible(fn ($r) => $r->status === 'draft' && static::allows('submit'))->action(fn ($r) => app(StockTransferService::class)->submit($r)), Actions\Action::make('approve')->visible(fn ($r) => $r->status === 'submitted' && static::allows('approve'))->action(fn ($r) => app(StockTransferService::class)->approve($r)), Actions\Action::make('ship')->label('Kirim')->visible(fn ($r) => $r->status === 'approved' && static::allows('update'))->requiresConfirmation()->action(fn ($r) => app(StockTransferService::class)->ship($r)), Actions\Action::make('receive')->label('Terima')->color('success')->visible(fn ($r) => $r->status === 'in_transit' && static::allows('update'))->requiresConfirmation()->action(fn ($r) => app(StockTransferService::class)->receive($r)), Actions\EditAction::make()->visible(fn ($r) => $r->status === 'draft')]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListStockTransfers::route('/'), 'create' => Pages\CreateStockTransfer::route('/create'), 'edit' => Pages\EditStockTransfer::route('/{record}/edit')];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GoodsReceiptResource\Pages;
use App\Models\GoodsReceipt;
use App\Services\GoodsReceiptService;
use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class GoodsReceiptResource extends EnterpriseResource
{
    protected static ?string $model = GoodsReceipt::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-inbox-arrow-down';

    protected static string|UnitEnum|null $navigationGroup = 'Procurement & Inventory';

    protected static ?string $navigationLabel = 'Penerimaan Barang';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([Forms\Components\TextInput::make('receipt_number')->disabled()->dehydrated(false), Forms\Components\Select::make('spare_part_purchase_order_id')->label('Purchase Order')->relationship('purchaseOrder', 'po_number')->searchable()->preload()->required(), Forms\Components\Select::make('warehouse_id')->label('Gudang')->relationship('warehouse', 'name')->searchable()->preload()->required(), Forms\Components\TextInput::make('supplier_delivery_note')->label('Surat Jalan'), Forms\Components\Textarea::make('notes')->columnSpanFull(), Forms\Components\Repeater::make('items')->relationship()->schema([Forms\Components\Select::make('spare_part_purchase_order_item_id')->label('Item PO')->relationship('purchaseOrderItem', 'id')->required(), Forms\Components\Select::make('spare_part_id')->relationship('sparePart', 'name')->required()->searchable(), Forms\Components\TextInput::make('accepted_quantity')->label('Diterima')->numeric()->required(), Forms\Components\TextInput::make('rejected_quantity')->label('Ditolak')->numeric()->default(0), Forms\Components\TextInput::make('damaged_quantity')->label('Rusak')->numeric()->default(0), Forms\Components\TextInput::make('unit_cost')->label('Harga')->numeric()->required()])->columns(3)->columnSpanFull()->minItems(1)])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([Tables\Columns\TextColumn::make('receipt_number')->searchable(), Tables\Columns\TextColumn::make('purchaseOrder.po_number')->label('PO'), Tables\Columns\TextColumn::make('warehouse.name')->label('Gudang'), Tables\Columns\TextColumn::make('received_at')->dateTime('d/m/Y H:i'), Tables\Columns\TextColumn::make('receivedBy.name')->label('Penerima'), Tables\Columns\TextColumn::make('status')->badge()])->filters([Tables\Filters\SelectFilter::make('status')->options(['draft' => 'Draft', 'confirmed' => 'Confirmed'])])->actions([Actions\Action::make('confirm')->label('Konfirmasi')->color('success')->visible(fn ($r) => $r->status === 'draft' && static::allows('approve'))->requiresConfirmation()->action(fn ($r) => app(GoodsReceiptService::class)->confirm($r)), Actions\EditAction::make()->visible(fn ($r) => $r->status === 'draft')]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListGoodsReceipts::route('/'), 'create' => Pages\CreateGoodsReceipt::route('/create'), 'edit' => Pages\EditGoodsReceipt::route('/{record}/edit')];
    }
}

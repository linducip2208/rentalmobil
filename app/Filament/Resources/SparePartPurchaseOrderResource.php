<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SparePartPurchaseOrderResource\Pages;
use App\Models\SparePartPurchaseOrder;
use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class SparePartPurchaseOrderResource extends EnterpriseResource
{
    protected static ?string $model = SparePartPurchaseOrder::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-shopping-cart';

    protected static string|UnitEnum|null $navigationGroup = 'Procurement & Inventory';

    protected static ?string $navigationLabel = 'Purchase Order';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([Forms\Components\TextInput::make('po_number')->disabled()->dehydrated(false), Forms\Components\Select::make('supplier_id')->relationship('supplier', 'name')->searchable()->preload()->required(), Forms\Components\Select::make('location_id')->relationship('location', 'name')->searchable()->preload()->required(), Forms\Components\Select::make('warehouse_id')->relationship('warehouse', 'name')->searchable()->preload()->required(), Forms\Components\DatePicker::make('order_date')->default(now())->required(), Forms\Components\DatePicker::make('expected_at')->label('Estimasi Tiba'), Forms\Components\TextInput::make('subtotal')->numeric()->required(), Forms\Components\TextInput::make('tax_amount')->numeric()->default(0), Forms\Components\TextInput::make('discount_amount')->numeric()->default(0), Forms\Components\TextInput::make('total_amount')->numeric()->required(), Forms\Components\Textarea::make('notes')->columnSpanFull(), Forms\Components\Repeater::make('items')->relationship()->schema([Forms\Components\Select::make('spare_part_id')->relationship('sparePart', 'name')->searchable()->required(), Forms\Components\TextInput::make('quantity')->numeric()->required(), Forms\Components\TextInput::make('unit_price')->numeric()->required(), Forms\Components\TextInput::make('line_total')->numeric()->required()])->columns(4)->columnSpanFull()->minItems(1)])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([Tables\Columns\TextColumn::make('po_number')->searchable(), Tables\Columns\TextColumn::make('supplier.name')->label('Supplier'), Tables\Columns\TextColumn::make('warehouse.name')->label('Gudang'), Tables\Columns\TextColumn::make('order_date')->date('d/m/Y'), Tables\Columns\TextColumn::make('expected_at')->date('d/m/Y'), Tables\Columns\TextColumn::make('total_amount')->money('IDR'), Tables\Columns\TextColumn::make('status')->badge()])->filters([Tables\Filters\SelectFilter::make('status')->options(['draft' => 'Draft', 'submitted' => 'Submitted', 'approved' => 'Approved', 'sent' => 'Sent', 'partially_received' => 'Partial Receipt', 'received' => 'Received', 'invoiced' => 'Invoiced', 'closed' => 'Closed', 'cancelled' => 'Cancelled'])])->actions([Actions\Action::make('submit')->visible(fn ($r) => $r->status === 'draft' && static::allows('submit'))->requiresConfirmation()->action(fn ($r) => $r->update(['status' => 'submitted'])), Actions\Action::make('approve')->color('success')->visible(fn ($r) => $r->status === 'submitted' && static::allows('approve'))->requiresConfirmation()->action(fn ($r) => $r->update(['status' => 'approved', 'approved_by' => auth()->id(), 'approved_at' => now()])), Actions\EditAction::make()->visible(fn ($r) => $r->status === 'draft')]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListPurchaseOrders::route('/'), 'create' => Pages\CreatePurchaseOrder::route('/create'), 'edit' => Pages\EditPurchaseOrder::route('/{record}/edit')];
    }
}

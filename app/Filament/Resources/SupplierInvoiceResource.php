<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupplierInvoiceResource\Pages;
use App\Models\SupplierInvoice;
use App\Services\SupplierPayableService;
use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class SupplierInvoiceResource extends EnterpriseResource
{
    protected static ?string $model = SupplierInvoice::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-document-currency-dollar';

    protected static string|UnitEnum|null $navigationGroup = 'Finance';

    protected static ?string $navigationLabel = 'Tagihan Supplier';

    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([Forms\Components\TextInput::make('bill_number')->disabled()->dehydrated(false), Forms\Components\TextInput::make('supplier_invoice_number')->label('Nomor Invoice Supplier'), Forms\Components\Select::make('supplier_id')->relationship('supplier', 'name')->searchable()->preload()->required(), Forms\Components\Select::make('location_id')->relationship('location', 'name')->searchable()->preload()->required(), Forms\Components\Select::make('spare_part_purchase_order_id')->label('Purchase Order')->relationship('purchaseOrder', 'po_number')->searchable(), Forms\Components\Select::make('goods_receipt_id')->label('Goods Receipt')->relationship('goodsReceipt', 'receipt_number')->searchable(), Forms\Components\DatePicker::make('invoice_date')->required()->default(now()), Forms\Components\DatePicker::make('due_date')->required(), Forms\Components\TextInput::make('subtotal')->numeric()->required(), Forms\Components\TextInput::make('tax_amount')->numeric()->default(0), Forms\Components\TextInput::make('discount_amount')->numeric()->default(0), Forms\Components\TextInput::make('total')->numeric()->required(), Forms\Components\Textarea::make('notes')->columnSpanFull(), Forms\Components\Repeater::make('items')->relationship()->schema([Forms\Components\Select::make('spare_part_id')->relationship('sparePart', 'name')->searchable(), Forms\Components\TextInput::make('description')->required(), Forms\Components\TextInput::make('quantity')->numeric()->required(), Forms\Components\TextInput::make('unit_price')->numeric()->required(), Forms\Components\TextInput::make('line_total')->numeric()->required()])->columns(5)->columnSpanFull()])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([Tables\Columns\TextColumn::make('bill_number')->searchable(), Tables\Columns\TextColumn::make('supplier.name')->searchable(), Tables\Columns\TextColumn::make('invoice_date')->date('d/m/Y'), Tables\Columns\TextColumn::make('due_date')->date('d/m/Y')->color(fn ($r) => $r->days_overdue > 0 ? 'danger' : null), Tables\Columns\TextColumn::make('total')->money('IDR'), Tables\Columns\TextColumn::make('paid_amount')->money('IDR'), Tables\Columns\TextColumn::make('outstanding_amount')->money('IDR'), Tables\Columns\TextColumn::make('status')->badge()])->filters([Tables\Filters\SelectFilter::make('status')->options(['draft' => 'Draft', 'posted' => 'Posted', 'partial' => 'Partial', 'paid' => 'Paid', 'overdue' => 'Overdue', 'cancelled' => 'Cancelled']), Tables\Filters\SelectFilter::make('supplier_id')->relationship('supplier', 'name')])->actions([Actions\Action::make('post')->label('Posting')->visible(fn ($r) => $r->status === 'draft' && static::allows('post'))->requiresConfirmation()->action(fn ($r) => app(SupplierPayableService::class)->post($r)), Actions\Action::make('pay')->label('Bayar')->visible(fn ($r) => in_array($r->status, ['posted', 'partial', 'overdue']) && static::allows('post'))->form([Forms\Components\TextInput::make('amount')->numeric()->required()->maxValue(fn ($r) => $r->outstanding_amount), Forms\Components\TextInput::make('reference')])->action(fn ($r, $data) => app(SupplierPayableService::class)->pay($r, (float) $data['amount'], null, $data['reference'] ?? null)), Actions\EditAction::make()->visible(fn ($r) => $r->status === 'draft')]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListSupplierInvoices::route('/'), 'create' => Pages\CreateSupplierInvoice::route('/create'), 'edit' => Pages\EditSupplierInvoice::route('/{record}/edit')];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseRequisitionResource\Pages;
use App\Models\PurchaseRequisition;
use App\Services\ProcurementService;
use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class PurchaseRequisitionResource extends EnterpriseResource
{
    protected static ?string $model = PurchaseRequisition::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string|UnitEnum|null $navigationGroup = 'Procurement & Inventory';

    protected static ?string $navigationLabel = 'Purchase Requisition';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([Forms\Components\TextInput::make('requisition_number')->label('Nomor')->disabled()->dehydrated(false), Forms\Components\Select::make('location_id')->label('Cabang')->relationship('location', 'name')->required()->searchable()->preload(), Forms\Components\Select::make('warehouse_id')->label('Gudang')->relationship('warehouse', 'name')->required()->searchable()->preload(), Forms\Components\Select::make('requested_by')->label('Pemohon')->relationship('requester', 'name')->default(fn () => auth()->id())->required(), Forms\Components\TextInput::make('department')->label('Departemen'), Forms\Components\DatePicker::make('request_date')->label('Tanggal Permintaan')->default(now())->required(), Forms\Components\DatePicker::make('required_date')->label('Dibutuhkan Tanggal'), Forms\Components\Select::make('priority')->options(['low' => 'Rendah', 'normal' => 'Normal', 'high' => 'Tinggi', 'urgent' => 'Mendesak'])->default('normal')->required(), Forms\Components\Textarea::make('notes')->columnSpanFull(), Forms\Components\Repeater::make('items')->relationship()->schema([Forms\Components\Select::make('spare_part_id')->relationship('sparePart', 'name')->searchable()->preload()->required(), Forms\Components\TextInput::make('quantity')->numeric()->minValue(.001)->required(), Forms\Components\TextInput::make('estimated_unit_price')->label('Estimasi Harga')->numeric()->required(), Forms\Components\TextInput::make('estimated_total')->numeric()->required(), Forms\Components\TextInput::make('notes')])->columns(4)->columnSpanFull()->minItems(1)])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([Tables\Columns\TextColumn::make('requisition_number')->label('Nomor')->searchable(), Tables\Columns\TextColumn::make('request_date')->date('d/m/Y')->sortable(), Tables\Columns\TextColumn::make('location.name')->label('Cabang'), Tables\Columns\TextColumn::make('requester.name')->label('Pemohon'), Tables\Columns\TextColumn::make('priority')->badge(), Tables\Columns\TextColumn::make('estimated_total')->money('IDR'), Tables\Columns\TextColumn::make('status')->badge()])->filters([Tables\Filters\SelectFilter::make('status')->options(array_combine(['draft', 'pending_approval', 'approved', 'rejected', 'converted_to_po', 'cancelled'], ['Draft', 'Menunggu Approval', 'Approved', 'Rejected', 'Menjadi PO', 'Dibatalkan'])), Tables\Filters\SelectFilter::make('location_id')->relationship('location', 'name')])->actions([Actions\Action::make('submit')->label('Ajukan')->icon('heroicon-o-paper-airplane')->visible(fn ($r) => $r->status === 'draft' && static::allows('submit'))->requiresConfirmation()->action(fn ($r) => app(ProcurementService::class)->submit($r)), Actions\Action::make('approve')->label('Setujui')->color('success')->visible(fn ($r) => in_array($r->status, ['submitted', 'pending_approval']) && static::allows('approve'))->action(fn ($r) => app(ProcurementService::class)->approve($r)), Actions\Action::make('reject')->label('Tolak')->color('danger')->visible(fn ($r) => in_array($r->status, ['submitted', 'pending_approval']) && static::allows('reject'))->form([Forms\Components\Textarea::make('reason')->required()])->action(fn ($r, $data) => app(ProcurementService::class)->reject($r, $data['reason'])), Actions\EditAction::make()->visible(fn ($r) => $r->status === 'draft')]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListPurchaseRequisitions::route('/'), 'create' => Pages\CreatePurchaseRequisition::route('/create'), 'edit' => Pages\EditPurchaseRequisition::route('/{record}/edit')];
    }
}

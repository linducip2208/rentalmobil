<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupplierResource\Pages;
use App\Models\Supplier;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class SupplierResource extends EnterpriseResource
{
    protected static ?string $model = Supplier::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-building-storefront';

    protected static string|UnitEnum|null $navigationGroup = 'Procurement & Inventory';

    protected static ?string $navigationLabel = 'Supplier';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([Forms\Components\TextInput::make('code')->label('Kode')->required()->unique(ignoreRecord: true), Forms\Components\TextInput::make('name')->label('Perusahaan')->required(), Forms\Components\TextInput::make('contact_person')->label('Kontak Utama'), Forms\Components\TextInput::make('phone')->tel(), Forms\Components\TextInput::make('email')->email(), Forms\Components\TextInput::make('tax_number')->label('NPWP'), Forms\Components\Textarea::make('address')->label('Alamat')->columnSpanFull(), Forms\Components\TextInput::make('payment_terms_days')->label('Termin (hari)')->numeric()->default(30), Forms\Components\TextInput::make('credit_limit')->label('Limit Kredit')->numeric()->prefix('Rp'), Forms\Components\TextInput::make('rating')->numeric()->minValue(0)->maxValue(5), Forms\Components\Toggle::make('is_active')->label('Aktif')->default(true), Forms\Components\Textarea::make('notes')->label('Catatan')->columnSpanFull()])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([Tables\Columns\TextColumn::make('code')->searchable()->sortable(), Tables\Columns\TextColumn::make('name')->label('Supplier')->searchable()->sortable(), Tables\Columns\TextColumn::make('contact_person')->label('Kontak'), Tables\Columns\TextColumn::make('payment_terms_days')->label('Termin')->suffix(' hari'), Tables\Columns\TextColumn::make('credit_limit')->money('IDR'), Tables\Columns\IconColumn::make('is_active')->boolean()])->filters([Tables\Filters\TernaryFilter::make('is_active')->label('Aktif')])->actions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListSuppliers::route('/'), 'create' => Pages\CreateSupplier::route('/create'), 'edit' => Pages\EditSupplier::route('/{record}/edit')];
    }
}

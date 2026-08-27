<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InsurancePolicyResource\Pages;
use App\Models\InsurancePolicy;
use Filament\Forms;
use Filament\Schemas;
use Filament\Schemas\Schema;
use App\Filament\Resources\EnterpriseResource as Resource;
use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;
use UnitEnum;
use BackedEnum;

class InsurancePolicyResource extends Resource
{
    protected static ?string $model = InsurancePolicy::class;

    protected static BackedEnum | string | null $navigationIcon = 'heroicon-o-shield-check';

    protected static string | UnitEnum | null $navigationGroup = 'Fleet';

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationLabel = 'Asuransi Kendaraan';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Schemas\Components\Section::make('Informasi Polis')->schema([
                Forms\Components\Select::make('vehicle_id')
                    ->label('Kendaraan')
                    ->relationship('vehicle', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('policy_number')
                    ->label('No. Polis')
                    ->maxLength(100)
                    ->required()
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('provider_name')
                    ->label('Nama Provider')
                    ->maxLength(255)
                    ->required(),
                Forms\Components\TextInput::make('coverage_type')
                    ->label('Tipe Coverage')
                    ->maxLength(100),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Aktif',
                        'expired' => 'Kadaluarsa',
                        'cancelled' => 'Dibatalkan',
                    ])
                    ->default('active')
                    ->required(),
            ])->columns(2),

            Schemas\Components\Section::make('Premi & Klaim')->schema([
                Forms\Components\TextInput::make('max_claim')
                    ->label('Maks. Klaim')
                    ->numeric()
                    ->prefix('Rp'),
                Forms\Components\TextInput::make('premium')
                    ->label('Premi')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),
                Forms\Components\DatePicker::make('start_date')
                    ->label('Tanggal Mulai')
                    ->required(),
                Forms\Components\DatePicker::make('end_date')
                    ->label('Tanggal Akhir')
                    ->required(),
                Forms\Components\FileUpload::make('document_path')
                    ->label('File Polis')
                    ->disk('public')
                    ->directory('insurance-policies')
                    ->maxSize(5120),
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
                Tables\Columns\TextColumn::make('vehicle.name')
                    ->label('Kendaraan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('policy_number')
                    ->label('No. Polis')
                    ->searchable(),
                Tables\Columns\TextColumn::make('provider_name')
                    ->label('Provider')
                    ->searchable(),
                Tables\Columns\TextColumn::make('coverage_type')
                    ->label('Tipe'),
                Tables\Columns\TextColumn::make('premium')
                    ->label('Premi')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_claim')
                    ->label('Maks. Klaim')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Mulai')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Akhir')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'expired' => 'danger',
                        'cancelled' => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Aktif',
                        'expired' => 'Kadaluarsa',
                        'cancelled' => 'Dibatalkan',
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
            'index' => Pages\ListInsurancePolicies::route('/'),
            'create' => Pages\CreateInsurancePolicy::route('/create'),
            'edit' => Pages\EditInsurancePolicy::route('/{record}/edit'),
            'view' => Pages\ViewInsurancePolicy::route('/{record}'),
        ];
    }
}

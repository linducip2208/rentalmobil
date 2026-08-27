<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlacklistEntryResource\Pages;
use App\Models\BlacklistEntry;
use Filament\Forms;
use Filament\Schemas;
use Filament\Schemas\Schema;
use App\Filament\Resources\EnterpriseResource as Resource;
use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;
use UnitEnum;
use BackedEnum;

class BlacklistEntryResource extends Resource
{
    protected static ?string $model = BlacklistEntry::class;

    protected static BackedEnum | string | null $navigationIcon = 'heroicon-o-no-symbol';

    protected static string | UnitEnum | null $navigationGroup = 'Risk & Security';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Blacklist';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Schemas\Components\Section::make('Informasi Blacklist')->schema([
                Forms\Components\Select::make('customer_id')
                    ->label('Customer')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Textarea::make('reason')
                    ->label('Alasan')
                    ->required()
                    ->rows(3),
                Forms\Components\Select::make('severity')
                    ->label('Tingkat Keparahan')
                    ->options([
                        'low' => 'Rendah',
                        'medium' => 'Sedang',
                        'high' => 'Tinggi',
                        'critical' => 'Kritis',
                    ])
                    ->default('medium')
                    ->required(),
                Forms\Components\FileUpload::make('evidence_path')
                    ->label('Bukti')
                    ->disk('public')
                    ->directory('blacklist')
                    ->maxSize(5120),
                Forms\Components\DateTimePicker::make('expires_at')
                    ->label('Berlaku Sampai')
                    ->placeholder('â€” Permanen â€”'),
                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
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
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.phone')
                    ->label('Telepon')
                    ->searchable(),
                Tables\Columns\TextColumn::make('severity')
                    ->label('Tingkat')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'low' => 'success',
                        'medium' => 'warning',
                        'high' => 'danger',
                        'critical' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'low' => 'Rendah',
                        'medium' => 'Sedang',
                        'high' => 'Tinggi',
                        'critical' => 'Kritis',
                        default => ucfirst($state),
                    }),
                Tables\Columns\TextColumn::make('reason')
                    ->label('Alasan')
                    ->limit(50),
                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Berlaku Sampai')
                    ->dateTime('d M Y H:i'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Aktif'),
                Tables\Filters\SelectFilter::make('severity')
                    ->label('Tingkat')
                    ->options([
                        'low' => 'Rendah',
                        'medium' => 'Sedang',
                        'high' => 'Tinggi',
                        'critical' => 'Kritis',
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
            'index' => Pages\ListBlacklistEntrys::route('/'),
            'create' => Pages\CreateBlacklistEntry::route('/create'),
            'edit' => Pages\EditBlacklistEntry::route('/{record}/edit'),
            'view' => Pages\ViewBlacklistEntry::route('/{record}'),
        ];
    }
}

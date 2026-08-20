<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;
use BackedEnum;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static BackedEnum | string | null $navigationIcon = 'heroicon-o-tag';

    protected static string | UnitEnum | null $navigationGroup = '🚗 Master Data';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Kategori';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Schemas\Components\Section::make('Informasi Kategori')->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Get $set, ?string $state) => $set('slug', Str::slug($state ?? ''))),

                Forms\Components\TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                Forms\Components\Select::make('parent_id')
                    ->label('Kategori Induk')
                    ->relationship('parent', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('— Tidak ada induk —'),

                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(3)
                    ->maxLength(1000),

                Forms\Components\TextInput::make('icon')
                    ->label('Icon')
                    ->maxLength(100),

                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),

                Forms\Components\TextInput::make('sort_order')
                    ->label('Urutan')
                    ->numeric()
                    ->default(0),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable(),

                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Induk')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Aktif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategorys::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
            'view' => Pages\ViewCategory::route('/{record}'),
        ];
    }
}

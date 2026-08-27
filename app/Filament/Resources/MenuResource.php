<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MenuResource\Pages;
use App\Models\Menu;
use Filament\Actions;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class MenuResource extends EnterpriseResource
{
    protected static ?string $model = Menu::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-bars-3-bottom-left';
    protected static \UnitEnum|string|null $navigationGroup = 'CMS';
    protected static ?string $navigationLabel = 'Menu Website';
    protected static ?int $navigationSort = 7;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Menu')->columns(2)->schema([
                TextInput::make('name')->label('Nama menu')->required()->maxLength(100),
                Select::make('location')->options(['header' => 'Header', 'footer' => 'Footer', 'mobile' => 'Mobile', 'sidebar' => 'Sidebar'])->required()->unique(ignoreRecord: true),
                Toggle::make('is_active')->default(true),
            ]),
            Section::make('Item Menu')->schema([
                Repeater::make('allItems')->relationship()->orderColumn('sort_order')->reorderable()->collapsible()->itemLabel(fn (array $state) => $state['label'] ?? 'Item')->schema([
                    TextInput::make('label')->required()->maxLength(100),
                    TextInput::make('url')->required()->maxLength(500)->helperText('Boleh URL relatif, contoh /booking.'),
                    Select::make('target')->options(['_self' => 'Tab yang sama', '_blank' => 'Tab baru'])->default('_self')->required(),
                    TextInput::make('icon')->helperText('Nama Heroicon opsional')->maxLength(100),
                    Toggle::make('is_active')->default(true),
                ])->columns(2),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->searchable(),
            Tables\Columns\TextColumn::make('location')->badge(),
            Tables\Columns\TextColumn::make('all_items_count')->counts('allItems')->label('Items'),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
        ])->recordActions([Actions\EditAction::make(), Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListMenus::route('/'), 'create' => Pages\CreateMenu::route('/create'), 'edit' => Pages\EditMenu::route('/{record}/edit')];
    }
}

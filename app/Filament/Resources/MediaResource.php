<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MediaResource\Pages;
use App\Models\Media;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class MediaResource extends EnterpriseResource
{
    protected static ?string $model = Media::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-photo';

    protected static \UnitEnum|string|null $navigationGroup = 'CMS';

    protected static ?string $navigationLabel = 'Media Library';

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            FileUpload::make('path')->label('File')->disk('public')->directory('cms-media')->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml', 'application/pdf'])->maxSize(10240)->required(),
            TextInput::make('alt_text')->label('Alt text')->maxLength(255),
            Textarea::make('caption')->maxLength(1000),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\ImageColumn::make('path')->disk('public')->label('Preview')->square(),
            Tables\Columns\TextColumn::make('original_name')->searchable(),
            Tables\Columns\TextColumn::make('mime_type')->badge(),
            Tables\Columns\TextColumn::make('file_size')->formatStateUsing(fn (int $state) => number_format($state / 1024, 1).' KB')->sortable(),
            Tables\Columns\TextColumn::make('alt_text')->searchable()->limit(40),
        ])->recordActions([Actions\EditAction::make(), Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListMedia::route('/'), 'create' => Pages\CreateMedia::route('/create'), 'edit' => Pages\EditMedia::route('/{record}/edit')];
    }
}

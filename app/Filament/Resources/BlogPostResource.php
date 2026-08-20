<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlogPostResource\Pages;
use App\Models\BlogPost;
use Filament\Forms;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Schemas\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;
use BackedEnum;

class BlogPostResource extends Resource
{
    protected static ?string $model = BlogPost::class;

    protected static BackedEnum | string | null $navigationIcon = 'heroicon-o-pencil';

    protected static string | UnitEnum | null $navigationGroup = '📢 Konten & Marketing';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Artikel Blog';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Schemas\Components\Section::make('Konten')->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Judul')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Get $set, ?string $state) => $set('slug', Str::slug($state ?? ''))),
                Forms\Components\TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Forms\Components\Textarea::make('excerpt')
                    ->label('Ringkasan')
                    ->rows(3)
                    ->maxLength(500),
                Forms\Components\RichEditor::make('content')
                    ->label('Konten')
                    ->required()
                    ->columnSpanFull(),
            ])->columns(2),

            Schemas\Components\Section::make('Publishing')->schema([
                Forms\Components\Select::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('author_id')
                    ->label('Penulis')
                    ->relationship('author', 'name')
                    ->searchable()
                    ->preload()
                    ->default(fn () => auth()->id()),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Diterbitkan',
                        'archived' => 'Diarsipkan',
                    ])
                    ->default('draft')
                    ->required(),
                Forms\Components\DateTimePicker::make('published_at')
                    ->label('Tanggal Publish'),
                Forms\Components\Toggle::make('is_featured')
                    ->label('Unggulan')
                    ->default(false),
            ])->columns(2),

            Schemas\Components\Section::make('SEO')->schema([
                Forms\Components\TextInput::make('meta_title')
                    ->label('Meta Title')
                    ->maxLength(255),
                Forms\Components\Textarea::make('meta_description')
                    ->label('Meta Description')
                    ->rows(2)
                    ->maxLength(300),
                Forms\Components\FileUpload::make('featured_image')
                    ->label('Gambar Utama')
                    ->image()
                    ->disk('public')
                    ->directory('blog')
                    ->maxSize(5120),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('author.name')
                    ->label('Penulis')
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'published' => 'success',
                        'archived' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'published' => 'Diterbitkan',
                        'archived' => 'Diarsipkan',
                        default => ucfirst($state),
                    }),
                Tables\Columns\TextColumn::make('views_count')
                    ->label('Views')
                    ->sortable(),
                Tables\Columns\TextColumn::make('published_at')
                    ->label('Publish')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Diterbitkan',
                        'archived' => 'Diarsipkan',
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
            'index' => Pages\ListBlogPosts::route('/'),
            'create' => Pages\CreateBlogPost::route('/create'),
            'edit' => Pages\EditBlogPost::route('/{record}/edit'),
            'view' => Pages\ViewBlogPost::route('/{record}'),
        ];
    }
}

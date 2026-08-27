<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages;
use App\Models\Page;
use Filament\Actions;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Get;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PageResource extends EnterpriseResource
{
    protected static ?string $model = Page::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';
    protected static \UnitEnum|string|null $navigationGroup = 'CMS';
    protected static ?string $navigationLabel = 'Halaman';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Halaman')->columns(2)->schema([
                TextInput::make('title')->label('Judul')->required()->maxLength(255)->live(onBlur: true)
                    ->afterStateUpdated(fn ($set, ?string $state) => $set('slug', Str::slug($state ?? ''))),
                TextInput::make('slug')->required()->unique(ignoreRecord: true)->maxLength(255),
                Select::make('template')->options(['default' => 'Default', 'landing' => 'Landing Page', 'full_width' => 'Full Width'])->default('default')->required(),
                Select::make('status')->options(['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived'])->default('draft')->required(),
                DateTimePicker::make('publish_at')->label('Jadwal publish'),
                Select::make('author_id')->relationship('author', 'name')->default(fn () => auth()->id())->searchable()->preload(),
                RichEditor::make('content')->label('Konten fallback')->columnSpanFull(),
            ]),
            Section::make('Page Builder')->description('Susun block dengan drag-and-drop. Data block berupa pasangan key/value agar fleksibel dan tetap dapat diedit tanpa source code.')->schema([
                Repeater::make('sections')->relationship()->orderColumn('sort_order')->reorderable()->collapsible()->cloneable()->itemLabel(fn (array $state) => ($state['name'] ?? null) ?: Str::headline($state['block_type'] ?? 'Block'))->schema([
                    Select::make('block_type')->label('Tipe block')->options([
                        'hero' => 'Hero', 'heading' => 'Heading', 'rich_text' => 'Rich Text', 'image' => 'Image',
                        'gallery' => 'Gallery', 'cta' => 'CTA', 'features' => 'Features', 'vehicle_list' => 'Vehicle List',
                        'pricing' => 'Pricing', 'faq' => 'FAQ', 'testimonial' => 'Testimonial', 'contact' => 'Contact',
                        'map' => 'Map', 'custom_html' => 'Custom HTML (sanitized)',
                    ])->required()->live(),
                    TextInput::make('name')->label('Nama internal')->maxLength(150),
                    Toggle::make('is_visible')->label('Tampilkan')->default(true),
                    KeyValue::make('data')->label('Konten block')->keyLabel('Field')->valueLabel('Nilai')->addActionLabel('Tambah field')->columnSpanFull()->required(),
                ])->columns(3)->columnSpanFull(),
            ]),
            Section::make('SEO')->relationship('seoMeta')->columns(2)->schema([
                TextInput::make('meta_title')->label('Meta title')->maxLength(70),
                Textarea::make('meta_description')->label('Meta description')->maxLength(170),
                TextInput::make('canonical_url')->url(),
                TextInput::make('og_title')->label('OG title')->maxLength(100),
                Textarea::make('og_description')->label('OG description')->maxLength(250),
                FileUpload::make('og_image')->image()->disk('public')->directory('seo'),
                Toggle::make('is_indexable')->label('Index')->default(true),
                Toggle::make('is_followable')->label('Follow')->default(true),
                KeyValue::make('schema_json')->label('Schema JSON-LD')->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('title')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('slug')->copyable()->searchable(),
            Tables\Columns\TextColumn::make('status')->badge(),
            Tables\Columns\TextColumn::make('sections_count')->counts('sections')->label('Blocks'),
            Tables\Columns\TextColumn::make('publish_at')->dateTime('d M Y H:i'),
        ])->recordActions([
            Actions\Action::make('open')->label('Buka')->icon('heroicon-o-arrow-top-right-on-square')->url(fn (Page $record) => url('/'.$record->slug))->openUrlInNewTab()->visible(fn (Page $record) => $record->status === 'published'),
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}

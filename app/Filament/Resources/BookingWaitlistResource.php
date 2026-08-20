<?php
namespace App\Filament\Resources;
use App\Filament\Resources\BookingWaitlistResource\Pages;
use App\Models\BookingWaitlist;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
class BookingWaitlistResource extends Resource
{
    protected static ?string $model = BookingWaitlist::class;
    protected static \BackedEnum|string|null $navigationIcon='heroicon-o-queue-list';
    protected static \UnitEnum|string|null $navigationGroup='📋 Penjualan';
    protected static ?string $navigationLabel='Daftar Tunggu'; protected static ?int $navigationSort=24;
    public static function form(Schema $schema): Schema { return $schema->components([
        Forms\Components\Select::make('customer_id')->relationship('customer','name')->searchable()->preload()->required(),
        Forms\Components\Select::make('category_id')->relationship('category','name')->searchable()->preload(),
        Forms\Components\Select::make('location_id')->relationship('location','name')->searchable()->preload(),
        Forms\Components\DatePicker::make('start_date')->required(), Forms\Components\DatePicker::make('end_date')->required()->afterOrEqual('start_date'),
        Forms\Components\TextInput::make('priority')->numeric()->default(100)->minValue(1),
        Forms\Components\Select::make('status')->options(['waiting'=>'Menunggu','offered'=>'Ditawarkan','converted'=>'Menjadi booking','expired'=>'Kedaluwarsa','cancelled'=>'Dibatalkan'])->default('waiting')->required(),
        Forms\Components\Textarea::make('notes')->columnSpanFull(),
    ])->columns(2); }
    public static function table(Table $table): Table { return $table->columns([
        Tables\Columns\TextColumn::make('customer.name')->label('Customer')->searchable(), Tables\Columns\TextColumn::make('category.name')->label('Kategori'),
        Tables\Columns\TextColumn::make('start_date')->label('Mulai')->date(), Tables\Columns\TextColumn::make('end_date')->label('Selesai')->date(),
        Tables\Columns\TextColumn::make('priority')->label('Prioritas')->sortable(), Tables\Columns\TextColumn::make('status')->badge(),
    ])->defaultSort('priority')->recordActions([Actions\Action::make('offer')->label('Tawarkan')->icon('heroicon-o-paper-airplane')->action(fn($r)=>$r->update(['status'=>'offered','offered_at'=>now(),'expires_at'=>now()->addHours(6)]))->visible(fn($r)=>$r->status==='waiting'),Actions\EditAction::make()]); }
    public static function getPages(): array { return ['index'=>Pages\ListBookingWaitlists::route('/'),'create'=>Pages\CreateBookingWaitlist::route('/create'),'edit'=>Pages\EditBookingWaitlist::route('/{record}/edit')]; }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationTemplateResource\Pages;
use App\Models\NotificationTemplate;
use Filament\Actions;
use Filament\Forms;
use App\Filament\Resources\EnterpriseResource as Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class NotificationTemplateResource extends Resource
{
    protected static ?string $model = NotificationTemplate::class;
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static \UnitEnum|string|null $navigationGroup = 'Settings';
    protected static ?string $navigationLabel = 'Template Notifikasi';
    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\TextInput::make('name')->label('Nama template')->required(),
            Forms\Components\Select::make('provider_id')->label('Provider BYOK')->relationship('provider', 'name')->searchable()->preload()->required(),
            Forms\Components\TextInput::make('event_type')->label('Kode event')->helperText('Contoh: booking_confirmation, waitlist_offer, payment_reminder')->required(),
            Forms\Components\Select::make('channel')->options(['sms'=>'SMS','whatsapp'=>'WhatsApp','email'=>'Email','push'=>'Push'])->required(),
            Forms\Components\TextInput::make('subject')->label('Subjek'),
            Forms\Components\Textarea::make('body')->label('Isi pesan')->helperText('Variabel memakai format {customer_name}, {order_number}, dan seterusnya.')->rows(8)->required()->columnSpanFull(),
            Forms\Components\KeyValue::make('variables')->label('Dokumentasi variabel')->columnSpanFull(),
            Forms\Components\Toggle::make('is_active')->label('Aktif')->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->label('Template')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('event_type')->label('Event')->badge()->searchable(),
            Tables\Columns\TextColumn::make('channel')->label('Kanal')->badge(),
            Tables\Columns\TextColumn::make('provider.name')->label('Provider'),
            Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
        ])->recordActions([Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index'=>Pages\ListNotificationTemplates::route('/'),'create'=>Pages\CreateNotificationTemplate::route('/create'),'edit'=>Pages\EditNotificationTemplate::route('/{record}/edit')];
    }
}

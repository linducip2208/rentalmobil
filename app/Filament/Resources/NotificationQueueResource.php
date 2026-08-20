<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationQueueResource\Pages;
use App\Models\NotificationQueue;
use Filament\Actions;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class NotificationQueueResource extends Resource
{
    protected static ?string $model=NotificationQueue::class;
    protected static \BackedEnum|string|null $navigationIcon='heroicon-o-inbox-stack';
    protected static \UnitEnum|string|null $navigationGroup='⚙️ Sistem & Integrasi';
    protected static ?string $navigationLabel='Antrean Notifikasi';
    protected static ?int $navigationSort=4;
    public static function form(Schema $schema):Schema{return $schema->components([]);}
    public static function table(Table $table):Table{return $table->columns([
        Tables\Columns\TextColumn::make('created_at')->label('Dibuat')->dateTime('d M Y H:i')->sortable(),
        Tables\Columns\TextColumn::make('event_type')->label('Event')->searchable(), Tables\Columns\TextColumn::make('channel')->label('Kanal')->badge(),
        Tables\Columns\TextColumn::make('provider.name')->label('Provider'), Tables\Columns\TextColumn::make('status')->badge(),
        Tables\Columns\TextColumn::make('attempts')->label('Percobaan'), Tables\Columns\TextColumn::make('error_message')->label('Error')->limit(60)->tooltip(fn($record)=>$record->error_message),
    ])->defaultSort('created_at','desc')->recordActions([
        Actions\Action::make('retry')->label('Coba lagi')->icon('heroicon-o-arrow-path')->action(fn(NotificationQueue $record)=>$record->update(['status'=>'pending','scheduled_at'=>now(),'failed_at'=>null,'error_message'=>null]))->visible(fn(NotificationQueue $record)=>$record->status==='failed'),
    ]);}
    public static function getPages():array{return['index'=>Pages\ListNotificationQueues::route('/')];}
}

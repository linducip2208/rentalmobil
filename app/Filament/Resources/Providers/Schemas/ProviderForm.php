<?php

namespace App\Filament\Resources\Providers\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\KeyValue;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProviderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([Section::make('Konfigurasi provider')->description('Semua nilai diisi pemilik aplikasi. Tidak ada vendor, endpoint, atau model yang dikunci oleh sistem.')->schema([
                TextInput::make('name')
                    ->label('Nama provider')
                    ->required(),
                Select::make('type')
                    ->label('Jenis integrasi')
                    ->options([
            'payment' => 'Payment',
            'sms' => 'Sms',
            'whatsapp' => 'Whatsapp',
            'gps' => 'Gps',
            'storage' => 'Storage',
            'ai' => 'Ai',
        ])
                    ->required(),
                TextInput::make('api_format')->label('Format API')->placeholder('rest_json, openai_compatible, redirect, smpp'),
                TextInput::make('base_url')
                    ->label('Base URL / endpoint')
                    ->url(),
                TextInput::make('api_key')
                    ->label('API key / secret')
                    ->password()->revealable()->dehydrated(fn (?string $state) => filled($state))
                    ->helperText('Terenkripsi saat disimpan dan tidak pernah ditampilkan kembali.'),
                KeyValue::make('extra_headers')->label('Header tambahan')->columnSpanFull(),
                KeyValue::make('config')->label('Konfigurasi tambahan')->columnSpanFull(),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true)
                    ->required(),
            ])->columns(2)]);
    }
}

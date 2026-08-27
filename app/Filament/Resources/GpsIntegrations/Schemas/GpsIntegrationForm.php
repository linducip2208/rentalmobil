<?php

namespace App\Filament\Resources\GpsIntegrations\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class GpsIntegrationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Provider & format')->description('Nama provider, endpoint, dan kredensial sepenuhnya diisi pemilik aplikasi.')->schema([
                    Select::make('provider_id')->label('Provider GPS')->relationship('provider', 'name', fn ($q) => $q->where('type', 'gps'))->searchable()->preload()->required()->unique(ignoreRecord: true),
                    Select::make('adapter_format')->label('Format adapter')->options([
                        'rest_polling' => 'REST JSON — polling',
                        'webhook_json' => 'REST JSON — webhook',
                        'traccar_compatible' => 'Traccar-compatible REST',
                        'tcp_gateway' => 'Gateway TCP/UDP eksternal',
                    ])->required()->live(),
                    Select::make('auth_type')->label('Format autentikasi')->options(['none' => 'Tanpa auth', 'bearer' => 'Bearer token', 'header' => 'Custom header', 'query' => 'Query parameter', 'basic' => 'Basic auth'])->default('bearer')->required(),
                    TextInput::make('credential_key_name')->label('Nama header/query atau username')->placeholder('Diisi sesuai dokumentasi provider'),
                    TextInput::make('credential_secret')->label('API key / secret BYOK')->password()->revealable()->dehydrated(fn (?string $state) => filled($state))->helperText('Dienkripsi di database dan tidak ditampilkan kembali.'),
                    Toggle::make('is_active')->label('Aktif')->default(true),
                ])->columns(2),
                Section::make('Endpoint')->schema([
                    TextInput::make('devices_endpoint')->label('Path daftar perangkat')->placeholder('/api/devices'),
                    TextInput::make('positions_endpoint')->label('Path posisi')->placeholder('/api/positions'),
                    TextInput::make('events_endpoint')->label('Path event')->placeholder('/api/events'),
                    TextInput::make('commands_endpoint')->label('Path perintah')->placeholder('/api/commands'),
                    Select::make('http_method')->options(['GET' => 'GET', 'POST' => 'POST'])->default('GET'),
                    TextInput::make('poll_interval_minutes')->label('Interval polling (menit)')->numeric()->minValue(1)->default(5),
                    KeyValue::make('request_parameters')->label('Parameter request')->columnSpanFull(),
                    KeyValue::make('response_paths')->label('Path koleksi respons')->helperText('Contoh key: positions atau webhook_records; value: data.positions')->columnSpanFull(),
                ])->columns(2),
                Section::make('Mapping field JSON')->description('Value adalah dotted path pada payload provider. Tidak ada nama field vendor yang dikunci.')->schema([
                    KeyValue::make('field_mapping')->label('Mapping')->helperText('Key internal: device_id, latitude, longitude, speed, heading, recorded_at, accuracy, battery_level. Value diisi sesuai path payload provider.')->required()->columnSpanFull(),
                ]),
                Section::make('Keamanan webhook')->schema([
                    TextInput::make('webhook_signature_header')->label('Nama header signature')->placeholder('Diisi sesuai konfigurasi pengirim'),
                    TextInput::make('webhook_secret')->label('Webhook HMAC secret')->password()->revealable()->dehydrated(fn (?string $state) => filled($state)),
                    TextInput::make('webhook_identifier_field')->label('Field identifier opsional'),
                    Textarea::make('last_error')->label('Error terakhir')->disabled()->columnSpanFull(),
                ])->columns(2),
            ]);
    }
}

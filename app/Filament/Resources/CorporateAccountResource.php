<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CorporateAccountResource\Pages;
use App\Models\CorporateAccount;
use App\Services\CorporateBillingService;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class CorporateAccountResource extends Resource
{
    protected static ?string $model = CorporateAccount::class;
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-building-office-2';
    protected static \UnitEnum|string|null $navigationGroup = '🗂️ Data Utama';
    protected static ?string $navigationLabel = 'Akun Korporat';
    protected static ?int $navigationSort = 8;

    public static function form(Schema $s): Schema
    {
        return $s->components([
            Forms\Components\TextInput::make('name')->label('Nama perusahaan')->required()->maxLength(150),
            Forms\Components\TextInput::make('tax_id')->label('NPWP')->maxLength(50),
            Forms\Components\TextInput::make('contact_name')->label('Nama PIC'),
            Forms\Components\TextInput::make('contact_phone')->label('Telp PIC')->tel(),
            Forms\Components\TextInput::make('contact_email')->label('Email PIC')->email(),
            Forms\Components\Textarea::make('address')->label('Alamat')->columnSpanFull(),
            Forms\Components\TextInput::make('credit_limit')->numeric()->prefix('Rp')->default(0)->helperText('0 = tanpa limit kredit'),
            Forms\Components\TextInput::make('payment_terms_days')->numeric()->suffix('hari')->default(30)->minValue(0)->maxValue(365),
            Forms\Components\TextInput::make('discount_percent')->numeric()->suffix('%')->default(0)->minValue(0)->maxValue(100),
            Forms\Components\Toggle::make('is_active')->label('Aktif')->default(true)->required(),
            Forms\Components\Textarea::make('notes')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $t): Table
    {
        return $t->columns([
            Tables\Columns\TextColumn::make('name')->searchable()->weight('bold'),
            Tables\Columns\TextColumn::make('contact_name')->placeholder('-'),
            Tables\Columns\TextColumn::make('contact_phone')->placeholder('-'),
            Tables\Columns\TextColumn::make('customers_count')->counts('customers')->label('Pelanggan'),
            Tables\Columns\TextColumn::make('credit_limit')->money('IDR')->label('Limit'),
            Tables\Columns\TextColumn::make('outstanding')->label('Outstanding')->state(function (CorporateAccount $r) {
                return 'Rp '.number_format($r->outstandingBalance(), 0, ',', '.');
            }),
            Tables\Columns\TextColumn::make('discount_percent')->suffix('%')->label('Diskon'),
            Tables\Columns\TextColumn::make('is_active')->badge()->formatStateUsing(fn ($state) => $state ? 'Aktif' : 'Nonaktif')->color(fn ($state) => $state ? 'success' : 'danger'),
        ])
        ->recordActions([
            Actions\Action::make('statement')
                ->label('Statement PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('info')
                ->form([
                    Forms\Components\DatePicker::make('from')->label('Dari')->required()->default(now()->startOfMonth()),
                    Forms\Components\DatePicker::make('to')->label('Sampai')->required()->default(now()),
                ])
                ->action(function (CorporateAccount $record, array $data) {
                    $from = \Illuminate\Support\Carbon::parse($data['from']);
                    $to = \Illuminate\Support\Carbon::parse($data['to']);
                    $pdf = app(CorporateBillingService::class)->generateStatementPdf($record, $from, $to);

                    return response()->streamDownload(
                        fn () => print ($pdf->output()),
                        "Statement-{$record->name}-{$from->format('Ymd')}-{$to->format('Ymd')}.pdf",
                    );
                }),
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCorporateAccounts::route('/'),
            'create' => Pages\CreateCorporateAccount::route('/create'),
            'edit' => Pages\EditCorporateAccount::route('/{record}/edit'),
        ];
    }
}

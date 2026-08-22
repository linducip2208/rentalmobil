<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BankStatementImportResource\Pages;
use App\Models\BankStatementImport;
use App\Services\BankReconciliationService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class BankStatementImportResource extends Resource
{
    protected static ?string $model = BankStatementImport::class;
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-building-library';
    protected static \UnitEnum|string|null $navigationGroup = '💰 Keuangan';
    protected static ?string $navigationLabel = 'Rekonsiliasi Bank';
    protected static ?int $navigationSort = 25;

    public static function form(Schema $s): Schema
    {
        return $s->components([
            Forms\Components\Select::make('bank_account_id')
                ->relationship('bankAccount', 'name')
                ->label('Rekening bank')
                ->searchable()
                ->helperText('Opsional — untuk pencatatan.'),
            Forms\Components\FileUpload::make('file')
                ->label('File rekening koran (CSV)')
                ->disk('local')
                ->directory('bank-imports')
                ->acceptedFileTypes(['text/csv', 'text/plain', 'application/csv', '.csv', '.txt'])
                ->required()
                ->helperText('Kolom yang dikenali: Tanggal, Keterangan, Mutasi Masuk, Mutasi Keluar, No. Referensi. Pemisah ; atau koma.')
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $t): Table
    {
        return $t->columns([
            Tables\Columns\TextColumn::make('file_name')->searchable(),
            Tables\Columns\TextColumn::make('bankAccount.name')->placeholder('-'),
            Tables\Columns\TextColumn::make('period_start')->date()->label('Mulai'),
            Tables\Columns\TextColumn::make('period_end')->date()->label('Sampai'),
            Tables\Columns\TextColumn::make('total_lines')->label('Baris'),
            Tables\Columns\TextColumn::make('matched_count')->label('Cocok'),
            Tables\Columns\TextColumn::make('status')->badge()->color(fn (string $state) => match ($state) {
                'posted' => 'success',
                'ready' => 'warning',
                default => 'gray',
            }),
        ])
        ->recordActions([
            Tables\Actions\Action::make('autoMatch')
                ->label('Auto-match')
                ->icon('heroicon-o-link')
                ->color('info')
                ->action(function (BankStatementImport $record) {
                    $n = app(BankReconciliationService::class)->autoMatch($record);
                    Notification::make()->title("{$n} baris cocok otomatis")->success()->send();
                }),
            Tables\Actions\Action::make('verifyAll')
                ->label('Verifikasi semua yang cocok')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->requiresConfirmation()
                ->modalDescription('Semua pembayaran yang cocok akan diverifikasi dan invoice diperbarui. Lanjutkan?')
                ->action(function (BankStatementImport $record) {
                    $n = app(BankReconciliationService::class)->verifyMatched($record, auth()->id());
                    Notification::make()->title("{$n} pembayaran terverifikasi")->success()->send();
                })
                ->visible(fn (BankStatementImport $record) => $record->matched_count > 0),
            Actions\DeleteAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBankStatementImports::route('/'),
            'create' => Pages\CreateBankStatementImport::route('/create'),
        ];
    }
}

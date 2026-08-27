<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BankStatementLineResource\Pages;
use App\Filament\Resources\EnterpriseResource as Resource;
use App\Models\BankStatementImport;
use App\Models\BankStatementLine;
use App\Models\Payment;
use App\Services\BankReconciliationService;
use App\Services\PaymentService;
use Filament\Actions;
use Filament\Actions\BulkActionGroup;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BankStatementLineResource extends Resource
{
    protected static ?string $model = BankStatementLine::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-list-bullet';

    protected static \UnitEnum|string|null $navigationGroup = 'Finance';

    protected static ?string $navigationLabel = 'Baris Mutasi Bank';

    protected static ?int $navigationSort = 26;

    protected static bool $hasNavigationBadge = false;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $s): Schema
    {
        return $s->components([]);
    }

    public static function table(Table $t): Table
    {
        return $t->modifyQueryUsing(fn (Builder $q) => $q->with('matchedPayment'))
            ->defaultSort('transaction_date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('import.file_name')->label('Import')->toggleable(),
                Tables\Columns\TextColumn::make('transaction_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('description')->limit(40)->searchable(),
                Tables\Columns\TextColumn::make('amount_in')->money('IDR')->label('Masuk'),
                Tables\Columns\TextColumn::make('amount_out')->money('IDR')->label('Keluar'),
                Tables\Columns\TextColumn::make('reference')->limit(20)->placeholder('-'),
                Tables\Columns\TextColumn::make('match_status')->badge()->color(fn (string $state) => match ($state) {
                    'matched' => 'success',
                    'ignored' => 'gray',
                    'conflict' => 'danger',
                    default => 'warning',
                })->label('Status'),
                Tables\Columns\TextColumn::make('matchedPayment.payment_number')->label('Pembayaran')->placeholder('-'),
                Tables\Columns\TextColumn::make('match_confidence')->label('Keyakinan')->formatStateUsing(fn ($state) => $state ? ((int) round($state * 100)).'%' : '-'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('import')
                    ->relationship('import', 'file_name')
                    ->label('Import'),
                Tables\Filters\SelectFilter::make('match_status')
                    ->options(['unmatched' => 'Belum cocok', 'matched' => 'Cocok', 'ignored' => 'Diabaikan', 'conflict' => 'Konflik'])
                    ->label('Status match'),
            ])
            ->recordActions([
                Tables\Actions\Action::make('matchManual')
                    ->label('Cocokkan manual')
                    ->icon('heroicon-o-link')
                    ->visible(fn (BankStatementLine $r) => $r->match_status === 'unmatched')
                    ->form([
                        Forms\Components\Select::make('payment_id')
                            ->label('Pembayaran pending')
                            ->options(fn () => Payment::where('status', 'pending')->orderByDesc('id')->limit(200)->get()->mapWithKeys(fn ($p) => [$p->id => "{$p->payment_number} â€” Rp ".number_format((float) $p->amount, 0, ',', '.')])->all())
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (BankStatementLine $r, array $data) {
                        $r->update(['match_status' => 'matched', 'matched_payment_id' => $data['payment_id'], 'match_confidence' => null, 'match_note' => 'Cocokkan manual oleh admin']);
                        Notification::make()->title('Baris dicocokkan manual')->success()->send();
                    }),
                Tables\Actions\Action::make('ignore')
                    ->label('Abaikan')
                    ->icon('heroicon-o-eye-slash')
                    ->color('gray')
                    ->visible(fn (BankStatementLine $r) => $r->match_status !== 'ignored')
                    ->action(function (BankStatementLine $r) {
                        $r->update(['match_status' => 'ignored']);
                        Notification::make()->title('Baris diabaikan')->send();
                    }),
                Tables\Actions\Action::make('verifyOne')
                    ->label('Verifikasi')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (BankStatementLine $r) => $r->match_status === 'matched' && $r->matchedPayment?->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (BankStatementLine $r) {
                        app(PaymentService::class)->verifyPayment($r->matchedPayment, auth()->id());
                        Notification::make()->title('Pembayaran terverifikasi')->success()->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    Actions\BulkAction::make('autoMatchSelected')
                        ->label('Auto-match terpilih')
                        ->icon('heroicon-o-link')
                        ->action(function ($records) {
                            $service = app(BankReconciliationService::class);
                            $n = 0;
                            foreach ($records as $line) {
                                $line->update(['match_status' => 'unmatched', 'matched_payment_id' => null]);
                            }
                            foreach ($records->pluck('import_id')->unique() as $importId) {
                                $n += $service->autoMatch(BankStatementImport::find($importId));
                            }
                            Notification::make()->title("{$n} baris cocok")->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBankStatementLines::route('/'),
        ];
    }
}

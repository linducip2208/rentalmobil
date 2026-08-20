<?php

namespace App\Filament\Widgets;

use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\ApprovalWorkflow;
use Illuminate\Support\Facades\Auth;

class PendingApprovalsWidget extends BaseWidget
{
    protected static ?string $heading = 'Menunggu Persetujuan';

    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return in_array(auth()->user()?->role, ['owner', 'manager', 'admin_operasional']);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ApprovalWorkflow::query()
                    ->with(['requestedBy'])
                    ->pending()
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'discount' => 'Diskon',
                        'cancellation' => 'Pembatalan',
                        'refund' => 'Refund',
                        'upgrade' => 'Upgrade',
                        'expense' => 'Pengeluaran',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'discount' => 'info',
                        'cancellation' => 'danger',
                        'refund' => 'warning',
                        'upgrade' => 'success',
                        'expense' => 'primary',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Jumlah')
                    ->formatStateUsing(fn ($state): string => $state ? 'Rp ' . number_format((float) $state, 0, ',', '.') : '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('requestedBy.name')
                    ->label('Diajukan Oleh')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('reason')
                    ->label('Alasan')
                    ->limit(50),
            ])
            ->actions([
                Actions\Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Setujui Permintaan')
                    ->modalDescription('Apakah Anda yakin ingin menyetujui permintaan ini?')
                    ->action(fn (ApprovalWorkflow $record) => $record->approve(Auth::id()))
                    ->visible(fn (ApprovalWorkflow $record): bool => $record->status === 'pending'),
                Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Tolak Permintaan')
                    ->modalForm([
                        \Filament\Schemas\Components\Textarea::make('reason')
                            ->label('Alasan Penolakan')
                            ->required(),
                    ])
                    ->action(fn (ApprovalWorkflow $record, array $data) => $record->reject(Auth::id(), $data['reason']))
                    ->visible(fn (ApprovalWorkflow $record): bool => $record->status === 'pending'),
            ])
            ->poll('30s');
    }
}

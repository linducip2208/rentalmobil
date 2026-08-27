<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountingPeriodResource\Pages;
use App\Models\AccountingPeriod;
use App\Services\PeriodClosingService;
use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class AccountingPeriodResource extends EnterpriseResource
{
    protected static ?string $model = AccountingPeriod::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static string|UnitEnum|null $navigationGroup = 'Finance';

    protected static ?string $navigationLabel = 'Periode Akuntansi';

    protected static ?int $navigationSort = 30;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([Forms\Components\TextInput::make('fiscal_year')->label('Tahun Fiskal')->numeric()->required(), Forms\Components\TextInput::make('period_number')->label('Periode')->numeric()->minValue(1)->maxValue(12)->required(), Forms\Components\DatePicker::make('start_date')->required(), Forms\Components\DatePicker::make('end_date')->required()->afterOrEqual('start_date'), Forms\Components\Select::make('status')->options(['open' => 'Open', 'soft_closed' => 'Soft Closed', 'closed' => 'Closed'])->default('open')->disabled(), Forms\Components\Textarea::make('closing_notes')->columnSpanFull()])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([Tables\Columns\TextColumn::make('fiscal_year')->sortable(), Tables\Columns\TextColumn::make('period_number')->label('Periode')->sortable(), Tables\Columns\TextColumn::make('start_date')->date('d/m/Y'), Tables\Columns\TextColumn::make('end_date')->date('d/m/Y'), Tables\Columns\TextColumn::make('status')->badge()->color(fn ($s) => match ($s) {
            'open' => 'success','soft_closed' => 'warning','closed' => 'danger',default => 'gray'
        }), Tables\Columns\TextColumn::make('closedBy.name')->label('Ditutup Oleh'), Tables\Columns\TextColumn::make('closed_at')->dateTime('d/m/Y H:i')])->actions([Actions\Action::make('soft_close')->label('Soft Close')->visible(fn ($r) => $r->status === 'open' && static::allows('close'))->requiresConfirmation()->action(fn ($r) => $r->update(['status' => 'soft_closed', 'closed_by' => auth()->id(), 'closed_at' => now()])), Actions\Action::make('close')->label('Final Close')->color('danger')->visible(fn ($r) => $r->status !== 'closed' && static::allows('close'))->requiresConfirmation()->action(fn ($r) => app(PeriodClosingService::class)->close($r)), Actions\Action::make('reopen')->label('Reopen')->visible(fn ($r) => $r->status !== 'open' && static::allows('reopen'))->requiresConfirmation()->action(fn ($r) => app(PeriodClosingService::class)->reopen($r)), Actions\EditAction::make()->visible(fn ($r) => $r->status === 'open')]);
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListAccountingPeriods::route('/'), 'create' => Pages\CreateAccountingPeriod::route('/create'), 'edit' => Pages\EditAccountingPeriod::route('/{record}/edit')];
    }
}

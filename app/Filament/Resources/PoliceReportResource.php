<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PoliceReportResource\Pages;
use App\Models\PoliceReport;
use Filament\Forms;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;
use UnitEnum;
use BackedEnum;

class PoliceReportResource extends Resource
{
    protected static ?string $model = PoliceReport::class;

    protected static BackedEnum | string | null $navigationIcon = 'heroicon-o-building-library';

    protected static string | UnitEnum | null $navigationGroup = '🛡️ Security';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Laporan Polisi';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Schemas\Components\Section::make('Informasi Laporan')->schema([
                Forms\Components\Select::make('investigation_case_id')
                    ->label('Kasus Investigasi')
                    ->relationship('investigationCase', 'case_number')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('vehicle_id')
                    ->label('Kendaraan')
                    ->relationship('vehicle', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('rental_order_id')
                    ->label('Rental Order')
                    ->relationship('rentalOrder', 'order_number')
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('report_number')
                    ->label('No. Laporan')
                    ->maxLength(100)
                    ->required()
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('police_station')
                    ->label('Kantor Polisi')
                    ->maxLength(255)
                    ->required(),
                Forms\Components\TextInput::make('officer_name')
                    ->label('Nama Petugas')
                    ->maxLength(255),
                Forms\Components\DatePicker::make('report_date')
                    ->label('Tanggal Laporan')
                    ->required(),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'filed' => 'Diajukan',
                        'investigating' => 'Investigasi',
                        'resolved' => 'Selesai',
                        'no_action' => 'Tindakan',
                    ])
                    ->default('filed')
                    ->required(),
            ])->columns(2),

            Schemas\Components\Section::make('Detail & Dokumen')->schema([
                Forms\Components\Textarea::make('report_text')
                    ->label('Isi Laporan')
                    ->rows(4),
                Forms\Components\Textarea::make('documents')
                    ->label('Dokumen (JSON)')
                    ->rows(2)
                    ->placeholder('["path/doc1.pdf", "path/doc2.jpg"]'),
            ])->columns(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('report_number')
                    ->label('No. Laporan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('vehicle.name')
                    ->label('Kendaraan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('police_station')
                    ->label('Kantor Polisi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('officer_name')
                    ->label('Petugas'),
                Tables\Columns\TextColumn::make('report_date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('investigationCase.case_number')
                    ->label('Kasus'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'filed' => 'info',
                        'investigating' => 'warning',
                        'resolved' => 'success',
                        'no_action' => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'filed' => 'Diajukan',
                        'investigating' => 'Investigasi',
                        'resolved' => 'Selesai',
                        'no_action' => 'Tindakan',
                    ]),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPoliceReports::route('/'),
            'create' => Pages\CreatePoliceReport::route('/create'),
            'edit' => Pages\EditPoliceReport::route('/{record}/edit'),
            'view' => Pages\ViewPoliceReport::route('/{record}'),
        ];
    }
}

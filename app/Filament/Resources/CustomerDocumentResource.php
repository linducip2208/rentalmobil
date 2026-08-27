<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerDocumentResource\Pages;
use App\Models\CustomerDocument;
use Filament\Forms;
use Filament\Schemas;
use Filament\Schemas\Schema;
use App\Filament\Resources\EnterpriseResource as Resource;
use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;
use UnitEnum;
use BackedEnum;

class CustomerDocumentResource extends Resource
{
    protected static ?string $model = CustomerDocument::class;

    protected static BackedEnum | string | null $navigationIcon = 'heroicon-o-identification';

    protected static string | UnitEnum | null $navigationGroup = 'Customers';

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationLabel = 'Dokumen Pelanggan';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Schemas\Components\Section::make('Informasi Dokumen')->schema([
                Forms\Components\Select::make('customer_id')
                    ->label('Customer')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('document_type')
                    ->label('Jenis Dokumen')
                    ->options([
                        'ktp' => 'KTP',
                        'sim' => 'SIM',
                        'passport' => 'Paspor',
                        'stnk' => 'STNK',
                        'other' => 'Lainnya',
                    ])
                    ->required()
                    ->maxLength(50),
                Forms\Components\TextInput::make('document_number')
                    ->label('No. Dokumen')
                    ->maxLength(100)
                    ->required(),
                Forms\Components\FileUpload::make('document_url')
                    ->label('File Dokumen')
                    ->disk('public')
                    ->directory('customer-documents')
                    ->maxSize(5120),
                Forms\Components\DatePicker::make('expiry_date')
                    ->label('Tanggal Kadaluarsa'),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Menunggu',
                        'verified' => 'Terverifikasi',
                        'rejected' => 'Ditolak',
                        'expired' => 'Kadaluarsa',
                    ])
                    ->default('pending')
                    ->required(),
            ])->columns(2),

            Schemas\Components\Section::make('Verifikasi')->schema([
                Forms\Components\Select::make('verified_by')
                    ->label('Diverifikasi Oleh')
                    ->relationship('verifiedBy', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\DateTimePicker::make('verified_at')
                    ->label('Waktu Verifikasi'),
                Forms\Components\Textarea::make('rejection_reason')
                    ->label('Alasan Penolakan')
                    ->rows(2),
                Forms\Components\Textarea::make('notes')
                    ->label('Catatan')
                    ->rows(2),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable(),
                Tables\Columns\TextColumn::make('document_type')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'ktp' => 'info',
                        'sim' => 'info',
                        'passport' => 'success',
                        'stnk' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('document_number')
                    ->label('No. Dokumen')
                    ->searchable(),
                Tables\Columns\TextColumn::make('expiry_date')
                    ->label('Kadaluarsa')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'verified' => 'success',
                        'rejected' => 'danger',
                        'expired' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('verifiedBy.name')
                    ->label('Diverifikasi Oleh'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Menunggu',
                        'verified' => 'Terverifikasi',
                        'rejected' => 'Ditolak',
                        'expired' => 'Kadaluarsa',
                    ]),
                Tables\Filters\SelectFilter::make('document_type')
                    ->label('Jenis Dokumen')
                    ->options([
                        'ktp' => 'KTP',
                        'sim' => 'SIM',
                        'passport' => 'Paspor',
                        'stnk' => 'STNK',
                        'other' => 'Lainnya',
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
            'index' => Pages\ListCustomerDocuments::route('/'),
            'create' => Pages\CreateCustomerDocument::route('/create'),
            'edit' => Pages\EditCustomerDocument::route('/{record}/edit'),
            'view' => Pages\ViewCustomerDocument::route('/{record}'),
        ];
    }
}

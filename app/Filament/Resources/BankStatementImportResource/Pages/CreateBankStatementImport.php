<?php

namespace App\Filament\Resources\BankStatementImportResource\Pages;

use App\Filament\Resources\BankStatementImportResource;
use App\Services\BankReconciliationService;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CreateBankStatementImport extends CreateRecord
{
    protected static string $resource = BankStatementImportResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $content = Storage::disk('local')->get($data['file']);

        return app(BankReconciliationService::class)->import(
            $data['bank_account_id'] ?? null,
            $content,
            basename($data['file']),
            auth()->id(),
        );
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Rekening koran terimport & siap di-auto-match';
    }
}

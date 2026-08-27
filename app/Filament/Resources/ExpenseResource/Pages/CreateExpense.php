<?php

namespace App\Filament\Resources\ExpenseResource\Pages;

use App\Filament\Resources\ExpenseResource;
use App\Services\AccountingService;
use App\Services\ApprovalService;
use Filament\Resources\Pages\CreateRecord;

class CreateExpense extends CreateRecord
{
    protected static string $resource = ExpenseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        $data['status'] = 'pending';

        return $data;
    }

    protected function afterCreate(): void
    {
        $service = app(ApprovalService::class);
        if ($service->checkApprovalRequired('expense', (float) $this->record->amount)) {
            $service->submitForApproval($this->record, 'expense', auth()->id());

            return;
        }
        $this->record->update(['status' => 'approved', 'approved_by' => auth()->id(), 'approved_at' => now()]);
        app(AccountingService::class)->recordExpense($this->record);
    }
}

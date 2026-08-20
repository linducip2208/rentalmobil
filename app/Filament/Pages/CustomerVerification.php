<?php

namespace App\Filament\Pages;

use App\Models\Customer;
use App\Models\CustomerDocument;
use Filament\Pages\Page;

class CustomerVerification extends Page
{
    protected string $view = 'filament.pages.customer-verification';

    protected static ?string $title = 'Verifikasi Customer';

    public ?int $customerId = null;
    public ?array $customer = null;
    public array $documents = [];
    public bool $saved = false;

    public function mount(int $customer): void
    {
        $this->customerId = $customer;

        $customerModel = Customer::find($customer);

        if (! $customerModel) {
            abort(404);
        }

        $this->customer = [
            'id' => $customerModel->id,
            'name' => $customerModel->name,
            'email' => $customerModel->email,
            'phone' => $customerModel->phone,
            'customer_type' => $customerModel->customer_type,
            'verification_status' => $customerModel->verification_status,
            'trust_score' => $customerModel->trust_score,
        ];

        $this->loadDocuments();
    }

    public function loadDocuments(): void
    {
        $this->documents = CustomerDocument::where('customer_id', $this->customerId)
            ->get()
            ->map(fn ($doc) => [
                'id' => $doc->id,
                'document_type' => $doc->document_type,
                'document_number' => $doc->document_number ?? '-',
                'document_url' => $doc->document_url,
                'expiry_date' => $doc->expiry_date?->format('d M Y'),
                'status' => $doc->status,
                'rejection_reason' => $doc->rejection_reason,
                'notes' => $doc->notes,
                'is_expired' => $doc->isExpired(),
            ])
            ->toArray();
    }

    public function verifyDocument(int $documentId): void
    {
        $doc = CustomerDocument::find($documentId);

        if (! $doc || $doc->customer_id != $this->customerId) {
            return;
        }

        $doc->update([
            'status' => 'verified',
            'verified_by' => auth()->id(),
            'verified_at' => now(),
            'rejection_reason' => null,
        ]);

        $this->updateCustomerStatus();
        $this->loadDocuments();

        session()->flash('success', "Dokumen {$doc->document_type} berhasil diverifikasi.");
    }

    public function rejectDocument(int $documentId, string $reason = ''): void
    {
        $doc = CustomerDocument::find($documentId);

        if (! $doc || $doc->customer_id != $this->customerId) {
            return;
        }

        $doc->update([
            'status' => 'rejected',
            'verified_by' => auth()->id(),
            'verified_at' => now(),
            'rejection_reason' => $reason ?: 'Dokumen tidak valid',
        ]);

        $this->updateCustomerStatus();
        $this->loadDocuments();

        session()->flash('warning', "Dokumen {$doc->document_type} ditolak.");
    }

    protected function updateCustomerStatus(): void
    {
        $customer = Customer::find($this->customerId);
        $docs = CustomerDocument::where('customer_id', $this->customerId)->get();

        if ($docs->isEmpty()) {
            $customer->update(['verification_status' => 'unverified']);

            return;
        }

        $allVerified = $docs->every('status', 'verified');
        $anyRejected = $docs->contains('status', 'rejected');

        $newStatus = match (true) {
            $allVerified => 'verified',
            $anyRejected => 'rejected',
            default => 'pending',
        };

        $customer->update(['verification_status' => $newStatus]);

        $this->customer['verification_status'] = $newStatus;
    }
}

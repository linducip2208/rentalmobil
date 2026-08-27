<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    protected $fillable = ['public_id', 'provider_id', 'invoice_id', 'customer_id', 'external_id', 'amount', 'currency', 'type', 'status', 'checkout_url', 'request_payload', 'response_payload', 'callback_event_id', 'paid_at'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'request_payload' => 'array', 'response_payload' => 'array', 'paid_at' => 'datetime'];
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Refund extends Model
{
    protected $fillable = [
        'payment_transaction_id',
        'provider',
        'provider_refund_id',
        'amount',
        'currency',
        'status',
    ];

    public function paymentTransaction(): BelongsTo
    {
        return $this->belongsTo(PaymentTransaction::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(RefundItem::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Earning extends Model
{
    protected $fillable = [
        'instructor_id',
        'order_item_id',
        'refund_item_id',
        'subscription_allocation_id',
        'reverses_earning_id',
        'revenue_channel',
        'source_key',
        'currency',
        'allocation_base_amount',
        'platform_amount',
        'payee_amount',
        'revenue_share_percentage_snapshot',
    ];

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function refundItem(): BelongsTo
    {
        return $this->belongsTo(RefundItem::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function reversedEarning(): BelongsTo
    {
        return $this->belongsTo(Earning::class, 'reverses_earning_id');
    }

    public function payout(): BelongsTo
    {
        return $this->belongsTo(Payout::class);
    }
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnRecord extends Model
{
    protected $fillable = [
        'rental_id',
        'admin_id',
        'return_checked_at',
        'cleaning_fee',
        'damage_fee',
        'late_fee',
        'total_penalty',
        'penalty_paid',
        'deposit_used',
        'deposit_refund',
        'delay_days',
        'rent_fee_snapshot',
        'deposit_paid_snapshot',
        'condition_note',
    ];

    protected $casts = [
        'return_checked_at'     => 'datetime',
        'cleaning_fee'          => 'integer',
        'damage_fee'            => 'integer',
        'late_fee'              => 'integer',
        'total_penalty'         => 'integer',
        'penalty_paid'          => 'integer',
        'deposit_used'          => 'integer',
        'deposit_refund'        => 'integer',
        'delay_days'            => 'integer',
        'rent_fee_snapshot'     => 'integer',
        'deposit_paid_snapshot' => 'integer',
    ];

    public function rental(): BelongsTo { return $this->belongsTo(Rental::class); }
    public function admin(): BelongsTo { return $this->belongsTo(User::class, 'admin_id'); }
}

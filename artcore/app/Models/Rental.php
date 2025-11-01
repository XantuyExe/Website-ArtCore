<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, HasOne};

class Rental extends Model
{
    protected $fillable = [
        'user_id','unit_id','status','rental_start','rental_end_plan','rental_end_actual',
        'deposit_required','deposit_paid','rent_fee_paid','eligibility_checked','notes','return_requested_at',
        'penalty_late_fee','penalty_cleaning_fee','penalty_damage_fee','penalty_total_due','penalty_paid','penalty_status','penalty_notes',
    ];

    protected $casts = [
        'rental_start'        => 'datetime',
        'rental_end_plan'     => 'datetime',
        'rental_end_actual'   => 'datetime',
        'return_requested_at' => 'datetime',
        'eligibility_checked' => 'boolean',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function unit(): BelongsTo { return $this->belongsTo(Unit::class); }
    public function payments(): HasMany { return $this->hasMany(Payment::class); }
    public function returnRecord(): HasOne { return $this->hasOne(ReturnRecord::class); }
    public function purchase(): HasOne { return $this->hasOne(Purchase::class); }
    public function penalties(): HasMany { return $this->hasMany(Penalty::class); }

    public function activeSlotCost(): int
    {
        return $this->unit?->isSculptureDoubleSlot() ? 2 : 1;
    }

    public function isReturnRequested(): bool
    {
        return in_array($this->status, ['RETURN_REQUESTED','AWAITING_PENALTY']);
    }

    public function isLate(?Carbon $reference = null): bool
    {
        $reference ??= now();
        return $this->rental_end_plan && $reference->greaterThan($this->rental_end_plan);
    }

    public function lateSeconds(?Carbon $reference = null): int
    {
        $reference ??= now();
        if (!$this->rental_end_plan) {
            return 0;
        }

        $diffSeconds = $reference->diffInSeconds($this->rental_end_plan, false);

        return $diffSeconds < 0 ? abs($diffSeconds) : 0;
    }

    public function lateDays(?Carbon $reference = null): int
    {
        $lateSeconds = $this->lateSeconds($reference);

        return $lateSeconds > 0 ? (int) ceil($lateSeconds / 86400) : 0;
    }

    public function penaltyOutstanding(): int
    {
        $total = (int) $this->penalty_total_due;
        if ($total <= 0) {
            return 0;
        }
        $depositCover = min($total, (int) $this->deposit_paid);
        return max(0, $total - $depositCover - (int) $this->penalty_paid);
    }

    public function hasPenaltyDue(): bool
    {
        return $this->penalty_status === 'DUE' && $this->penaltyOutstanding() > 0;
    }

    public function countdownInfo(?Carbon $reference = null): array
    {
        $reference ??= now();
        if (!$this->rental_end_plan) {
            return [
                'isLate'      => false,
                'diffSeconds' => null,
                'diffHuman'   => null,
                'lateDays'    => 0,
            ];
        }

        $diffSeconds = $reference->diffInSeconds($this->rental_end_plan, false);
        $interval    = CarbonInterval::seconds(abs($diffSeconds))->cascade();
        $human       = abs($diffSeconds) > 0
            ? $interval->forHumans([
                'parts'       => 3,
                'short'       => true,
                'join'        => true,
                'minimumUnit' => 'second',
            ])
            : '0 detik';

        return [
            'isLate'      => $diffSeconds < 0,
            'diffSeconds' => $diffSeconds,
            'diffHuman'   => $human,
            'lateDays'    => $diffSeconds < 0 ? $this->lateDays($reference) : 0,
        ];
    }
}







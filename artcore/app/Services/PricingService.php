<?php

namespace App\Services;

use App\Models\{Rental, Unit};
use Carbon\Carbon;

class PricingService
{
    public function calcDeposit(Unit $unit): int
    {
        if (!$unit->requiresDeposit()) return 0;
        $percent = (int) config('artcore.deposit_percent', 30);
        return (int) round(($unit->sale_price * $percent) / 100);
    }

    public function calcLateFee(Rental $rental, \DateTimeInterface $actual): int
    {
        if (!$rental->rental_end_plan) {
            return 0;
        }

        $reference = Carbon::make($actual) ?? Carbon::parse($actual->format('Y-m-d H:i:s'));
        $lateDays  = $rental->lateDays($reference);
        if ($lateDays <= 0) {
            return 0;
        }

        $percent    = (int) config('artcore.late_fee_percent', 10);
        $baseAmount = (int) ($rental->rent_fee_paid ?: ($rental->unit?->rent_price_5d ?? 0));

        return (int) round(($baseAmount * $percent / 100) * $lateDays);
    }

    public function trialToOwnFinalPrice(Unit $unit, Rental $rental): int
    {
        // harga akhir = sale_price - rent_fee_paid (deposit di-refund jika tak ada potongan)
        return max(0, (int) $unit->sale_price - (int) $rental->rent_fee_paid);
    }
}

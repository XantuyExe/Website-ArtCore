<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReturnConfirmRequest;
use App\Models\{Rental, Penalty, Payment};
use App\Services\PricingService;
use Illuminate\Support\Facades\DB;

class ReturnAdminController extends Controller
{
    public function __construct(private PricingService $pricing) {}

    public function index()
    {
        $requests = Rental::with('user', 'unit.category')
            ->whereIn('status', ['RETURN_REQUESTED', 'AWAITING_PENALTY'])
            ->orderByDesc('return_requested_at')
            ->paginate(30);

        return view('admin.returns.index', compact('requests'));
    }

    public function form(Rental $rental)
    {
        $rental->load('user', 'unit.category');
        abort_if(! in_array($rental->status, ['RETURN_REQUESTED', 'AWAITING_PENALTY']), 422, 'Pengembalian belum diajukan oleh user.');

        $now       = now();
        $lateFee   = $this->pricing->calcLateFee($rental, $now);
        $lateDays  = $rental->lateDays($now);
        $countdown = $rental->countdownInfo($now);

        return view('admin.returns.confirm', compact('rental', 'lateFee', 'lateDays', 'countdown'));
    }

    public function confirm(ReturnConfirmRequest $request, Rental $rental)
    {
        abort_if(! in_array($rental->status, ['ACTIVE', 'RETURN_REQUESTED', 'AWAITING_PENALTY']), 422, 'Status rental tidak valid.');

        $rental->loadMissing('unit', 'user');

        $action        = $request->input('action', 'invoice');
        $now           = now();
        $lateFeeInput  = $this->pricing->calcLateFee($rental, $now);
        $cleaningFee   = (int) $request->input('cleaning_fee', $rental->penalty_cleaning_fee ?? 0);
        $damageFee     = (int) $request->input('damage_fee', $rental->penalty_damage_fee ?? 0);
        $note          = $request->input('condition_note', $rental->penalty_notes);
        $lateDaysInput = $rental->lateDays($now);

        if ($action === 'invoice') {
            $lateFee      = $lateFeeInput;
            $totalPenalty = max(0, $lateFee + $cleaningFee + $damageFee);
            $depositHeld  = (int) $rental->deposit_paid;
            $depositCover = min($totalPenalty, $depositHeld);
            $cashDue      = max(0, $totalPenalty - $depositCover);
            $existingPaid = (int) $rental->penalty_paid;
            $newPaid      = min($existingPaid, $cashDue);
            $statusLabel  = $totalPenalty > 0 ? (($cashDue - $newPaid) > 0 ? 'DUE' : 'PAID') : 'NONE';
            $rentalStatus = $statusLabel === 'DUE' ? 'AWAITING_PENALTY' : 'RETURN_REQUESTED';

            DB::transaction(function () use ($rental, $lateFee, $cleaningFee, $damageFee, $totalPenalty, $newPaid, $statusLabel, $rentalStatus, $note) {
                $rental->update([
                    'penalty_late_fee'      => $lateFee,
                    'penalty_cleaning_fee'  => $cleaningFee,
                    'penalty_damage_fee'    => $damageFee,
                    'penalty_total_due'     => $totalPenalty,
                    'penalty_paid'          => $newPaid,
                    'penalty_status'        => $statusLabel,
                    'penalty_notes'         => $note,
                    'status'                => $rentalStatus,
                ]);
            });

            return back()->with([
                'status' => 'Tagihan denda disimpan dan dikirim ke user.',
                'toast'  => 'Tagihan denda berhasil dihitung.',
            ]);
        }

        // Finalize confirmation
        $lateFee         = (int) ($rental->penalty_late_fee ?: $lateFeeInput);
        $cleaningFee     = (int) ($rental->penalty_cleaning_fee ?: $cleaningFee);
        $damageFee       = (int) ($rental->penalty_damage_fee ?: $damageFee);
        $totalPenalty    = max(0, $rental->penalty_total_due ?: ($lateFee + $cleaningFee + $damageFee));
        $depositHeld     = (int) $rental->deposit_paid;
        $depositCoverage = min($totalPenalty, $depositHeld);
        $cashDue         = max(0, $totalPenalty - $depositCoverage);
        $cashOutstanding = max(0, $cashDue - (int) $rental->penalty_paid);

        abort_if($cashOutstanding > 0, 422, 'Menunggu pembayaran denda oleh user.');

        $delayDays = $lateDaysInput;

        DB::transaction(function () use ($rental, $now, $lateFee, $cleaningFee, $damageFee, $totalPenalty, $depositCoverage, $cashDue, $note, $delayDays) {
            $rental->penalties()->delete();

            if ($lateFee > 0) {
                Penalty::create([
                    'rental_id' => $rental->id,
                    'kind'      => 'LATE',
                    'amount'    => $lateFee,
                    'reason'    => 'Terlambat',
                ]);
            }

            if ($cleaningFee > 0) {
                Penalty::create([
                    'rental_id' => $rental->id,
                    'kind'      => 'CLEANING',
                    'amount'    => $cleaningFee,
                    'reason'    => 'Cleaning fee',
                ]);
            }

            if ($damageFee > 0) {
                Penalty::create([
                    'rental_id' => $rental->id,
                    'kind'      => 'DAMAGE',
                    'amount'    => $damageFee,
                    'reason'    => 'Damage fee',
                ]);
            }

            if ($depositCoverage > 0) {
                Payment::create([
                    'rental_id' => $rental->id,
                    'type'      => 'PENALTY',
                    'amount'    => $depositCoverage,
                    'method'    => 'DEPOSIT',
                    'paid_at'   => now(),
                    'ref_code'  => 'DEPOSIT_DEDUCTION',
                ]);
            }

            $depositRefund = max(0, $rental->deposit_paid - $depositCoverage);

            $rental->returnRecord()->updateOrCreate(
                [],
                [
                    'admin_id'              => auth()->id(),
                    'return_checked_at'     => $now,
                    'cleaning_fee'          => $cleaningFee,
                    'damage_fee'            => $damageFee,
                    'late_fee'              => $lateFee,
                    'total_penalty'         => $totalPenalty,
                    'penalty_paid'          => (int) $rental->penalty_paid,
                    'deposit_used'          => $depositCoverage,
                    'deposit_refund'        => $depositRefund,
                    'delay_days'            => $delayDays,
                    'rent_fee_snapshot'     => $rental->rent_fee_paid,
                    'deposit_paid_snapshot' => $rental->deposit_paid,
                    'condition_note'        => $note,
                ]
            );

            $rental->unit()->update(['is_available' => true, 'is_sold' => false]);

            $rental->update([
                'status'               => 'RETURNED',
                'rental_end_actual'    => $now,
                'return_requested_at'  => null,
                'penalty_status'       => $totalPenalty > 0 ? 'PAID' : 'NONE',
                'penalty_total_due'    => $totalPenalty,
                'penalty_notes'        => $note,
            ]);
        });

        return redirect()->to(route('adminManage.dashboard').'#riwayat-sewa')->with([
            'status' => 'Pengembalian dikonfirmasi.',
            'toast'  => 'Pengembalian dikonfirmasi.',
        ]);
    }
}







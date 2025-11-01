@extends('layouts.app')
@section('title','Konfirmasi Pengembalian')
@section('content')
  <h1 class="text-2xl font-semibold mb-4">Konfirmasi Pengembalian</h1>
  @php
    $countdown = $countdown ?? ['isLate' => false, 'diffHuman' => null];
    $isLate = $countdown['isLate'];
    $lateBadge = 'badge-dark';
    $effectiveLateFee = $rental->penalty_late_fee ?: $lateFee;
    $effectiveCleaning = $rental->penalty_cleaning_fee ?? 0;
    $effectiveDamage = $rental->penalty_damage_fee ?? 0;
    $penaltyTotal = (int) ($rental->penalty_total_due ?: ($effectiveLateFee + $effectiveCleaning + $effectiveDamage));
    $depositPaid = (int) $rental->deposit_paid;
    $depositCover = min($penaltyTotal, $depositPaid);
    $cashDue = max(0, $penaltyTotal - $depositCover);
    $cashOutstanding = max(0, $cashDue - (int) $rental->penalty_paid);
    $hasInvoice = $penaltyTotal > 0 || $rental->penalty_status !== 'NONE';
  @endphp

  <div class="grid lg:grid-cols-2 gap-4 mb-6">
    <div class="card p-5 space-y-3">
      <div class="flex items-start justify-between">
        <div>
          <h2 class="font-semibold text-lg text-brand-text">{{ $rental->unit->name }}</h2>
          <p class="text-sm text-brand-text/70">{{ $rental->user->name }} &middot; Mulai {{ $rental->rental_start?->format('d M Y H:i') }}</p>
          <p class="text-sm text-brand-text/70">Jatuh Tempo: {{ $rental->rental_end_plan?->format('d M Y H:i') }}</p>
        </div>
        <span class="{{ $lateBadge }} text-xs">
          @if($isLate)
            Terlambat {{ $countdown['diffHuman'] ?? '0 detik' }}
          @else
            Sisa {{ $countdown['diffHuman'] ?? '0 detik' }}
          @endif
        </span>
      </div>
      <div class="text-xs text-brand-text/60">Deposit: Rp {{ number_format($rental->deposit_paid,0,',','.') }} &middot; Biaya sewa: Rp {{ number_format($rental->rent_fee_paid,0,',','.') }}</div>
      @if(($lateDays ?? 0) > 0)
        <div class="text-xs text-brand-text/60">Total keterlambatan: {{ $lateDays }} hari</div>
      @endif
      @if($rental->penalty_status === 'DUE')
        <div class="alert-dark text-xs">
          Menunggu pembayaran denda oleh user sebesar Rp {{ number_format($cashOutstanding,0,',','.') }}.
        </div>
      @elseif($rental->penalty_status === 'PAID' && $penaltyTotal > 0)
        <div class="alert-dark text-xs">
          Semua denda telah dilunasi oleh user. Lanjutkan konfirmasi untuk menutup transaksi.
        </div>
      @endif
    </div>

    <div class="card p-5 space-y-3">
      <h3 class="font-semibold text-brand-text">Ringkasan Tagihan</h3>
      <div class="text-sm text-brand-text/70 flex justify-between">
        <span>Denda keterlambatan</span>
        <span>Rp {{ number_format($effectiveLateFee,0,',','.') }}</span>
      </div>
      <div class="text-sm text-brand-text/70 flex justify-between">
        <span>Denda pencucian</span>
        <span>Rp {{ number_format($effectiveCleaning,0,',','.') }}</span>
      </div>
      <div class="text-sm text-brand-text/70 flex justify-between">
        <span>Denda kerusakan</span>
        <span>Rp {{ number_format($effectiveDamage,0,',','.') }}</span>
      </div>
      <div class="border-t border-brand-nav/40 pt-2 flex justify-between text-sm text-brand-text">
        <span>Total denda</span>
        <span>Rp {{ number_format($penaltyTotal,0,',','.') }}</span>
      </div>
      <div class="flex justify-between text-xs text-brand-text/60">
        <span>Potongan dari deposit</span>
        <span>Rp {{ number_format($depositCover,0,',','.') }}</span>
      </div>
      <div class="flex justify-between text-xs text-brand-text/60">
        <span>Pembayaran user</span>
        <span>Rp {{ number_format($rental->penalty_paid,0,',','.') }}</span>
      </div>
      <div class="flex justify-between text-sm font-semibold text-brand-text">
        <span>Sisa tagihan</span>
        <span>Rp {{ number_format($cashOutstanding,0,',','.') }}</span>
      </div>
    </div>
  </div>

  <form method="POST" action="{{ route('adminManage.returns.confirm',$rental) }}" class="card p-6 max-w-3xl space-y-4">
    @csrf
    <input type="hidden" name="condition_note" value="{{ old('condition_note', $rental->penalty_notes) }}">
    <div class="grid md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm mb-1">Denda Pencucian (Rp)</label>
        <input name="cleaning_fee" type="number" class="brand-input" placeholder="0" min="0" value="{{ old('cleaning_fee', $rental->penalty_cleaning_fee) }}">
      </div>
      <div>
        <label class="block text-sm mb-1">Denda Kerusakan (Rp)</label>
        <input name="damage_fee" type="number" class="brand-input" placeholder="0" min="0" value="{{ old('damage_fee', $rental->penalty_damage_fee) }}">
      </div>
    </div>
    <div>
      <label class="block text-sm mb-1">Catatan Kondisi</label>
      <textarea name="condition_note_display" class="brand-input" rows="3" placeholder="Catat keadaan unit saat kembali" oninput="document.querySelector('[name=condition_note]').value=this.value">{{ old('condition_note', $rental->penalty_notes) }}</textarea>
    </div>

    <div class="flex flex-wrap gap-3 items-center">
      <button class="btn btn-ghost" name="action" value="invoice">Simpan &amp; Kirim Tagihan</button>
      <button class="btn btn-ghost" name="action" value="finalize" @disabled($cashOutstanding > 0)
        @if($cashOutstanding > 0)
          title="Menunggu pembayaran denda oleh user"
        @endif
      >Konfirmasi Pengembalian</button>
      @if($cashOutstanding > 0)
        <span class="text-xs text-brand-text/60">Tombol konfirmasi aktif setelah user melunasi denda.</span>
      @endif
    </div>
  </form>
@endsection




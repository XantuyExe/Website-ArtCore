@extends('layouts.app')
@section('title','Riwayat Sewa & Pembelian')
@section('content')
  <h1 class="text-2xl font-semibold mb-4">Riwayat Sewa & Pembelian</h1>
  @forelse($rentals as $rental)
    @php($record = $rental->returnRecord)
    @php($isReturned = $rental->status === 'RETURNED')
    @php($isPurchased = $rental->status === 'PURCHASED')
    @php($late = $record?->delay_days > 0)
    <div class="card p-5 mb-4 space-y-3">
      <div class="flex flex-wrap justify-between gap-3">
        <div>
          <div class="text-lg font-semibold text-brand-text">{{ $rental->unit->name }}</div>
          <div class="text-xs text-brand-text/60">{{ $rental->unit->category->name ?? '-' }} &middot; {{ $rental->unit->vintage }}</div>
          <div class="mt-2 text-sm text-brand-text/70">Mulai: {{ $rental->rental_start?->format('d M Y H:i') }}</div>
          <div class="text-sm text-brand-text/70">Jatuh Tempo: {{ $rental->rental_end_plan?->format('d M Y H:i') }}</div>
          @if($isReturned)
            <div class="text-sm text-brand-text/70">Dikembalikan: {{ $rental->rental_end_actual?->format('d M Y H:i') }}</div>
          @endif
        </div>
        <div class="flex flex-col items-end gap-2">
          <span class="badge-dark text-xs uppercase">
            {{ $isReturned ? ($late ? 'Kembali terlambat' : 'Kembali tepat waktu') : $rental->status }}
          </span>
          @if($isPurchased && $rental->purchase)
            <span class="text-xs text-brand-text/60">Dibeli pada {{ $rental->purchase->decided_at?->format('d M Y H:i') }}</span>
          @endif
        </div>
      </div>

      <div class="grid md:grid-cols-2 gap-4">
        <div class="rounded-lg border border-brand-nav/40 p-4 space-y-2 text-sm text-brand-text/80">
          <div class="font-semibold text-brand-text">Ringkasan</div>
          <div class="flex justify-between"><span>Biaya sewa</span><span>Rp {{ number_format($rental->rent_fee_paid,0,',','.') }}</span></div>
          <div class="flex justify-between"><span>Deposit dibayar</span><span>Rp {{ number_format($rental->deposit_paid,0,',','.') }}</span></div>
          @if($isPurchased && $rental->purchase)
            <div class="flex justify-between"><span>Harga beli (TPO)</span><span>Rp {{ number_format($rental->purchase->final_price,0,',','.') }}</span></div>
          @endif
        </div>

        @if($isReturned && $record)
          @php($totalPenalty = $record->total_penalty)
          @php($depositUsed = $record->deposit_used)
          @php($cashPaid = $record->penalty_paid)
          <div class="rounded-lg border border-brand-nav/40 p-4 space-y-2 text-sm text-brand-text/80">
            <div class="font-semibold text-brand-text">Resi Pengembalian</div>
            <div class="flex justify-between"><span>Denda keterlambatan</span><span>Rp {{ number_format($record->late_fee,0,',','.') }}</span></div>
            <div class="flex justify-between"><span>Denda pencucian</span><span>Rp {{ number_format($record->cleaning_fee,0,',','.') }}</span></div>
            <div class="flex justify-between"><span>Denda kerusakan</span><span>Rp {{ number_format($record->damage_fee,0,',','.') }}</span></div>
            <div class="flex justify-between font-semibold text-brand-text"><span>Total denda</span><span>Rp {{ number_format($totalPenalty,0,',','.') }}</span></div>
            <div class="flex justify-between text-xs text-brand-text/60"><span>Potong deposit</span><span>Rp {{ number_format($depositUsed,0,',','.') }}</span></div>
            <div class="flex justify-between text-xs text-brand-text/60"><span>Pembayaran cash</span><span>Rp {{ number_format($cashPaid,0,',','.') }}</span></div>
            <div class="flex justify-between text-xs text-brand-text/60"><span>Deposit dikembalikan</span><span>Rp {{ number_format($record->deposit_refund,0,',','.') }}</span></div>
            <div class="text-xs text-brand-text/60">Catatan: {{ $record->condition_note ?? '-' }}</div>
          </div>
        @endif
      </div>
    </div>
  @empty
    <div class="card p-6 text-brand-text/60">Belum ada riwayat sewa atau pembelian.</div>
  @endforelse

  <div class="mt-4">
    {{ $rentals->links() }}
  </div>
@endsection

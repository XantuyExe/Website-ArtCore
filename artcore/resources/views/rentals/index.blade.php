@extends('layouts.app')
@section('title','Unit Disewa')
@section('content')
  <h1 class="text-2xl font-semibold mb-4">Unit Disewa</h1>
  @php
    $reference = $now ?? now();
  @endphp
  @forelse($rentals as $r)
    @php
      $countdown = $r->countdownInfo($reference);
      $displayDiff = $countdown['diffHuman'] ?? '0 detik';
      $penaltyTotal = (int) $r->penalty_total_due;
      $depositPaid = (int) $r->deposit_paid;
      $depositCover = min($penaltyTotal, $depositPaid);
      $cashDue = max(0, $penaltyTotal - $depositCover);
      $outstanding = max(0, $cashDue - (int) $r->penalty_paid);
      $isAwaitingPenalty = $r->penalty_status === 'DUE' && $outstanding > 0;
      $lateTone = '';
      $onTimeTone = '';
      $penaltyTone = '';
      $baseBadgeClass = 'badge-dark text-xs transition-colors duration-200';
      $badgeClass = trim($baseBadgeClass.' '.($countdown['isLate'] ? $lateTone : $onTimeTone));
      $badgeMode = 'timer';
      if ($isAwaitingPenalty) {
          $badgeClass = $baseBadgeClass.' '.$penaltyTone;
          $badgeMode = 'penalty';
      } elseif ($r->penalty_status === 'PAID' && $penaltyTotal > 0) {
          $badgeClass = $baseBadgeClass.' '.$onTimeTone;
          $badgeMode = 'static';
      }
      $statusLabel = 'Sisa '.$displayDiff;
      if ($isAwaitingPenalty) {
          $statusLabel = 'Menunggu pembayaran denda';
      } elseif ($r->penalty_status === 'PAID' && $penaltyTotal > 0) {
          $statusLabel = 'Denda lunas';
      } elseif ($countdown['isLate']) {
          $statusLabel = 'Terlambat '.$displayDiff;
      }
      $targetIso = $r->rental_end_plan?->toIso8601String();
    @endphp
    <div class="card p-5 mb-4 space-y-3">
      <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
          <div class="font-semibold text-lg text-brand-text">{{ $r->unit->name }}</div>
          <div class="text-xs text-brand-ink/60">{{ $r->unit->category->name }} &middot; {{ $r->unit->vintage }}</div>
          <div class="text-sm mt-2">Mulai Sewa: {{ $r->rental_start?->format('d M Y H:i') }}</div>
          <div class="text-sm">Jatuh Tempo: <b>{{ $r->rental_end_plan?->format('d M Y H:i') }}</b></div>
          <div class="text-xs text-brand-accent mt-1">Denda 10% dari biaya sewa untuk setiap hari keterlambatan.</div>
          @if($countdown['isLate'])
            <div class="mt-2 text-xs alert-dark">
              Terlambat {{ $displayDiff }}. Mohon segera selesaikan pengembalian agar denda tidak semakin besar.
            </div>
          @endif
          @if($r->status === 'AWAITING_PENALTY')
            <div class="mt-2 text-xs alert-dark">
              Menunggu konfirmasi admin setelah pembayaran denda.
            </div>
          @elseif($r->status === 'RETURN_REQUESTED')
            <div class="mt-2 text-xs text-brand-ink/70">
              Menunggu konfirmasi admin sejak {{ $r->return_requested_at?->format('d M Y H:i') }}.
            </div>
          @endif
        </div>
        <div class="flex flex-col items-end gap-2">
          <span class="{{ $badgeClass }}"
                data-countdown-badge
                data-countdown-target="{{ $targetIso }}"
                data-countdown-ontime-class="{{ trim($baseBadgeClass.' '.$onTimeTone) }}"
                data-countdown-late-class="{{ trim($baseBadgeClass.' '.$lateTone) }}"
                data-countdown-penalty-class="{{ trim($baseBadgeClass.' '.$penaltyTone) }}"
                data-countdown-mode="{{ $badgeMode }}"
                data-countdown-ontime-label="Sisa"
                data-countdown-late-label="Terlambat">{{ $statusLabel }}</span>
          @if($r->status === 'ACTIVE' && $reference->diffInDays($r->rental_start) <= config('artcore.tpo_window_days'))
            <form method="POST" action="{{ route('rentals.purchase', $r) }}">@csrf
              <button class="btn btn-primary">Beli Unit (TPO)</button>
            </form>
          @endif
          @if($r->status === 'ACTIVE')
            <form method="POST" action="{{ route('rentals.return-request', $r) }}">@csrf
              <button class="btn btn-ghost">Ajukan Pengembalian</button>
            </form>
          @endif
        </div>
      </div>

      @if($penaltyTotal > 0 || $isAwaitingPenalty)
        <div class="mt-3 border border-brand-nav/40 rounded-lg overflow-hidden">
          <div class="bg-brand-nav/40 px-4 py-2 text-sm font-semibold text-brand-text">Rincian Denda</div>
          <div class="p-4 space-y-1 text-sm text-brand-text/80">
            <div class="flex justify-between"><span>Denda keterlambatan</span><span>Rp {{ number_format($r->penalty_late_fee,0,',','.') }}</span></div>
            <div class="flex justify-between"><span>Denda pencucian</span><span>Rp {{ number_format($r->penalty_cleaning_fee,0,',','.') }}</span></div>
            <div class="flex justify-between"><span>Denda kerusakan</span><span>Rp {{ number_format($r->penalty_damage_fee,0,',','.') }}</span></div>
            @if(($countdown['lateDays'] ?? 0) > 0)
              <div class="flex justify-between text-xs text-brand-text/60"><span>Keterlambatan</span><span>{{ $countdown['lateDays'] }} hari</span></div>
            @endif
            <div class="flex justify-between font-semibold pt-2 border-t border-brand-nav/40"><span>Total</span><span>Rp {{ number_format($penaltyTotal,0,',','.') }}</span></div>
            <div class="flex justify-between text-xs text-brand-text/60"><span>Potong deposit</span><span>Rp {{ number_format($depositCover,0,',','.') }}</span></div>
            <div class="flex justify-between text-xs text-brand-text/60"><span>Pembayaran user</span><span>Rp {{ number_format($r->penalty_paid,0,',','.') }}</span></div>
            <div class="flex justify-between font-semibold text-sm"><span>Sisa bayar</span><span>Rp {{ number_format($outstanding,0,',','.') }}</span></div>
          </div>
          @if($isAwaitingPenalty)
            <div class="alert-dark text-xs flex justify-between items-center">
              <span>Silakan selesaikan pembayaran denda sebelum admin memproses pengembalian.</span>
              <form method="POST" action="{{ route('rentals.penalty-pay', $r) }}" class="inline">@csrf
                <button class="btn btn-primary btn-lg">Bayar Denda</button>
              </form>
            </div>
          @elseif($penaltyTotal > 0)
            <div class="alert-dark text-xs">Denda telah dilunasi. Menunggu konfirmasi admin.</div>
          @endif
        </div>
      @endif
    </div>
  @empty
    <div class="card p-6 text-brand-ink/60">Belum ada sewa aktif.</div>
  @endforelse

  @once
    <script>
      document.addEventListener('DOMContentLoaded', () => {
        const formatCountdown = (totalSeconds) => {
          const days = Math.floor(totalSeconds / 86400);
          const hours = Math.floor((totalSeconds % 86400) / 3600);
          const minutes = Math.floor((totalSeconds % 3600) / 60);
          const seconds = totalSeconds % 60;
          const parts = [];
          if (days) parts.push(`${days} hari`);
          if (hours) parts.push(`${hours} jam`);
          if (!days && minutes) parts.push(`${minutes} menit`);
          if (!parts.length) parts.push(`${seconds} detik`);
          return parts.slice(0, 2).join(' ');
        };

        document.querySelectorAll('[data-countdown-badge]').forEach(badge => {
          const mode = badge.dataset.countdownMode || 'timer';
          if (mode !== 'timer') {
            if (mode === 'penalty') {
              const penaltyClass = badge.dataset.countdownPenaltyClass;
              if (penaltyClass) {
                badge.className = penaltyClass;
              }
            }
            return;
          }

          const targetString = badge.dataset.countdownTarget;
          if (!targetString) {
            return;
          }

          const target = new Date(targetString);
          if (Number.isNaN(target.getTime())) {
            return;
          }

          const onTimeClass = badge.dataset.countdownOntimeClass || badge.className;
          const lateClass = badge.dataset.countdownLateClass || badge.className;
          const onTimeLabel = badge.dataset.countdownOntimeLabel || 'Sisa';
          const lateLabel = badge.dataset.countdownLateLabel || 'Terlambat';

          const update = () => {
            const diff = target.getTime() - Date.now();
            const isLate = diff < 0;
            const totalSeconds = Math.max(0, Math.round(Math.abs(diff) / 1000));
            const label = isLate ? lateLabel : onTimeLabel;
            badge.textContent = `${label} ${formatCountdown(totalSeconds)}`;
            badge.className = isLate ? lateClass : onTimeClass;
          };

          update();
          setInterval(update, 30000);
        });
      });
    </script>
  @endonce
@endsection


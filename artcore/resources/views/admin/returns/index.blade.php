@extends('layouts.app')
@section('title','Pengembalian')
@section('content')
  <h1 class="text-2xl font-semibold mb-4">Permintaan Pengembalian</h1>
  <div class="card overflow-hidden">
    <table class="w-full text-sm">
      <thead class="bg-brand-nav/40">
        <tr>
          <th class="p-2 text-left">User</th>
          <th class="p-2 text-left">Unit</th>
          <th class="p-2">Diajukan</th>
          <th class="p-2">Jatuh Tempo</th>
          <th class="p-2">Status</th>
          <th class="p-2">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($requests as $r)
          @php($countdown = $r->countdownInfo())
          @php($badgeClass = 'badge-dark')
          <tr class="border-t">
            <td class="p-2">{{ $r->user->name }}</td>
            <td class="p-2">{{ $r->unit->name }}</td>
            <td class="p-2 text-center">{{ $r->return_requested_at?->format('d M Y H:i') }}</td>
            <td class="p-2 text-center">{{ $r->rental_end_plan?->format('d M Y H:i') }}</td>
            <td class="p-2 text-center">
              <span class="{{ $badgeClass }} text-xs">
                @if($r->penalty_status === 'DUE')
                  Menunggu pembayaran denda
                @elseif($r->penalty_status === 'PAID' && $r->penalty_total_due > 0)
                  Denda lunas menunggu konfirmasi
                @elseif($countdown['isLate'])
                  Terlambat {{ $countdown['diffHuman'] ?? '0 detik' }}
                @else
                  Sisa {{ $countdown['diffHuman'] ?? '0 detik' }}
                @endif
              </span>
            </td>
            <td class="p-2 text-center">
              <a href="{{ route('adminManage.returns.form', $r) }}" class="underline">Konfirmasi</a>
            </td>
          </tr>
        @empty
          <tr><td class="p-3 text-center text-brand-text/60" colspan="6">Tidak ada permintaan pengembalian.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="mt-3">{{ $requests->links() }}</div>
@endsection

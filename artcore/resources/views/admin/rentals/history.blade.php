@extends('layouts.app')
@section('title','Riwayat Sewa')
@section('content')
  <h1 class="text-2xl font-semibold mb-4">Riwayat Sewa</h1>

  <div class="card p-4 mb-4">
    <form method="GET" class="grid md:grid-cols-5 gap-3">
      <input type="text" name="user" value="{{ request('user') }}" placeholder="Cari user" class="brand-input">
      <input type="text" name="unit" value="{{ request('unit') }}" placeholder="Cari unit" class="brand-input">
      <select name="status" class="brand-input">
        <option value="">Semua Status</option>
        @foreach(['ACTIVE','RETURN_REQUESTED','AWAITING_PENALTY','RETURNED','PURCHASED','CANCELLED'] as $status)
          <option value="{{ $status }}" @selected(request('status')===$status)>{{ $status }}</option>
        @endforeach
      </select>
      <input type="date" name="from" value="{{ request('from') }}" class="brand-input" placeholder="Mulai">
      <input type="date" name="to" value="{{ request('to') }}" class="brand-input" placeholder="Selesai">
      <button class="btn btn-ghost md:col-start-5">Filter</button>
    </form>
  </div>

  <div class="card overflow-hidden">
    <table class="w-full text-sm">
      <thead class="bg-brand-nav/40">
        <tr>
          <th class="p-2 text-left">User</th>
          <th class="p-2 text-left">Unit</th>
          <th class="p-2">Status</th>
          <th class="p-2">Mulai</th>
          <th class="p-2">Jatuh Tempo</th>
          <th class="p-2">Selesai</th>
          <th class="p-2">Keterangan</th>
          <th class="p-2">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($rentals as $r)
          @php($record = $r->returnRecord)
          @php($isLate = $record?->delay_days > 0)
          <tr class="border-t">
            <td class="p-2">{{ $r->user->name }}</td>
            <td class="p-2">{{ $r->unit->name }}</td>
            <td class="p-2 text-center">{{ $r->status }}</td>
            <td class="p-2 text-center">{{ $r->rental_start?->format('d M Y') }}</td>
            <td class="p-2 text-center">{{ $r->rental_end_plan?->format('d M Y') }}</td>
            <td class="p-2 text-center">{{ $r->rental_end_actual?->format('d M Y') ?? '-' }}</td>
            <td class="p-2 text-center">
              @if($r->status === 'RETURNED')
                <span class="badge-dark text-xs">
                  {{ $isLate ? 'Kembali terlambat' : 'Tepat waktu' }}
                </span>
              @else
                <span class="text-xs text-brand-text/60">—</span>
              @endif
            </td>
            <td class="p-2 text-center"><a class="underline" href="{{ route('adminManage.rentals.show', $r) }}">Detail</a></td>
          </tr>
        @empty
          <tr><td class="p-3 text-center text-brand-text/60" colspan="8">Tidak ada data.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="mt-3">{{ $rentals->links() }}</div>
@endsection


@if($units->count())
  <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
    @foreach($units as $u)
      @php($canAddToCart = auth()->check() && !auth()->user()->is_admin)
      @if($canAddToCart)
        <a href="{{ route('cart') }}"
           class="card unit-card p-3 hover:shadow-lg transition text-sm js-add-to-cart"
           data-unit-id="{{ $u->id }}"
           data-unit-name="{{ $u->name }}"
           data-cart-url="{{ route('cart') }}"
        >
      @else
        <div class="card unit-card p-3 hover:shadow-lg transition text-sm opacity-85 cursor-default select-none">
      @endif
        <div class="aspect-[3/2] w-full rounded-lg bg-brand-nav/40 flex items-center justify-center mb-2 overflow-hidden">
          @if(!empty($u->images))
            <img src="{{ asset('storage/'.$u->images[0]) }}" alt="{{ $u->name }}" class="w-full h-full object-cover">
          @else
            <span class="text-xs text-brand-text/60">Frame Gambar</span>
          @endif
        </div>
        <div class="flex items-start justify-between gap-3">
          <div class="pr-2">
            <div class="font-semibold text-brand-text text-sm truncate">{{ $u->name }}</div>
            <div class="text-[11px] text-brand-text/70 mt-0.5">{{ $u->category->name ?? '-' }} &middot; {{ $u->vintage }}</div>
          </div>
          <div class="text-right text-xs font-semibold text-brand-text whitespace-nowrap">Rp {{ number_format($u->rent_price_5d,0,',','.') }}</div>
        </div>
        <div class="mt-2 flex flex-wrap gap-2">
          @if(in_array($u->vintage,['60s','70s']))
            <span class="unit-pill">Deposit 30%</span>
          @endif
          @if(($u->category->name ?? '')==='SCULPTURE_3D')
            <span class="unit-pill">2 slot</span>
          @endif
          @if($u->is_sold)
            <span class="unit-pill">SOLD</span>
          @elseif($u->is_available)
            <span class="unit-pill">Tersedia &#10003;</span>
          @else
            <span class="unit-pill">Tidak Tersedia</span>
          @endif
        </div>
      @if($canAddToCart)
        </a>
      @else
        </div>
      @endif
    @endforeach
  </div>
  @if(method_exists($units,'links')) <div class="mt-6">{{ $units->links() }}</div> @endif
@else
  @include('units.empty')
@endif

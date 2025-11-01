@php($user = auth()->user())
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title','ArtCore')</title>
  <script>
    window.__toastQueue = window.__toastQueue || [];
    window.showToast = window.showToast || function(message, opts) {
      window.__toastQueue.push({ message, opts });
    };
  </script>
  @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col">
  <!-- Navbar -->
  <header data-main-header class="site-header sticky top-0 z-40 border-b border-brand-card bg-brand-nav/90 backdrop-blur-sm transition-colors duration-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
      <a href="{{ route('home') }}" class="text-lg font-bold tracking-wide text-brand-text">
        <span class="inline-block w-3 h-3 rounded-full bg-brand-accent mr-2"></span>ArtCore
      </a>

      <div class="flex items-center gap-4">
        @auth
          @php(
            $adminBase = Route::has('adminManage.dashboard') ? route('adminManage.dashboard') : url('/admin-manage')
          )
          @php($isAdminArea = request()->is('admin-manage*'))
          <nav class="main-nav" data-nav>
            <span class="main-nav__highlight" data-nav-highlight></span>
            @if($isAdminArea)
              {{-- Sedang di halaman admin --}}
              <a data-nav-link href="{{ $adminBase }}#dashboard-umum"
                 class="main-nav__link is-active">Tinjauan</a>
              <a data-nav-link href="{{ $adminBase }}#manajemen-katalog"
                 class="main-nav__link">Katalog &amp; Unit</a>
              <a data-nav-link href="{{ $adminBase }}#manajemen-anggota"
                 class="main-nav__link">Anggota</a>
              <a data-nav-link href="{{ $adminBase }}#status-unit"
                 class="main-nav__link">Daftar Unit</a>
              <a data-nav-link href="{{ $adminBase }}#konfirmasi-pengembalian"
                 class="main-nav__link">Pengembalian</a>
              <a data-nav-link href="{{ $adminBase }}#riwayat-sewa"
                 class="main-nav__link">Riwayat Sewa</a>
            @else
              {{-- Bukan halaman admin --}}
              @if($user->is_admin)
                <a data-nav-link href="{{ $adminBase }}"
                   class="main-nav__link {{ request()->is('admin-manage*') ? 'is-active' : '' }}">AdminManage</a>
              @endif
              @unless($user->is_admin)
                <a data-nav-link href="{{ route('cart') }}"
                   class="main-nav__link {{ request()->routeIs('cart') ? 'is-active' : '' }}">Keranjang</a>
                <a data-nav-link href="{{ route('purchases') }}"
                   class="main-nav__link {{ request()->routeIs('purchases') ? 'is-active' : '' }}">Pembelian</a>
                <a data-nav-link href="{{ route('rentals.index') }}"
                   class="main-nav__link {{ request()->routeIs('rentals.index') ? 'is-active' : '' }}">Unit Disewa</a>
                <a data-nav-link href="{{ route('rentals.history') }}"
                   class="main-nav__link {{ request()->routeIs('rentals.history') ? 'is-active' : '' }}">Riwayat</a>
              @endunless
            @endif
            <a data-nav-link href="{{ route('profile.edit') }}"
               class="main-nav__link {{ request()->routeIs('profile.*') ? 'is-active' : '' }}">Profil</a>
          </nav>
        @endauth

        @guest
          <a class="btn btn-dark" href="{{ route('login') }}">Login</a>
          <a class="btn btn-ghost" href="{{ route('register') }}">Daftar</a>
        @endguest

        @auth
          <form method="POST" action="{{ route('logout') }}" class="inline">
            @csrf
            <button class="btn btn-dark">Logout</button>
          </form>
        @endauth
      </div>
    </div>
  </header>

  @if(session('status'))
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
      <div class="card px-4 py-2">{{ session('status') }}</div>
    </div>
  @endif

  <main class="flex-1 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      @yield('content')
    </div>
  </main>

  <footer class="mt-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="card bg-brand-card p-6 flex items-center justify-between">
        <p class="text-sm text-brand-text/80">&copy; {{ date('Y') }} ArtCore | Rafi Surya Pratama | Telkon University | Sertifikasi BNSP</p>
        <div class="flex gap-2">
          <span class="w-3 h-3 rounded-full bg-brand-accent"></span>
          <span class="w-3 h-3 rounded-full bg-brand-card"></span>
        </div>
      </div>
    </div>
  </footer>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const header = document.querySelector('[data-main-header]');
      if (!header) return;
      const handleScroll = () => {
        header.classList.toggle('is-scrolled', window.scrollY > 10);
      };
      handleScroll();
      window.addEventListener('scroll', handleScroll, { passive: true });
    });
  </script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      document.querySelectorAll('form[data-scroll-anchor]').forEach(form => {
        form.addEventListener('submit', () => {
          const raw = form.getAttribute('data-scroll-anchor');
          if (!raw || form.dataset.anchorAppended === 'true') return;
          const anchor = raw.startsWith('#') ? raw : `#${raw}`;
          form.action = `${form.action.split('#')[0]}${anchor}`;
          form.dataset.anchorAppended = 'true';
        }, { once: true });
      });
    });
  </script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      document.querySelectorAll('[data-nav]').forEach(nav => {
        const highlight = nav.querySelector('[data-nav-highlight]');
        const links = Array.from(nav.querySelectorAll('[data-nav-link]'));
        if (!highlight || !links.length) {
          return;
        }

        let activeLink = null;

        const updateHighlight = (link) => {
          if (!link) {
            nav.classList.remove('main-nav--active');
            highlight.style.opacity = '0';
            links.forEach(l => l.classList.remove('is-highlighted'));
            return;
          }
          const navRect = nav.getBoundingClientRect();
          const rect = link.getBoundingClientRect();
          const translateX = rect.left - navRect.left;
          const translateY = rect.top - navRect.top;
          highlight.style.width = `${rect.width}px`;
          highlight.style.height = `${rect.height}px`;
          highlight.style.transform = `translate3d(${translateX}px, ${translateY}px, 0)`;
          nav.classList.add('main-nav--active');
          links.forEach(l => l.classList.remove('is-highlighted'));
          link.classList.add('is-highlighted');
        };

        const applyActive = (link) => {
          if (!link) {
            return;
          }
          activeLink = link;
          links.forEach(l => l.classList.toggle('is-active', l === link));
          updateHighlight(link);
        };

        const ensureActiveFromHash = () => {
          const { hash } = window.location;
          if (!hash) return false;
          const match = links.find(link => link.hash === hash);
          if (match) {
            applyActive(match);
            return true;
          }
          return false;
        };

        const findInitialActive = () => {
          if (ensureActiveFromHash()) {
            return;
          }
          const preset = links.find(link => link.classList.contains('is-active'));
          if (preset) {
            applyActive(preset);
          } else {
            applyActive(links[0]);
          }
        };

        findInitialActive();
        updateHighlight(activeLink);

        let scrollSpyActive = true;
        let scrollTimeout = null;
        const disableScrollSpyTemporarily = () => {
          scrollSpyActive = false;
          if (scrollTimeout) {
            clearTimeout(scrollTimeout);
          }
          scrollTimeout = setTimeout(() => {
            scrollSpyActive = true;
          }, 900);
        };

        links.forEach(link => {
          link.addEventListener('focus', () => updateHighlight(link));
          link.addEventListener('blur', () => updateHighlight(activeLink));
          link.addEventListener('click', () => {
            disableScrollSpyTemporarily();
            applyActive(link);
          });
        });

        nav.addEventListener('mouseleave', () => updateHighlight(activeLink));

        const anchorSections = links
          .filter(link => link.hash && link.hash.startsWith('#'))
          .map(link => {
            const id = link.hash.slice(1);
            const section = document.getElementById(id);
            return section ? { link, section } : null;
          })
          .filter(Boolean);

        if (anchorSections.length) {
          const evaluateScrollTarget = () => {
            if (!scrollSpyActive) {
              return;
            }
            const viewportCenter = window.innerHeight / 2;
            let closest = null;
            let closestDistance = Number.POSITIVE_INFINITY;

            anchorSections.forEach(({ section, link }) => {
              const rect = section.getBoundingClientRect();
              const sectionCenter = rect.top + rect.height / 2;
              const distance = Math.abs(sectionCenter - viewportCenter);
              if (distance < closestDistance) {
                closestDistance = distance;
                closest = link;
              }
            });

            if (closest) {
              applyActive(closest);
            }
          };

          let ticking = false;
          const requestEvaluate = () => {
            if (ticking) return;
            ticking = true;
            requestAnimationFrame(() => {
              evaluateScrollTarget();
              ticking = false;
            });
          };
          window.addEventListener('scroll', requestEvaluate, { passive: true });
          evaluateScrollTarget();
        }

        window.addEventListener('hashchange', () => {
          if (!ensureActiveFromHash() && activeLink) {
            updateHighlight(activeLink);
          }
        });

        window.addEventListener('resize', () => {
          if (activeLink) {
            applyActive(activeLink);
          }
        });

        window.addEventListener('load', () => {
          if (activeLink) {
            applyActive(activeLink);
          }
        });
      });
    });
  </script>

  <div id="toast-container" class="fixed bottom-4 right-4 space-y-3 z-50 pointer-events-none"></div>

  <script>
    const showToastImpl = function(message, opts = {}) {
      if (!message) return;
      const container = document.getElementById('toast-container');
      if (!container) return;
      const toast = document.createElement('div');
      toast.className = 'toast-notice';
      toast.textContent = message;
      container.appendChild(toast);
      requestAnimationFrame(() => toast.classList.add('is-visible'));
      const duration = opts.duration ?? 5000;
      setTimeout(() => {
        toast.classList.remove('is-visible');
        setTimeout(() => toast.remove(), 220);
      }, duration);
    };
    window.showToast = showToastImpl;
    if (Array.isArray(window.__toastQueue)) {
      window.__toastQueue.forEach(item => showToastImpl(item.message, item.opts || {}));
      window.__toastQueue = [];
    }

    @if(session('toast'))
      document.addEventListener('DOMContentLoaded', () => {
        window.showToast(@json(session('toast')));
      });
    @endif
  </script>
  @auth
  @unless(auth()->user()->is_admin)
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
      const cartUrl = @json(route('cart'));
      const loginUrl = @json(route('login'));

      const bindAddToCart = (link) => {
        if (!link || link.dataset.cartBound) return;
        link.dataset.cartBound = '1';
        link.addEventListener('click', event => {
          const unitId = link.dataset.unitId;
          if (!unitId || !csrfToken) {
            return;
          }
          event.preventDefault();
          const redirectAfterAdd = sessionStorage.getItem('artcore.cart.redirect') === 'ready';

          fetch(@json(route('cart.add')), {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrfToken,
              'Accept': 'application/json',
            },
            body: JSON.stringify({ unit_id: unitId })
          })
          .then(async response => {
            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
              if (response.status === 401) {
                window.location.assign(loginUrl);
                return;
              }
              if (data.redirect) {
                window.location.assign(link.dataset.cartUrl || cartUrl);
                return;
              }
              window.showToast(data.message || 'Gagal menambahkan unit ke keranjang.', { duration: 6000 });
              return;
            }
            sessionStorage.setItem('artcore.cart.redirect', 'ready');
            window.showToast(data.message || 'Unit masuk ke keranjang.');
            if (redirectAfterAdd) {
              window.location.assign(link.dataset.cartUrl || cartUrl);
            }
          })
          .catch(() => {
            window.location.assign(link.dataset.cartUrl || cartUrl);
          });
        });
      };

      document.querySelectorAll('.js-add-to-cart').forEach(bindAddToCart);

      document.addEventListener('artcore:refresh-add-to-cart', () => {
        document.querySelectorAll('.js-add-to-cart').forEach(bindAddToCart);
      });
    });
  </script>
  @endunless
  @endauth
</body>
</html>

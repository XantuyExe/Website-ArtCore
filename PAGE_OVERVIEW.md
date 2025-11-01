# ArtCore - Page Overview & File Map

Ringkasan berikut memetakan halaman, controller, dan view aplikasi ArtCore setelah pembaruan UI/UX, navigasi dinamis, dan alur denda (Oktober 2025).

---

## 1. Layout & Asset Global
- **Layout utama**: `resources/views/layouts/app.blade.php` — navbar adaptif, sticky header glossy, highlight bergerak, toast container.
- **Navigasi JS**: `resources/js/app.js` — smooth scroll, scroll spy yang aktif hanya saat scroll, binding tombol add-to-cart.
- **Styling**: `resources/css/app.css` — palet hitam/cream, komponen tombol outline/dark, kartu unit dengan badge hitam, scrollbar carousel transparan.
- **Konfigurasi tema**: `tailwind.config.js` mengatur warna brand (`brand-nav`, `brand-card`, `brand-accent`).

## 2. Autentikasi & Proteksi Route
- **Routes**: `routes/auth.php` (Laravel Breeze default).
- **Controllers**: `app/Http/Controllers/Auth/*`.
- **Kebijakan**: `routes/web.php` membungkus landing dan seluruh fitur dalam middleware `auth`; guest selalu diarahkan ke login.

## 3. Landing / Home
- **Route**: GET `/` (`home`).
- **Controller**: `App\Http\Controllers\Web\HomeController@index`.
- **View**: `resources/views/home.blade.php`.
- **Isi**: hero, carousel “Sorotan Terbaru” (dengan tombol scroll tanpa background putih), bagian kategori, grid unit menggunakan partial `_grid`.

## 4. Katalog Lengkap
- **Route**: GET `/units` (`units.index`).
- **Controller**: `UnitController@index`.
- **View**: `resources/views/units/index.blade.php`.
- **Fitur**: filter nama/kode, kategori, vintage (select bernuansa hitam); tombol filter dengan hover/active hitam; pagination.

## 5. Detail Unit
- **Route**: GET `/units/{unit}` (`units.show`).
- **Controller**: `UnitController@show`.
- **View**: `resources/views/units/show.blade.php`.
- **Konten**: foto utama, info harga jual & sewa, badge status hitam (Deposit, Slot, Tersedia (ikon cek) / Sold Out), tombol sewa (auth only).

## 6. Keranjang
- **Routes**: GET `/cart`, POST `/cart/add`, DELETE `/cart/{unit}`.
- **Controller**: `RentalController@cart`, `@addToCart`, `@removeFromCart`.
- **Views**: `resources/views/user/cart.blade.php` + `resources/views/user/cart-empty.blade.php`.
- **Validasi**: maksimum 2 slot (sculpture = 2 slot), otomatis menghitung deposit vintage 60s/70s.

## 7. Checkout & Penyewaan
- **Route**: POST `/rentals` (`rentals.store`).
- **Controller**: `RentalController@store`.
- **Model terkait**: `App\Models\Rental`, `App\Services\PricingService`.
- **Efek**: menyimpan `rental_start`, `rental_end_plan`, deposit 30% jika perlu, unit `is_available = false`.

## 8. Unit Disewa (Active Rentals)
- **Route**: GET `/rentals` (`rentals.index`).
- **Controller**: `RentalController@index`, `@purchase`, `@requestReturn`, `@payPenalty`.
- **View**: `resources/views/rentals/index.blade.php`.
- **Fitur**: countdown dengan warna adaptif, tombol Trial-to-Own (aktif 5 hari), Ajukan Pengembalian, tombol besar Bayar Denda ketika status `AWAITING_PENALTY`, ringkasan penalti & deposit.

## 9. Riwayat & Pembelian User
- **Riwayat Sewa**: GET `/rentals/history` (`rentals.history`), view `resources/views/user/rentals-history.blade.php`.
  - Menampilkan resi lengkap (deposit, denda, status Tepat Waktu/Terlambat, delay_days).
- **Pembelian**: GET `/purchases` (`purchases`), view `resources/views/user/purchases.blade.php`.
  - Daftar unit Trial-to-Own sukses atau pembelian langsung.

## 10. Profil User
- **Routes**: GET `/profile`, PATCH `/profile`.
- **Controller**: `ProfileController@edit`, `@update`.
- **View**: `resources/views/profile/edit.blade.php`.
- **Isi**: form identitas, alamat, kredensial; tombol mengikuti gaya outline/dark.

## 11. AdminManage Overview
- **Route group**: prefix `/admin-manage`, middleware `auth` + `EnsureUserIsAdmin` (`app/Http/Middleware/EnsureUserIsAdmin.php`).
- **Navbar admin**: anchor ke section `#dashboard-umum`, `#manajemen-katalog`, `#manajemen-anggota`, `#status-unit`, `#konfirmasi-pengembalian`, `#riwayat-sewa`.

## 12. Dashboard Admin
- **Route**: GET `/admin-manage` (`adminManage.dashboard`).
- **Controller**: `Admin\DashboardController@index`.
- **View**: `resources/views/admin/dashboard.blade.php`.
- **Komponen**: statistik total unit/user, ringkasan sewa aktif (on time vs telat), daftar cepat permintaan pengembalian, tombol "Pantau Pengembalian" (tombol utama satu-satunya).

## 13. Manajemen Konten Admin
- **Unit**: `Admin\UnitAdminController` + view `resources/views/admin/units/*` (index/create/edit). Tombol dengan tema outline, preview gambar memakai kartu hitam.
- **Kategori & Vintage**: `Admin\CategoryAdminController` + view `resources/views/admin/categories/*`.
- **Anggota/User**: `Admin\UserAdminController` + view `resources/views/admin/users/*`.
- **Status Unit & Detail Rental**: `Admin\RentalAdminController@index|show`, view `resources/views/admin/rentals/index.blade.php` & `show.blade.php`.

## 14. Pengembalian & Denda
- **Daftar permintaan**: GET `/admin-manage/returns` (`adminManage.returns.index`), view `resources/views/admin/returns/index.blade.php`.
  - Badge menandai status "Belum Terlambat", "Sudah Terlambat", "Menunggu Pembayaran".
- **Form konfirmasi**: GET `/admin-manage/returns/{rental}/confirm` (`adminManage.returns.form`).
  - View `resources/views/admin/returns/confirm.blade.php` menampilkan countdown, ringkasan deposit, input cleaning/damage fee, breakdown receipt.
- **Aksi**: POST `/admin-manage/returns/{rental}/confirm` (`adminManage.returns.confirm`) memanggil `ReturnAdminController@confirm` untuk mode `invoice` dan `finalize`.
- **Supporting service**: `App\Services\PricingService` menangani perhitungan deposit & late fee.

## 15. Laporan Admin
- **Riwayat Sewa**: GET `/admin-manage/reports/rentals` (`adminManage.reports.rentals`).
- **Ekspor CSV**: GET `/admin-manage/reports/rentals/export`.
- **View**: `resources/views/admin/rentals/history.blade.php`.
- **Konten**: filter per user/unit/periode, tabel dengan badge status, tombol ekspor outline.

## 16. Partial & Komponen Pendukung
- **Grid Unit**: `resources/views/units/_grid.blade.php` — digunakan di landing & katalog.
- **Toast & notif**: script pada `layouts/app.blade.php` + CSS `.toast-notice` di `app.css`.
- **Komponen lainnya**: kartu highlight, badge deposit/slot/status dikelola melalui kelas `.unit-pill` dkk.

## 17. Data & Migrasi Pendukung
- **Config**: `config/artcore.php` — deposit percent, late fee percent per hari, window Trial-to-Own, daftar vintage deposit.
- **Migrasi penalti**: `database/migrations/2025_10_30_221953_add_penalty_tracking_to_rentals_table.php` dan `2025_10_30_222104_add_penalty_details_to_return_records_table.php`.
- **Seeder**: `database/seeders/CategorySeeder.php`, `UnitSeeder.php`, `AdminUserSeeder.php`, `DatabaseSeeder.php`.

Dokumen ini dipasangkan dengan *FLOWS_AND_RENTAL.md* untuk memahami alur bisnis, serta README.md yang berisi daftar fitur dan instruksi setup.



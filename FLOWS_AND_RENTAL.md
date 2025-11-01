# ArtCore – User Flow & Rental Scheme

Dokumen ini menggambarkan alur akses, interaksi, dan skema penyewaan terbaru setelah penyempurnaan UI/UX, navigasi, serta logika denda ArtCore (Oktober 2025).

---

## 1. Akses & Navigasi Utama

- **Guest diarahkan ke login.** Route `/` berada di dalam middleware `auth`, sehingga setiap pengunjung wajib login/daftar sebelum mengakses konten.
- **Setelah login, user dan admin masuk ke landing page** (`/`). Navbar menyesuaikan peran:
  - **User:** Keranjang, Pembelian, Unit Disewa, Riwayat, Profil, Logout.
  - **Admin:** Tombol `AdminManage` menuju area manajemen; pada halaman admin, navbar berubah menjadi daftar anchor (Tinjauan, Katalog & Unit, dst) dengan highlight bergerak halus mengikuti klik atau scroll.
- **Tema & interaksi:** Seluruh tombol memakai palet hitam–cream sesuai desain. Hover dan active state menghadirkan transisi smooth; highlight navbar hanya mengikuti scroll saat pengguna memang melakukan scroll manual (bukan saat klik).

---

## 2. Alur User

1. **Beranda / Home**
   - Carousel "Sorotan Terbaru", grid katalog dengan filter nama/kode, kategori, dan vintage.
   - Tombol "Tambah ke Keranjang" bekerja via AJAX; unit badge menampilkan deposit, slot, dan status dalam kartu hitam.
2. **Keranjang (`/cart`)**
   - Validasi otomatis: maksimal 2 slot (kategori Sculpture menghitung 2 slot).
   - Unit vintage 60s/70s memaksa deposit 30% (config `artcore.deposit_percent`).
   - Checkout (`POST /rentals`) membuat Rental status `PENDING_PAYMENT` -> `ACTIVE` beserta deposit.
3. **Unit Disewa (`/rentals`)**
   - Countdown batas pengembalian dengan warna berbeda sebelum/sesudah telat.
   - Tombol "Trial to Own", "Ajukan Pengembalian", dan tombol besar "Bayar Denda" jika status `AWAITING_PENALTY`.
4. **Pembayaran Denda (`POST /rentals/{rental}/penalty-pay`)**
   - User melunasi sisa denda (deposit otomatis dihitung sebagai potongan). Status penalty berubah ke `PAID` saat lunas.
5. **Riwayat & Pembelian**
   - `/rentals/history` menampilkan resi pengembalian lengkap (status Tepat Waktu/Terlambat, rincian deposit & denda).
   - `/purchases` mencantumkan unit yang dibeli via skema Trial-to-Own atau pembelian langsung.
6. **Profil (`/profile`)**
   - Ubah nama, email, password, alamat, dan kontak.
7. **Logout** mengembalikan user ke halaman login.

---

## 3. Alur Admin

1. **AdminManage Dashboard (`/admin-manage`)**
   - Seksi "Tinjauan" menampilkan statistik unit, user, sewa aktif, dan ringkasan keterlambatan (berapa yang on time vs telat).
   - Tombol aksi berstyle `btn-ghost` (outline) kecuali "Pantau Pengembalian" yang tetap menonjol.
2. **Navigasi Seksi Admin**
   - Katalog & Unit (`/admin-manage/units`), Kategori, Anggota, Status Unit, Pengembalian, dan Riwayat Sewa. Semua tautan anchor (#) tersinkron dengan scroll spy sehingga highlight berpindah halus.
3. **Manajemen Data**
   - CRUD Unit, Kategori, User memakai form bernuansa hitam/cream dan tombol outline.
   - Daftar sewa aktif (`/admin-manage/rentals`), detail sewa (`/admin-manage/rentals/{id}`) menampilkan info countdown, deposit, riwayat pembayaran, dan status penalty.
4. **Pengembalian**
   - `/admin-manage/returns` menampilkan daftar permintaan dengan badge warna hitam (Belum Telat / Telah Telat / Menunggu Pembayaran).
   - Form konfirmasi (`/admin-manage/returns/{rental}/confirm`) memiliki dua mode: simpan tagihan (invoice) dan finalisasi setelah pembayaran.
5. **Laporan**
   - `/admin-manage/reports/rentals` menyediakan filter & tabel riwayat, dengan tombol ekspor CSV.

---

## 4. Skema Penyewaan & Denda

1. **Checkout**
   - `RentalController@store` menyimpan `rental_start = now` dan `rental_end_plan = +5 hari` (konfigurasi `artcore.max_rental_days`).
   - `deposit_paid` = 30% harga jual untuk vintage `60s` atau `70s` (tersimpan jika `Unit::requiresDeposit()`).
   - Unit dikunci (`is_available = false`).
2. **Trial-to-Own**
   - `RentalController@purchase` hanya aktif dalam 5 hari pertama (`artcore.tpo_window_days`).
   - Harga akhir = `sale_price - rent_fee_paid`; status rental `PURCHASED`, unit `is_sold = true`.
3. **Permintaan Pengembalian**
   - `RentalController@requestReturn` memberi status `RETURN_REQUESTED` dan mencatat `return_requested_at`.
4. **Perhitungan Denda**
   - `PricingService::calcLateFee()` menghitung 10% dari biaya sewa per hari keterlambatan (dibulatkan per hari penuh).
   - Admin bisa menambahkan cleaning fee (`artcore.cleaning_flat_fee` default 150k) dan damage fee.
5. **Invoice Denda (Admin)**
   - `ReturnAdminController@confirm` dengan aksi `invoice` menyimpan: `penalty_late_fee`, `penalty_cleaning_fee`, `penalty_damage_fee`, `penalty_total_due`, dan `penalty_status` (`DUE` bila masih ada yang harus dibayar).
   - Jika masih ada kekurangan setelah deposit, status rental berubah ke `AWAITING_PENALTY` sehingga user melihat tombol bayar denda.
6. **Pembayaran User**
   - `RentalController@payPenalty` menambahkan catatan pembayaran. Sistem menghitung ulang kekurangan dengan mempertimbangkan deposit yang terserap.
7. **Finalisasi Admin**
   - `ReturnAdminController@confirm` dengan aksi `finalize` mengecek `cashOutstanding`. Jika masih ada sisa, proses diblok dengan pesan "Menunggu pembayaran denda oleh user".
   - Pada finalisasi: buat record `Penalties` per jenis, catat potongan deposit sebagai `Payment` dengan metode `DEPOSIT`, simpan snapshot di `return_records`, set status rental `RETURNED`, dan buka ketersediaan unit kembali.
   - `delay_days` tercatat di `return_records` untuk membedakan Tepat Waktu vs Terlambat pada laporan.
8. **Riwayat & Notifikasi**
   - User mendapatkan notifikasi toast saat invoice dibuat dan ketika denda lunas.
   - Admin dashboard menandai sewa aktif yang melewati batas. Riwayat sewa menampilkan receipt (biaya sewa, deposit, denda, refund).

---

## 5. Validasi & Ketentuan Penting

- Slot aktif dihitung dari rental berstatus `PENDING_PAYMENT`, `ACTIVE`, `RETURN_REQUESTED`, dan `AWAITING_PENALTY`. Sculpture 3D mengambil 2 slot.
- Deposit hanya diwajibkan pada vintage `60s` dan `70s`; riwayat penalti admin dapat dipakai untuk menentukan eligibility (flag `eligibility_checked`).
- User tidak bisa mengonfirmasi pengembalian sendiri; mereka hanya mengajukan permintaan dan (bila perlu) membayar denda.
- Admin tidak dapat mem-finalize selama `penalty_total_due` belum lunas. Pesan validasi muncul di form konfirmasi.
- Semua badge status kini menggunakan warna hitam/cream agar konsisten dengan palet baru (tidak ada lagi hijau/kuning).
- Countdown di sisi user berubah warna setelah telat; highlight navbar berpindah sesuai section yang sedang dilihat.

Gunakan *PAGE_OVERVIEW.md* untuk detail file & controller yang terkait dengan setiap halaman. README utama memuat daftar fitur dan langkah setup proyek.




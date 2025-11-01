# ArtCore – Fitur Website

Repository ini berisi kode aplikasi **ArtCore**, platform sewa dan beli karya seni vintage yang dibangun dengan Laravel 10 dan Tailwind CSS. Dokumen ini merangkum daftar fitur lengkap per jenis pengguna.

## Teknologi Utama
- **Backend:** Laravel 10+, PHP 8.2
- **Frontend:** Blade + Tailwind CSS
- **Database:** MySQL (sudah disediakan migrasi dan seeder)
- **Autentikasi:** Laravel Breeze dengan guard `web` & middleware admin

## Fitur untuk Pengunjung (Guest)
- **Landing page dengan highlight** – carousel horizontal menampilkan unit unggulan terbaru.
- **Pencarian instan** berdasarkan nama/kode unit.
- **Filter kategori & tingkat vintage** langsung di beranda.
- **Detail unit** (setelah login) berisi foto, deskripsi, harga jual/sewa, status ketersediaan.
- **Proteksi akses**: klik unit / tambah keranjang otomatis mengarahkan ke login jika belum autentikasi.

## Fitur Pengguna Setelah Login
### Navigasi & Dashboard
- Navbar khusus user: `Keranjang`, `Pembelian`, `Unit Disewa`, `Riwayat`, `Profil`, `Logout`.
- Dashboard ringkas menampilkan sewa aktif, notifikasi, dan tautan cepat ke profil & riwayat.

### Katalog & Keranjang
- Tambah unit ke keranjang dengan validasi:
  - Batas maksimal 2 slot aktif.
  - Unit kategori *Sculpture/3D* otomatis memakai 2 slot.
  - Unit vintage 60s/70s butuh deposit 30% jika riwayat pengguna bersih dari denda kerusakan/keterlambatan.
- Checkout sewa mencatat pembayaran sewa + deposit dan menandai unit tidak tersedia.

### Manajemen Sewa Aktif
- Halaman **Unit Disewa** menampilkan tiap rental dengan countdown jatuh tempo, status badge hitam, ringkasan penalti, dan catatan deposit.
- Tombol *Ajukan Pengembalian* mengubah status menjadi `RETURN_REQUESTED`.
- Tombol *Bayar Denda* (ukuran besar) muncul jika penalti menunggu pembayaran.
- Tombol *Trial-to-Own (TPO)* memungkinkan pembelian unit dalam 5 hari pertama; harga akhir = harga jual - biaya sewa.

### Pengembalian & Pembayaran Denda
- Pengguna dapat membayar denda terlebih dahulu sebelum admin finalize.
- Riwayat rental/pembelian menyimpan resi lengkap (total denda, deposit dipotong/dikembalikan, catatan kondisi).
- Notifikasi pengembalian dan status denda tampil di halaman sewa.

### Profil Pengguna
- Edit nama, email, password, nomor telepon, dan alamat pengiriman.

## Fitur Admin (`/admin-manage`)
### Navigasi & Dashboard
- Navbar admin: `Dashboard Umum`, `Manajemen Katalog`, `Manajemen Anggota`, `Daftar Unit`, `Pengembalian`, `Riwayat Sewa`.
- Dashboard menampilkan statistik (unit total/tersedia, sewa aktif on-time vs late, total deposit, permintaan pengembalian, total user) serta daftar ringkasan unit terbaru, user terbaru, sewa aktif, unit purchased, permintaan pengembalian, dan riwayat sewa.
- Tombol aksi menggunakan gaya `btn-ghost` (border-only) kecuali "Pantau Pengembalian" yang tetap primary.

### Manajemen Data
- **CRUD Unit** lengkap: informasi dasar, harga sewa/jual, unggah foto, penentuan status tersedia/sold, flag double-slot.
- **CRUD Kategori**: tambah/edit/hapus kategori seni & level vintage.
- **CRUD Pengguna**: tambah/edit data anggota (user & admin).
- **Riwayat Sewa** dengan filter status, nama user, unit, rentang tanggal, dan opsi ekspor CSV.

### Workflow Pengembalian
- Halaman pengembalian menampilkan daftar `RETURN_REQUESTED` & `AWAITING_PENALTY` dengan badge hitam.
- Form konfirmasi pengembalian:
  - Hitung kombinasidenda (late/cleaning/damage) & kirim invoice ke user.
  - Validasi pembayaran tunai/deposit sebelum finalize.
  - Simpan catatan admin + snapshot biaya ke tabel `return_records`.
  - Otomatis menandai unit tersedia kembali dan mengubah status rental menjadi `RETURNED`.

## Sistem Harga, Deposit, dan Denda
- Biaya sewa standar: `rent_price_5d` (paket 5 hari). 
- Deposit 30% wajib untuk unit vintage (60s/70s) jika riwayat pengguna memenuhi syarat.
- Denda keterlambatan: 10% dari biaya sewa per hari keterlambatan (`PricingService::calcLateFee`).
- Admin dapat menambahkan denda cleaning/damage; sistem memotong deposit terlebih dahulu sebelum menagih user.
- Resi pengembalian menyimpan snapshot biaya sewa, deposit, denda, pengembalian deposit, dan catatan kondisi.

## Sorotan UI & Experience
- Tema warna konsisten (palet 5 warna) dengan badge/tombol hitam.
- Highlight halus pada kartu unit & sorotan (transform dan shadow).
- Scroll spy navbar admin mengikuti section saat pengguna scroll.
- Scrollbar carousel disesuaikan agar menyatu dengan warna tema.

## Seeder & Migrasi
- Seeder awal (kategori, unit contoh, admin) memudahkan setup.
- Migrasi tambahan menambahkan kolom penalti ke tabel `rentals` dan `return_records` untuk proses denda.

<img width="1366" height="768" alt="image" src="https://github.com/user-attachments/assets/68697a51-a761-4d93-bce6-3edc4ae415b3" />

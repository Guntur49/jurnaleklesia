# Jurnal Eklesia

Aplikasi jurnal pekerjaan harian internal untuk staff, admin, dan owner. Dibangun dengan Laravel dan MySQL; seluruh tampilan menggunakan CSS internal pada file Blade.

## Kebutuhan

- PHP 8.3 atau lebih baru
- Composer
- MySQL 8.0 atau MariaDB yang kompatibel

## Menjalankan aplikasi

1. Buat database bernama `jurnaleklesia` di MySQL.
2. Salin `.env.example` menjadi `.env`, lalu sesuaikan `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD`.
3. Jalankan:

   ```sh
   composer install
   php artisan key:generate
   php artisan migrate
   php artisan app:create-admin
   php artisan serve
   ```

4. Buka `http://127.0.0.1:8000/login` dan masuk dengan username serta kata sandi admin yang dibuat pada langkah sebelumnya.

Admin awal dibuat melalui prompt interaktif, tidak menggunakan kredensial bawaan. Admin dapat menambahkan akun staff dan owner dari menu **Kelola akun**.

## Peran dan fitur

- **Staff** mengisi atau memperbarui uraian pekerjaan dan hasil hari ini pada jam yang diatur admin. Jadwal yang melewati tengah malam didukung. Staff juga dapat melihat jurnal semua staff berdasarkan bulan dan tahun.
- **Admin** membuat, mengubah, serta menghapus akun staff/owner, mengatur jadwal pengisian staff, melihat arsip jurnal, dan mengunduh PDF bulanan.
- **Owner** melihat aktivitas jurnal terbaru di beranda dan menelusuri atau mengunduh arsip jurnal, tanpa akses untuk mengisi jurnal.

Untuk setiap bulan yang dipilih, halaman arsip dan file PDF menampilkan seluruh tanggal, termasuk hari tanpa jurnal, uraian pekerjaan, dan hasil hari tersebut. Jurnal lama tetap tersedia; jurnal yang dibuat sebelum kolom hasil ditambahkan akan menampilkan keterangan bahwa hasil belum dicatat. Menghapus akun staff juga menghapus jurnal akun tersebut.

## Pengujian

```sh
php artisan test
```

Test menggunakan SQLite in-memory dan tidak memerlukan konfigurasi MySQL.

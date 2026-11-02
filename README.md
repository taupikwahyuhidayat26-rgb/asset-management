<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Hak Akses Aplikasi

Aplikasi menyediakan role `Admin`, `Staff`, dan `User` untuk modul aset/unit fisik, barang masuk/keluar, supplier/Drop Point, serta karyawan dan transaksi HR.

- `Admin`: dapat melihat, menambah, mengubah, menghapus, mengekspor, dan mencetak data.
- `Staff`: dapat melakukan tindakan data yang sama, tetapi tidak memiliki pengelolaan akun.
- `User`: hanya dapat melihat data.

Hak akses juga diperiksa pada route, sehingga aksi yang tidak diizinkan tidak dapat dijalankan hanya dengan mengakses URL secara langsung.

Isi `ACCESS_INITIAL_ADMIN_EMAIL` di `.env` dengan email akun Admin awal. Pada database yang sudah berisi data, periksa status migrasi terlebih dahulu:

```sh
php artisan config:clear
php artisan migrate:status
```

Jangan jalankan `php artisan migrate` tanpa meninjau semua migrasi `Pending`: beberapa migrasi lama di proyek ini mencoba membuat ulang tabel yang sudah ada. Untuk menerapkan perubahan role saja pada database yang sudah berjalan, gunakan:

```sh
php artisan migrate --path=database/migrations/2026_10_29_000001_add_role_to_users_table.php
```

Jangan gunakan `migrate:fresh` pada database yang berisi data. Untuk instalasi baru atau melanjutkan migrasi lama yang tertunda, bereskan konflik migrasi lama terlebih dahulu.

Role akun lain secara default adalah `User`. Untuk menetapkan role `Staff`, ubah role akun tersebut melalui database:

```sql
UPDATE users SET role = 'Staff' WHERE email = 'email-staff@example.com';
```

Admin yang sudah login dapat mengelola akun melalui menu **Pengelolaan User**. Form tersebut dapat membuat akun, mengubah role dan data akun, mereset password, serta menghapus akun. Akun Admin utama dan Admin terakhir dilindungi agar akses pengelolaan tidak terkunci.

`HASH_VERIFY=false` memungkinkan login selama database masih berisi hash bcrypt dan Argon yang tercampur. Laravel akan membuat ulang hash menjadi algoritma default (bcrypt) setelah login berhasil. Setelah semua akun berhasil login dan hash lama telah diperbarui, pertimbangkan mengubahnya menjadi `true`.

## Foto Unit Aset

Pengguna yang sudah login dapat melihat foto unit melalui tombol **Foto** di menu **Detail Item**; Admin dan Staff juga dapat mengunggah serta menghapus foto. Foto disimpan pada disk privat aplikasi. Format yang didukung adalah JPG, PNG, dan WebP, dengan batas 10 foto per unggahan dan 5 MB per foto.

Pastikan konfigurasi PHP server mengizinkan `upload_max_filesize` minimal `5M` dan `post_max_size` minimal `50M` agar unggahan beberapa foto sekaligus tidak terpotong.

Pada database yang sudah berjalan, terapkan migrasi foto secara terpisah:

```sh
php artisan migrate --path=database/migrations/2026_10_30_000001_create_asset_item_images_table.php
```

## Riwayat Mutasi Inventory

Unit yang diterima selalu dibuat dengan status `Ready` di gudang. Transaksi barang keluar hanya menerima unit `Ready` yang masih berada di gudang; setelah transaksi berhasil, lokasi berpindah ke Drop Point tujuan dan status menjadi `Deployed`. Penyesuaian status/lokasi dari Detail Item juga dicatat bersama petugas dan alasan opsional. `Ready` hanya valid di gudang, sedangkan `Deployed` harus berada di Drop Point.

Secara default, ID Drop Point gudang adalah `1`. Jika berbeda, atur `INVENTORY_WAREHOUSE_DROPPOINT_ID` pada `.env`, lalu jalankan `php artisan config:clear`. Terapkan migrasi mutasi secara terpisah setelah memastikan tabel transaksi yang sedang digunakan benar:

```sh
php artisan migrate --path=database/migrations/2026_10_30_000002_create_stock_mutations_table.php
```

Migrasi ini mengisi riwayat awal dari dokumen barang masuk/keluar yang sudah tercatat. Perubahan lokasi atau status manual yang terjadi sebelum fitur audit ini dipasang tidak dapat direkonstruksi. Riwayat baru dapat dilihat lewat **Laporan → Kartu Stok Perangkat** dengan memilih model atau mencari SKU/nomor seri.

## Retur dan Transfer Antar-Drop Point

Setelah migrasi riwayat mutasi, terapkan migrasi transaksi retur/transfer:

```sh
php artisan migrate --path=database/migrations/2026_10_30_000003_create_asset_return_and_droppoint_transfer_tables.php
```

Menu **Barang Kembali** mencatat pengembalian unit dari satu Drop Point ke gudang, dengan status penerimaan `Ready`, `Maintenance`, atau `Broken`. Menu **Transfer Drop Point** memindahkan unit `Deployed` dari Drop Point asal ke tujuan lain dan mempertahankan status `Deployed`. Kedua transaksi mengunci dan memvalidasi ulang unit saat disimpan, lalu membuat dokumen, detail unit, perubahan lokasi/status, serta riwayat mutasi secara atomik.

## Laporan Inventory dan Backup

Setelah kategori aset tersedia, jalankan migrasi menu laporan:

```sh
php artisan migrate --path=database/migrations/2026_10_30_000004_create_asset_categories_table.php
php artisan migrate --path=database/migrations/2026_10_30_000005_add_inventory_report_navigation.php
```

Menu **Laporan → Laporan Inventory** menampilkan jumlah unit berdasarkan filter kategori, status, lokasi, atau kata kunci, dan dapat mengekspor hasil yang sama ke CSV yang kompatibel dengan Excel. Ekspor laporan tersedia untuk pengguna yang memiliki akses melihat data.

Menu **Administrasi → Backup Data** hanya tersedia untuk Admin. Backup dibuat saat diminta dan langsung diunduh sebagai ZIP berisi dump SQL database, gambar aset privat, dan README pemulihan. Pastikan ekstensi PHP `zip` aktif; untuk PostgreSQL, executable `pg_dump` juga harus tersedia di server. Arsip memuat data akun dan transaksi; simpan di lokasi aman yang aksesnya dibatasi. Backup tidak disimpan otomatis pada server.

Pemulihan dilakukan dengan mengimpor `database.sql` ke database yang sesuai dan mengunggah isi folder `images/` ke disk foto privat yang dikonfigurasi, dengan struktur direktori tetap. Lakukan uji pemulihan pada database terpisah secara berkala.

## Hosting Demo Gratis

Panduan deploy Laravel ke Render dengan PostgreSQL dan penyimpanan foto privat Supabase tersedia di [docs/hosting.md](docs/hosting.md). Jangan menjalankan migrasi otomatis sebelum meninjau migrasi lama yang masih berstatus `Pending`.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

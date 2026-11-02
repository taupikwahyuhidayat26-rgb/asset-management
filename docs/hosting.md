# Hosting Demo Gratis

Konfigurasi demo ini memakai Render untuk menjalankan Laravel dan Supabase Free untuk PostgreSQL serta penyimpanan foto privat. Render Free menidurkan web service setelah tidak ada trafik dan menghapus perubahan pada filesystem lokal saat service tidur, restart, atau deploy. Supabase Free dapat menjeda proyek setelah seminggu tidak aktif. Karena itu, konfigurasi ini untuk demo, bukan sistem produksi.

Kuota Supabase Free saat panduan ini dibuat mencakup database 500 MB dan penyimpanan file 1 GB. Aplikasi lokal yang diperiksa memakai MySQL aktif, bukan `database/database.sqlite`; snapshot database berukuran sekitar 1.3 MiB dan tiga foto sekitar 0.24 MiB. Ukur kembali sebelum pemindahan. Detail batasan dapat berubah:

- [Render Free instances](https://render.com/docs/free)
- [Supabase pricing](https://supabase.com/pricing)
- [Supabase S3 compatibility](https://supabase.com/docs/guides/storage/s3/compatibility)

## Sebelum deploy

1. Gunakan PHP 8.2 atau lebih baru untuk perintah Laravel lokal. Pastikan Laragon tidak menjalankan PHP 8.1 dari CLI.
2. Buat repository GitHub **private** dan hubungkan ke Render. `.gitignore` mengecualikan database SQLite dan foto privat; jangan menghapus pengecualian ini atau mengunggah `.env`, backup, maupun data aset ke GitHub.
3. Buat proyek Supabase, ambil kredensial PostgreSQL dari **Session Pooler** bila koneksi langsung tidak tersedia, dan buat bucket Storage **private** dengan S3 credentials.
4. Buat web service Render dari repository. `render.yaml` menyediakan Docker build dan meminta nilai rahasia di dashboard; jangan menaruh password atau access key di repository.
5. Atur `APP_KEY` (hasilkan dengan `php artisan key:generate --show`), `APP_URL`, email admin awal, variabel `DB_*`, dan kredensial S3 Supabase di Render. Untuk `AWS_ENDPOINT`, gunakan endpoint S3 proyek Supabase; bucket harus tetap privat. Masukkan secret langsung di dashboard Render, jangan mengirimkannya melalui chat.

## Pemindahan database dan foto

Database lokal yang aktif menggunakan MySQL, sedangkan demo memakai PostgreSQL. File `database/database.sqlite` bukan sumber data aktif. Pemindahan memerlukan konversi skema dan data MySQL ke PostgreSQL; backup SQL dari aplikasi tidak bisa langsung diimpor ke PostgreSQL.

Jangan jalankan `php artisan migrate --force` pada database lokal atau database hasil salinan sebelum riwayat migrasi ditinjau. Database proyek saat ini memiliki beberapa migrasi lama berstatus `Pending` yang dapat mencoba membuat tabel yang sudah ada. Konfigurasi deploy ini sengaja tidak menjalankan migrasi otomatis saat startup.

Sebelum membuka akses demo, siapkan skema PostgreSQL, pindahkan data MySQL dengan alat konversi yang mendukung MySQL-to-PostgreSQL, lalu unggah foto lokal ke bucket privat dengan mempertahankan path di `asset_item_images.path`. Verifikasi jumlah baris, relasi, akun Admin, dan foto yang bisa dibaca. Jalankan impor hanya ke database Supabase baru/backup yang dapat dipulihkan; jangan gunakan `migrate:fresh` pada data yang ingin dipertahankan.

Fitur **Backup Data** menghasilkan dump PostgreSQL melalui `pg_dump` dan menyertakan foto dari disk privat yang dikonfigurasi. Simpan arsip dengan aman karena memuat data akun dan transaksi.

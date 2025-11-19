# Fast-Claim

Sistem klaim kesehatan (BPJS) berbasis Laravel 12 + Livewire 3 (Volt) + Tailwind CSS v4.

## 1. Fitur Utama (Ringkas)

-   Upload & proses dokumen SEP / klaim.
-   Integrasi folder shared (Z:) untuk sinkronisasi file klaim (opsional).
-   Livewire Volt untuk interaktivitas.
-   Antrian (queue) berbasis database untuk tugas berat.
-   Penyimpanan file terstruktur (storage/app/private, shared, backup).

## 2. Prasyarat

Pastikan terpasang:

-   PHP 8.3.x (ext: fileinfo, pdo_mysql, mbstring, openssl, intl, gd atau imagick jika perlu).
-   Composer 2.x
-   MySQL 8 / MariaDB (atau kompatibel).
-   Node.js 20+ dan npm 10+
-   Git
-   (Opsional) pdftotext (isi `PDFTOTEXT_PATH` di `.env`).
-   Folder jaringan (Windows) untuk `Z:` jika memakai shared/backup asli.

## 3. Clone & Setup Awal

```bash
git clone <repo-url> kubr-claim
cd kubr-claim
composer install
npm install
```

## 4. Konfigurasi Lingkungan

Salin `.env`:

```bash
cp .env.example .env
php artisan key:generate
```

Edit variabel penting:

```
APP_URL=https://your-domain-or-ngrok.test/
APP_ENV=local
APP_DEBUG=true
DB_HOST=127.0.0.1
DB_DATABASE=fast-claim
DB_USERNAME=fast-claim
DB_PASSWORD=fast-claim
FILESYSTEM_DISK=local
FOLDER_SHARED="Z:/FOLDER KLAIM REGULER BPJS SINTA"
FOLDER_BACKUP="Z:/mnt/Backup Folder Klaim/Folder Klaim Reguler BPJS"
```

Jika hosting Linux tanpa drive `Z:`:

-   Buat direktori lokal, misal `storage/app/shared` & `storage/app/backup`.
-   Ubah:

```
FOLDER_SHARED=/var/www/app/storage/app/shared
FOLDER_BACKUP=/var/www/app/storage/app/backup
```

Pastikan permission (Linux):

```bash
chmod -R ug+rw storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## 5. Migrasi & Tabel Tambahan

Queue + session + cache memakai database.

Generate (jika belum ada) lalu migrasi:

```bash
php artisan queue:table
php artisan session:table
php artisan cache:table
php artisan migrate
```

## 6. Link Storage

```bash
php artisan storage:link
```

## 7. Format Kode

Sebelum commit:

```bash
vendor/bin/pint --dirty
```

## 8. Build Asset Frontend

Mode dev:

```bash
npm run dev
```

Produksi:

```bash
npm run build
```

Tailwind v4: pastikan file CSS utama memakai:

```css
@import "tailwindcss";
@theme {
    /* kustom tema */
}
```

## 9. Menjalankan Aplikasi

Server lokal (jika tidak pakai Laragon/nginx):

```bash
php artisan serve
```

Queue worker:

```bash
php artisan queue:work --tries=3
```

## 10. Upload Dokumen SEP

-   Pastikan komponen Livewire Volt memakai `WithFileUploads` (jika class-based).
-   Periksa limit:
    -   `php.ini`: `upload_max_filesize`, `post_max_size`
-   Folder sementara Livewire: `storage/framework/livewire-tmp` harus writable.
-   Jika gagal memproses file di hosting tanpa `Z:`, pastikan memakai disk fallback (`local`).

## 11. Penyesuaian Filesystem

`config/filesystems.php` mendefinisikan disk:

-   `local` -> `storage/app/private`
-   `shared` & `backup` -> ENV path (ubah ke path valid server produksi)
    Pastikan tidak menaruh path Windows di server Linux.

## 12. Testing

Menjalankan seluruh test:

```bash
php artisan test
```

Tambahkan test baru (Pest):

```bash
php artisan make:test --pest Feature/YourFeatureTest
```

## 13. Deploy Produksi (Ringkas)

1. Set `.env`:
    - `APP_ENV=production`
    - `APP_DEBUG=false`
2. Jalankan:
    ```bash
    composer install --no-dev --optimize-autoloader
    php artisan optimize
    npm run build
    php artisan migrate --force
    php artisan queue:work --daemon --tries=3
    ```
3. Pastikan supervisor/systemd untuk queue & scheduler (`php artisan schedule:run` via cron per menit).

## 14. Troubleshooting Singkat

| Gejala                          | Solusi                                                    |
| ------------------------------- | --------------------------------------------------------- |
| Upload gagal                    | Periksa permission storage & limit php.ini                |
| File tidak muncul               | Jalankan `php artisan storage:link` dan cek disk path     |
| Tailwind class tidak ter-update | Jalankan `npm run dev` atau refresh build                 |
| 419 (expired)                   | Cek session table & `SESSION_DRIVER=database` migrasi ada |
| Queue tidak jalan               | Pastikan worker aktif & tabel jobs terbuat                |

## 15. Kontribusi

1. Fork repository.
2. Buat branch fitur: `feat/nama-fitur`.
3. Jalankan pint & test.
4. Pull request ringkas (sertakan deskripsi perubahan & langkah uji).

## 16. Keamanan

-   Jangan commit `.env`.
-   Regenerasi `APP_KEY` hanya saat setup awal.
-   Pastikan sanitasi/validasi file upload (mimes, size).

## 17. Lisensi

Tambahkan informasi lisensi (MIT / lainnya) di bagian ini.

---

Selesai. Tambahkan detail domain / modul klinis jika perlu.

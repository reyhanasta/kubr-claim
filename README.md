# Fast-Claim

A healthcare claims (BPJS) system built with Laravel 12 + Livewire 3 (Volt) + Tailwind CSS v4.

## 1. Key Features (Brief)

-   Upload and process SEP / claim documents.
-   Optional shared folder integration (drive `Z:`) for claim file synchronization.
-   Livewire Volt for interactivity.
-   Database-backed queues for heavy jobs.
-   Structured file storage (storage/app/private, shared, backup).

## 2. Prerequisites

Please have these installed:

-   PHP 8.3.x (extensions: fileinfo, pdo_mysql, mbstring, openssl, intl, gd or imagick if needed).
-   Composer 2.x
-   MySQL 8 / MariaDB (or compatible).
-   Node.js 20+ and npm 10+
-   Git
-   (Optional) pdftotext (set `PDFTOTEXT_PATH` in `.env`).
-   Network drive (Windows) for `Z:` if using the original shared/backup locations.

## 3. Clone & Initial Setup

```bash
git clone <repo-url> kubr-claim
cd kubr-claim
composer install
npm install
```

## 4. Environment Configuration

Copy `.env`:

```bash
cp .env.example .env
php artisan key:generate
```

Edit the important variables:

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

If you deploy on Linux without drive `Z:`:

-   Create local directories, e.g. `storage/app/shared` & `storage/app/backup`.
-   Change:

```
FOLDER_SHARED=/var/www/app/storage/app/shared
FOLDER_BACKUP=/var/www/app/storage/app/backup
```

Ensure permissions (Linux):

```bash
chmod -R ug+rw storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## 5. Migrations & Supporting Tables

Queue + session + cache use the database.

Generate (if not present) then migrate:

```bash
php artisan queue:table
php artisan session:table
php artisan cache:table
php artisan migrate
```

## 6. Storage Symlink

```bash
php artisan storage:link
```

## 7. Code Formatting

Before committing:

```bash
vendor/bin/pint --dirty
```

## 8. Build Frontend Assets

Development mode:

```bash
npm run dev
```

Production build:

```bash
npm run build
```

Tailwind v4: ensure your main CSS file includes:

```css
@import "tailwindcss";
@theme {
    /* custom theme */
}
```

## 9. Run the Application

Local server (if not using Laragon/nginx):

```bash
php artisan serve
```

Queue worker:

```bash
php artisan queue:work --tries=3
```

## 10. Uploading SEP Documents

-   Ensure the Livewire (Volt) component uses `WithFileUploads` (if class-based).
-   Check limits:
    -   `php.ini`: `upload_max_filesize`, `post_max_size`
-   Livewire temporary folder: `storage/framework/livewire-tmp` must be writable.
-   If processing fails on hosts without `Z:`, ensure you use the fallback disk (`local`).

## 11. Filesystem Adjustments

`config/filesystems.php` defines disks:

-   `local` -> `storage/app/private`
-   `shared` & `backup` -> ENV paths (change to valid server paths in production)
    Ensure you don’t use Windows paths on Linux servers.

## 12. Testing

Run all tests:

```bash
php artisan test
```

Add a new test (Pest):

```bash
php artisan make:test --pest Feature/YourFeatureTest
```

## 13. Production Deployment (Quick)

1. Set `.env`:
    - `APP_ENV=production`
    - `APP_DEBUG=false`
2. Run:
    ```bash
    composer install --no-dev --optimize-autoloader
    php artisan optimize
    npm run build
    php artisan migrate --force
    php artisan queue:work --daemon --tries=3
    ```
3. Ensure supervisor/systemd for the queue worker and the scheduler (`php artisan schedule:run` via cron every minute).

## 14. Quick Troubleshooting

| Symptom                       | Fix                                                    |
| ----------------------------- | ------------------------------------------------------ |
| Upload fails                  | Check storage permissions & php.ini limits             |
| Files not visible             | Run `php artisan storage:link` and verify disk paths   |
| Tailwind classes not updating | Run `npm run dev` or refresh build                     |
| 419 (expired)                 | Check session table & `SESSION_DRIVER=database` exists |
| Queue not working             | Ensure worker running & `jobs` table exists            |

## 15. Contributing

1. Fork the repository.
2. Create a feature branch: `feat/your-feature`.
3. Run Pint & tests.
4. Open a concise PR (describe changes & test steps).

## 16. Security

-   Do not commit `.env`.
-   Only generate `APP_KEY` during initial setup.
-   Validate/sanitize file uploads (mimes, size).

## 17. License

Add license details here (MIT / other).

---

Done. Add your domain details / clinical modules as needed.

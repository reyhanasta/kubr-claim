# FastClaim - Sistem Klaim BPJS

<p align="center">
  <img src="public/FastClaim_Icon.png" alt="FastClaim Logo" width="120">
</p>

<p align="center">
  <strong>Sistem Manajemen Klaim BPJS yang Cepat, Mudah, dan Terintegrasi</strong>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-red?logo=laravel" alt="Laravel">
  <img src="https://img.shields.io/badge/Livewire-3.x-pink?logo=livewire" alt="Livewire">
  <img src="https://img.shields.io/badge/PHP-8.2+-blue?logo=php" alt="PHP">
  <img src="https://img.shields.io/badge/TailwindCSS-4.x-38B2AC?logo=tailwindcss" alt="Tailwind">
</p>

---

## 📋 Tentang Aplikasi

**FastClaim** adalah aplikasi web untuk mengelola dokumen klaim BPJS Kesehatan di fasilitas kesehatan (klinik/rumah sakit). Aplikasi ini membantu proses:

-   📄 Upload dan merge dokumen klaim (SEP, Resume Medis, Billing, Hasil Lab)
-   🔄 Ekstraksi data otomatis dari file SEP PDF
-   📁 Penyimpanan terstruktur berdasarkan periode dan jenis rawatan
-   💾 Backup otomatis ke lokasi terpisah
-   👥 Manajemen user dengan role (Admin/Operator)

## ✨ Fitur Utama

| Fitur               | Deskripsi                                                                    |
| ------------------- | ---------------------------------------------------------------------------- |
| **Upload Dokumen**  | Upload SEP, Resume Medis, Billing, LIP, dan Hasil Lab dalam format PDF       |
| **Auto Extract**    | Ekstraksi otomatis nomor SEP, nama pasien, tanggal, dan kelas dari file SEP  |
| **PDF Merge**       | Penggabungan otomatis semua dokumen menjadi satu file PDF                    |
| **Backup Otomatis** | Backup file ke lokasi terpisah dengan struktur folder yang sama              |
| **Dashboard**       | Monitoring jumlah klaim, statistik per periode, dan status backup            |
| **Multi User**      | Role Admin dan Operator dengan hak akses berbeda                             |
| **Dark Mode**       | Dukungan tema terang dan gelap                                               |
| **Settings**        | Pengaturan profil klinik dan lokasi folder penyimpanan dengan folder browser |

## 🛠️ Tech Stack

-   **Backend**: Laravel 12, PHP 8.2+
-   **Frontend**: Livewire 3, Flux UI, Tailwind CSS 4
-   **Database**: MySQL / SQLite
-   **PDF Processing**:
    -   `setasign/fpdi` - Merge PDF
    -   `spatie/pdf-to-text` - Ekstraksi teks PDF (via Poppler)
-   **Queue**: Database Queue Driver

## 📦 Requirements

### Software

-   PHP >= 8.2 dengan extensions:
    -   BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML
-   Composer >= 2.0
-   Node.js >= 18.x dengan NPM
-   MySQL >= 5.7.x atau SQLite
-   **Poppler Utils** (untuk ekstraksi teks PDF)
    -   Windows: Download dari [poppler-windows](https://github.com/oschwartz10612/poppler-windows/releases)
    -   Linux: `sudo apt install poppler-utils`

### Hardware (Minimum)

-   CPU: 2 Core
-   RAM: 4 GB
-   Storage: 50 GB (tergantung volume klaim)

## 🚀 Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/reyhanasta/kubr-claim.git
cd kubr-claim
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node dependencies
npm install
```

### 3. Environment Setup

```bash
# Copy environment file
cp .env.dev .env

# Generate application key
php artisan key:generate
```

### 4. Konfigurasi .env

Edit file `.env` dan sesuaikan:

```env
# Application
APP_NAME=FastClaim
APP_ENV=production
APP_DEBUG=false
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fast_claim
DB_USERNAME=root
DB_PASSWORD=

# Queue (wajib untuk backup)
QUEUE_CONNECTION=database

# PDF Text Extraction
PDFTOTEXT_PATH=C:\path\to\pdftotext.exe  # Windows
# PDFTOTEXT_PATH=/usr/bin/pdftotext       # Linux

# Storage Paths
FOLDER_SHARED=D:/Folder Klaim BPJS
FOLDER_BACKUP=D:/Backup Klaim BPJS
```

### 5. Database Migration

```bash
# Buat database terlebih dahulu, lalu:
php artisan migrate --seed
```

### 6. Build Assets

```bash
npm run build
```

### 7. Storage Link

```bash
php artisan storage:link
```

## 🖥️ Menjalankan Aplikasi

### Development

```bash
# Jalankan semua service (server, queue, vite)
composer run dev
```

### Production

```bash
# Build dan optimize
composer run prod
```

Atau jalankan manual:

```bash
# Terminal 1 - Web Server
php artisan serve --host=0.0.0.0 --port=8000

# Terminal 2 - Queue Worker (wajib untuk backup)
php artisan queue:listen --tries=3 --timeout=120
```

## 👤 Default Login

Setelah menjalankan seeder:

| Role     | Email                | Password    |
| -------- | -------------------- | ----------- |
| Admin    | astareyhan@gmail.com | admin1234   |
| Operator | operator@kubr.local  | operator123 |

> ⚠️ **Penting**: Segera ganti password default setelah login pertama!

## 📁 Struktur Folder Output

File klaim akan disimpan dengan struktur:

```
FOLDER_SHARED/
└── 2025/
    └── 12_DESEMBER REGULER 2025/
        ├── R.JALAN/
        │   └── 01/
        │       └── 0069S0020125V000001/
        │           ├── NAMA_PASIEN.pdf (merged)
        │           └── LIP.pdf
        └── R.INAP/
            └── 15/
                └── 0069S0021225V000002/
                    ├── NAMA_PASIEN.pdf
                    └── LIP.pdf
```

## ⚙️ Konfigurasi Tambahan

### Auto Start (Windows)

Untuk menjalankan aplikasi otomatis saat PC menyala, gunakan script yang tersedia:

````bash
# Menggunakan batch script
start-server.bat


Kemudian tambahkan ke Windows Task Scheduler.

### Network Share

Jika menggunakan network share (Z:/, dll), pastikan:

1. Drive sudah di-mount sebelum aplikasi berjalan
2. User yang menjalankan PHP memiliki akses write ke share
3. Gunakan UNC path jika mapped drive tidak terdeteksi: `//192.168.1.100/share`

### Konfigurasi Folder via UI

Admin dapat mengkonfigurasi folder penyimpanan melalui:

1. Login sebagai Admin
2. Buka **Settings** → **Penyimpanan**
3. Gunakan tombol **Browse** untuk memilih folder
4. Klik **Test Koneksi** untuk verifikasi
5. Simpan pengaturan

## 🧪 Testing

```bash
# Jalankan semua test
composer run test

# Atau dengan filter
php artisan test --filter=BpjsRawatJalanFormTest
````

## 🔧 Troubleshooting

| Masalah                        | Solusi                                                                           |
| ------------------------------ | -------------------------------------------------------------------------------- |
| Upload gagal                   | Cek permission storage & php.ini limits (`upload_max_filesize`, `post_max_size`) |
| File tidak terlihat            | Jalankan `php artisan storage:link` dan verifikasi disk paths                    |
| Tailwind tidak update          | Jalankan `npm run build` atau `npm run dev`                                      |
| Error 419 (expired)            | Cek session table & pastikan `SESSION_DRIVER=database`                           |
| Queue tidak jalan              | Pastikan worker running & tabel `jobs` ada                                       |
| Ekstraksi SEP gagal            | Cek `PDFTOTEXT_PATH` di .env sudah benar                                         |
| Folder shared tidak terdeteksi | Gunakan UNC path (`//ip/share`) atau pastikan drive sudah mount                  |

## 📝 Changelog

### v1.0.0 (Desember 2025)

-   Initial release
-   Upload dan merge dokumen klaim
-   Ekstraksi data otomatis dari SEP
-   Backup otomatis ke folder terpisah
-   Dashboard statistik klaim
-   Multi-user dengan role Admin/Operator
-   Settings untuk profil klinik dan storage
-   Folder browser untuk konfigurasi path
-   Dark mode support

## 🤝 Kontribusi

Kontribusi sangat diterima! Silakan:

1. Fork repository
2. Buat branch fitur (`git checkout -b feature/AmazingFeature`)
3. Format code (`vendor/bin/pint --dirty`)
4. Commit perubahan (`git commit -m 'Add some AmazingFeature'`)
5. Push ke branch (`git push origin feature/AmazingFeature`)
6. Buat Pull Request

## 📄 Lisensi

Distributed under the MIT License. See `LICENSE` for more information.

## 📞 Kontak

**Developer**: Reyhan Asta  
**Email**: astareyhan@gmail.com  
**Repository**: [https://github.com/reyhanasta/kubr-claim](https://github.com/reyhanasta/kubr-claim)

---

<p align="center">
  Made with ❤️ for Indonesian Healthcare
</p>

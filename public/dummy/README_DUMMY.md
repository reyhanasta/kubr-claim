# File Dummy untuk Demo

File-file ini digunakan untuk demo aplikasi FastClaim.

## File SEP Dummy

File `SURAT ELEGIBILITAS PESERTA test.pdf` sudah kompatibel dengan ekstraksi otomatis.

### Data yang Diekstrak:

-   **No. SEP**: 0069G0020212X123426
-   **Tgl. SEP**: 2021-01-01
-   **No. Kartu BPJS**: 0009999999999
-   **No. RM**: 0242424
-   **Nama Pasien**: Jep Besos
-   **Jenis Rawatan**: R.Jalan (RJ)
-   **Kelas Rawat**: Kelas 3

### Cara Menggunakan:

1. Login ke aplikasi
2. Pilih menu **Rawat Jalan**
3. Upload file SEP dummy ini
4. Data akan ter-ekstrak otomatis
5. Upload file dummy lainnya (Resume, Billing, dll)
6. Klik **Simpan**

## Membuat PDF Dummy Sendiri

Jika ingin membuat PDF SEP dummy sendiri, pastikan format teks mengikuti pola ini:

```
SURAT ELEGIBILITAS PESERTA

No.SEP                  : 0069G0020212X123426
Tgl.SEP                 : 2021-01-01
No.Kartu                : 0009999999999 (MR. 0242424)
Nama Peserta            : Jep Besos
Tgl.Lahir               : 2007-02-18
Jns.Rawat               : R.Jalan
Kls.Rawat               : Kelas 3
```

**Penting:**

-   Format tanggal harus `YYYY-MM-DD`
-   No. Kartu dan MR harus dalam format: `(MR. 123456)`
-   Jenis Rawat: `R.Jalan` atau `R.Inap`
-   Kelas: `Kelas 1`, `Kelas 2`, atau `Kelas 3`

## File Dummy Lainnya

Anda juga perlu:

-   **Resume Medis** (PDF)
-   **Billing** (PDF/JPG/PNG)
-   **LIP** - opsional (PDF)
-   **Hasil Lab** - opsional (PDF)

File-file ini tidak perlu format khusus, cukup file PDF/image biasa untuk testing merge PDF.

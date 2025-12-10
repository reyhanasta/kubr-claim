# FastClaim - Performance & ROI Analysis

Dokumen ini berisi analisis waktu, biaya, dan ROI dari penggunaan FastClaim dibandingkan dengan proses manual.

---

## 📊 Breakdown Proses Manual vs FastClaim

### **Proses Manual (Per 1 Klaim):**

| Tahap | Waktu | Deskripsi |
|-------|-------|-----------|
| **1. Review File** | 1 menit | Buka dan cek 5 file PDF (SEP, Resume, Billing, Lab, LIP) |
| **2. Merge PDF** | 1 menit 40 detik | Buka tool merge, arrange 4 files, proses merge & save |
| **3. Rename File** | 25 detik | Baca nama pasien dari SEP, rename merged PDF |
| **4. Buat/Navigasi Folder** | 26 detik | Cek/buat folder tanggal (~1 detik overhead), buat folder SEP unik (~15 detik), navigasi (~10 detik) |
| **5. Move File** | 30 detik | Cut & paste merged PDF + LIP terpisah |
| **6. Backup Manual** | 45 detik | Navigasi ke folder backup, copy-paste |
| **TOTAL** | **4 menit 46 detik** | Waktu ideal tanpa gangguan |

**Dengan Overhead Realistis:**
- Merge PDF error/lambat: ~5-10% klaim (+30 detik)
- Salah folder/redo: ~3-5% klaim (+1 menit)
- Gangguan kerja (telepon, koordinasi): ~10% waktu

**Waktu Realistis: 5-6 menit per klaim** (rata-rata: **5.5 menit**)

---

### **Proses dengan FastClaim (Per 1 Klaim):**

| Tahap | Waktu | Deskripsi |
|-------|-------|-----------|
| **1. Upload File** | 30 detik | Drag & drop 5 file PDF |
| **2. Auto-Extract & Review** | 20 detik | Sistem ekstrak data SEP, user review & confirm |
| **3. Submit** | 5 detik | Klik submit |
| **4. Auto Process** | 15 detik | Background: merge PDF, organize folder, backup |
| **TOTAL** | **1 menit 10 detik** | Termasuk semua proses otomatis |

**Waktu Realistis: 1-1.5 menit per klaim** (rata-rata: **1.25 menit**)

---

## 🧮 Perhitungan untuk 400 Klaim per Bulan

### **Skenario Manual:**

| Metrik | Perhitungan | Hasil |
|--------|-------------|-------|
| **Waktu per klaim** | 5.5 menit | 5.5 menit |
| **Total waktu untuk 400 klaim** | 400 × 5.5 menit | **2,200 menit** |
| **Konversi ke jam** | 2,200 ÷ 60 | **36.7 jam** |
| **Konversi ke hari kerja** (8 jam/hari) | 36.7 ÷ 8 | **4.6 hari kerja** |

**Implikasi:**
- 4.6 hari penuh hanya untuk input klaim
- Sisa 17.4 hari dalam 1 bulan (22 hari kerja) untuk kerjaan lain

---

### **Skenario dengan FastClaim:**

| Metrik | Perhitungan | Hasil |
|--------|-------------|-------|
| **Waktu per klaim** | 1.25 menit | 1.25 menit |
| **Total waktu untuk 400 klaim** | 400 × 1.25 menit | **500 menit** |
| **Konversi ke jam** | 500 ÷ 60 | **8.3 jam** |
| **Konversi ke hari kerja** (8 jam/hari) | 8.3 ÷ 8 | **1 hari kerja** |

**Implikasi:**
- 1 hari untuk input klaim
- Sisa 21 hari dalam 1 bulan untuk kerjaan lain

---

## 💰 Analisis Biaya & Penghematan

### **Asumsi Biaya:**
- Upah 1 petugas administrasi BPJS: **Rp 3.000.000/bulan**
- Jam kerja: 8 jam/hari × 22 hari = **176 jam/bulan**
- Upah per jam: Rp 3.000.000 ÷ 176 jam = **Rp 17.045/jam**

---

### **Per Bulan (400 Klaim):**

| Item | Manual | FastClaim | **Selisih** |
|------|--------|-----------|-------------|
| **Jam kerja** | 36.7 jam | 8.3 jam | **28.4 jam** |
| **Biaya waktu** | Rp 625.551 | Rp 141.474 | **Rp 484.077** |
| **Hari produktif hilang** | 4.6 hari | 1 hari | **Hemat 3.6 hari** |
| **Produktivitas** | 87 klaim/hari | 400 klaim/hari | **4.6x lebih produktif** |

**Penghematan Waktu:**
```
Manual: 36.7 jam
FastClaim: 8.3 jam
─────────────────────
Hemat: 28.4 jam (77% lebih cepat!)
```

---

### **Per Tahun (4,800 Klaim):**

| Metrik | Manual | FastClaim | **Hemat** |
|--------|--------|-----------|-----------|
| **Total jam kerja** | 440 jam | 100 jam | **340 jam** |
| **Setara hari kerja** | 55 hari | 12.5 hari | **42.5 hari kerja** |
| **Setara minggu kerja** | 11 minggu | 2.5 minggu | **8.5 minggu** |
| **Biaya waktu** | Rp 7.506.612 | Rp 1.704.545 | **Rp 5.802.067** |

---

## 🎯 Return on Investment (ROI)

### **Investasi:**
- Harga FastClaim (tier Professional): **Rp 1.500.000/tahun**

### **Benefit per Tahun:**
- Penghematan biaya waktu: **Rp 5.802.067**
- Penghematan hari kerja: **42.5 hari** (bisa produktif untuk hal lain)
- Peningkatan kapasitas: **360%** (87 → 400 klaim/hari)

### **Perhitungan ROI:**
```
ROI = (Benefit - Cost) / Cost × 100%
    = (5.802.067 - 1.500.000) / 1.500.000 × 100%
    = 287% per tahun
```

### **Break-Even Point:**
```
Break-Even = Investment / Monthly Savings
           = 1.500.000 ÷ 484.077
           = 3.1 bulan
```

**FastClaim balik modal dalam 3 bulan pertama!**

---

## 📈 Productivity & Capacity Gain

### **Waktu yang Dihemat (28.4 jam/bulan = 3.6 hari kerja):**

Dengan waktu ekstra ini, petugas dapat:

✅ **Verifikasi klaim lebih teliti** (reduce reject rate dari BPJS)  
✅ **Koordinasi intensif** dengan dokter & verifikator BPJS  
✅ **Training & SOP improvement** untuk tim  
✅ **Handle komplain & pertanyaan pasien** dengan lebih baik  
✅ **Prepare dokumentasi** untuk audit BPJS  

**Atau:**

✅ **Handle lebih banyak klaim** dengan SDM yang sama  
- Kapasitas sebelumnya: 87 klaim/hari/orang  
- Kapasitas dengan FastClaim: 400 klaim/hari/orang  
- **Peningkatan: 360%**

---

## 🎯 Error Rate Comparison

| Error Type | Manual (estimasi) | FastClaim | **Improvement** |
|------------|-------------------|-----------|-----------------|
| **Salah input nomor SEP** | 5-10% | 0% | Auto-extract dari PDF |
| **Nama pasien typo** | 3-5% | 0% | Auto-extract dari PDF |
| **File salah folder** | 3-5% | 0% | Auto-organize berdasarkan aturan |
| **Lupa backup** | 10-20% | 0% | Auto-backup setiap upload |
| **Struktur folder tidak konsisten** | 20-30% | 0% | Enforced standardization |

**Waktu Terbuang untuk Error:**
- Manual: ~3-6 jam/bulan untuk redo error
- FastClaim: ~0 jam (error prevention)

---

## 🏆 Summary

### **400 Klaim per Bulan:**

| Metrik | Manual | FastClaim | **Improvement** |
|--------|--------|-----------|-----------------|
| **Waktu Total** | 36.7 jam (4.6 hari) | 8.3 jam (1 hari) | **🚀 77% lebih cepat** |
| **Biaya Waktu** | Rp 625.551 | Rp 141.474 | **💰 Hemat Rp 484K/bulan** |
| **Hari Produktif Tersisa** | 17.4 hari | 21 hari | **📈 +3.6 hari produktif** |
| **Error Rate** | 5-10% | <1% | **✅ 95-99% akurat** |
| **Kapasitas per Hari** | 87 klaim | 400 klaim | **⚡ 4.6x lebih produktif** |

---

### **Per Tahun (4,800 Klaim):**

| Metrik | Manual | FastClaim | **Benefit** |
|--------|--------|-----------|-------------|
| **Waktu Total** | 440 jam (55 hari) | 100 jam (12.5 hari) | **Hemat 340 jam** |
| **Biaya Waktu** | Rp 7.5 juta | Rp 1.7 juta | **Hemat Rp 5.8 juta** |
| **Investasi** | - | Rp 1.5 juta | **ROI 287%** |
| **Break-Even** | - | 3.1 bulan | **Balik modal <4 bulan** |

---

## 💡 Value Proposition

> **"FastClaim menghemat 28.4 jam per bulan (setara 3.6 hari kerja produktif) untuk mengelola 400 klaim BPJS. Dengan investasi Rp 1.5 juta/tahun, fasilitas kesehatan dapat menghemat Rp 5.8 juta biaya waktu, meningkatkan kapasitas 360%, dan mencapai ROI 287% dengan periode balik modal hanya 3 bulan."**

---

## 📝 Notes

**Catatan Perhitungan:**
- Perhitungan ini berdasarkan **proses aktual** tanpa input data ke Excel/form terpisah
- **Overhead folder tahunan/bulanan** diabaikan karena frekuensinya rendah (1-2x per bulan)
- **Folder tanggal** overhead diperhitungkan minimal karena 1 folder untuk 10-20 klaim
- **Folder nomor SEP** unik per klaim sehingga dihitung penuh
- Error rate berdasarkan pengalaman umum di fasilitas kesehatan

**Asumsi Volume:**
- 400 klaim/bulan = rata-rata ~18-20 klaim per hari kerja
- Volume ini realistis untuk klinik pratama/utama atau puskesmas menengah
- Untuk RS dengan volume lebih tinggi, benefit akan lebih besar lagi

**Catatan Harga:**
- Harga Rp 1.5 juta/tahun adalah estimasi untuk tier Professional
- Tier lain mungkin berbeda, tapi ROI tetap signifikan positif

---

**Tanggal Analisis:** 11 Desember 2025  
**Versi:** 1.0  
**Status:** Final

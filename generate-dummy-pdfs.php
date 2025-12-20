<?php

require __DIR__.'/vendor/autoload.php';

use setasign\Fpdi\Fpdi;

/**
 * Generate dummy PDF files for testing BPJS claim system
 * These PDFs are created without advanced compression to ensure compatibility with FPDI
 */

// Ensure output directory exists
$outputDir = __DIR__.'/storage/app/public/dummy-pdfs';
if (! is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

// 1. Generate SEP (Surat Eligibilitas Peserta) - Sample
echo "Generating SEP-DUMMY.pdf...\n";
$pdf = new Fpdi();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'SURAT ELIGIBILITAS PESERTA (SEP)', 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('Arial', '', 11);
$pdf->Cell(50, 7, 'No. SEP', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, '0069G0020212X123426', 0, 1);

$pdf->Cell(50, 7, 'Tgl. SEP', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, '01-01-2021', 0, 1);

$pdf->Cell(50, 7, 'No. Kartu BPJS', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, '0009999999999', 0, 1);

$pdf->Cell(50, 7, 'No. RM', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, '0242424', 0, 1);

$pdf->Cell(50, 7, 'Nama Peserta', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, 'Jep Besos', 0, 1);

$pdf->Cell(50, 7, 'Kelas Rawat', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, 'Kelas 3', 0, 1);

$pdf->Cell(50, 7, 'Jenis Rawatan', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, 'Rawat Jalan (RJ)', 0, 1);

$pdf->Ln(10);
$pdf->SetFont('Arial', 'I', 9);
$pdf->MultiCell(0, 5, 'Catatan: Ini adalah dokumen SEP dummy untuk keperluan testing sistem. Data yang tertera adalah data fiktif dan tidak mencerminkan data pasien sebenarnya.');

$pdf->Output('F', $outputDir.'/SEP-DUMMY.pdf');
echo "✓ SEP-DUMMY.pdf created\n";

// 2. Generate RESUME (Resume Medis)
echo "Generating RESUME-DUMMY.pdf...\n";
$pdf = new Fpdi();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'RESUME MEDIS PASIEN', 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 7, 'Data Pasien', 0, 1);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(50, 6, 'Nama', 0, 0);
$pdf->Cell(5, 6, ':', 0, 0);
$pdf->Cell(0, 6, 'Jep Besos', 0, 1);
$pdf->Cell(50, 6, 'No. RM', 0, 0);
$pdf->Cell(5, 6, ':', 0, 0);
$pdf->Cell(0, 6, '0242424', 0, 1);
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 7, 'Diagnosa', 0, 1);
$pdf->SetFont('Arial', '', 11);
$pdf->MultiCell(0, 6, "Diagnosa Utama: Gastritis Akut (K29.0)\nDiagnosa Sekunder: Hipertensi Stage 1 (I10)");
$pdf->Ln(3);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 7, 'Anamnesa', 0, 1);
$pdf->SetFont('Arial', '', 11);
$pdf->MultiCell(0, 6, 'Pasien mengeluh nyeri ulu hati sejak 3 hari yang lalu, disertai mual dan muntah. Pasien memiliki riwayat hipertensi yang tidak terkontrol.');
$pdf->Ln(3);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 7, 'Pemeriksaan Fisik', 0, 1);
$pdf->SetFont('Arial', '', 11);
$pdf->MultiCell(0, 6, "TD: 150/95 mmHg\nNadi: 88 x/menit\nRR: 20 x/menit\nSuhu: 36.8°C\nAbdomen: Nyeri tekan epigastrium (+)");
$pdf->Ln(3);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 7, 'Terapi', 0, 1);
$pdf->SetFont('Arial', '', 11);
$pdf->MultiCell(0, 6, "1. Omeprazole 20mg 2x1\n2. Antasida Syr 3x1 C\n3. Amlodipin 5mg 1x1\n4. Diet rendah garam");
$pdf->Ln(5);

$pdf->SetFont('Arial', 'I', 9);
$pdf->MultiCell(0, 5, 'Dokumen dummy untuk testing. Data adalah fiktif.');

$pdf->Output('F', $outputDir.'/RESUME-DUMMY.pdf');
echo "✓ RESUME-DUMMY.pdf created\n";

// 3. Generate BILLING (Rincian Biaya)
echo "Generating BILLING-DUMMY.pdf...\n";
$pdf = new Fpdi();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'RINCIAN BIAYA PELAYANAN', 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('Arial', '', 11);
$pdf->Cell(50, 7, 'No. SEP', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, '0069G0020212X123426', 0, 1);
$pdf->Cell(50, 7, 'Nama Pasien', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, 'Jep Besos', 0, 1);
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(120, 7, 'URAIAN', 1, 0, 'C');
$pdf->Cell(30, 7, 'QTY', 1, 0, 'C');
$pdf->Cell(40, 7, 'BIAYA (Rp)', 1, 1, 'C');

$pdf->SetFont('Arial', '', 10);
$items = [
    ['Konsultasi Dokter Spesialis', '1', '150.000'],
    ['Obat Omeprazole 20mg', '14', '28.000'],
    ['Obat Antasida Syr', '3', '15.000'],
    ['Obat Amlodipin 5mg', '30', '30.000'],
    ['Pemeriksaan Laboratorium', '1', '125.000'],
    ['Jasa Pelayanan', '1', '50.000'],
];

$total = 0;
foreach ($items as $item) {
    $pdf->Cell(120, 6, $item[0], 1);
    $pdf->Cell(30, 6, $item[1], 1, 0, 'C');
    $pdf->Cell(40, 6, number_format((int) str_replace('.', '', $item[2])), 1, 1, 'R');
    $total += (int) str_replace('.', '', $item[2]);
}

$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(150, 7, 'TOTAL BIAYA', 1, 0, 'R');
$pdf->Cell(40, 7, number_format($total), 1, 1, 'R');

$pdf->Ln(5);
$pdf->SetFont('Arial', 'I', 9);
$pdf->MultiCell(0, 5, 'Dokumen dummy untuk testing. Data biaya adalah fiktif.');

$pdf->Output('F', $outputDir.'/BILLING-DUMMY.pdf');
echo "✓ BILLING-DUMMY.pdf created\n";

// 4. Generate LIP (Laporan Individu Pasien)
echo "Generating LIP-DUMMY.pdf...\n";
$pdf = new Fpdi();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'LAPORAN INDIVIDU PASIEN (LIP)', 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('Arial', '', 11);
$pdf->Cell(50, 7, 'No. SEP', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, '0069G0020212X123426', 0, 1);

$pdf->Cell(50, 7, 'Nama Pasien', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, 'Jep Besos', 0, 1);

$pdf->Cell(50, 7, 'No. RM', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, '0242424', 0, 1);

$pdf->Cell(50, 7, 'Tanggal Pelayanan', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, '01 Januari 2021', 0, 1);
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 7, 'Ringkasan Pelayanan', 0, 1);
$pdf->SetFont('Arial', '', 11);
$pdf->MultiCell(0, 6, "Pasien datang dengan keluhan nyeri ulu hati. Dilakukan pemeriksaan fisik dan penunjang. Diberikan terapi medikamentosa dan edukasi diet. Pasien dianjurkan kontrol 1 minggu kemudian.\n\nPrognosis: Baik\nKondisi Pulang: Membaik");

$pdf->Ln(10);
$pdf->SetFont('Arial', 'I', 9);
$pdf->MultiCell(0, 5, 'Dokumen LIP dummy untuk testing sistem.');

$pdf->Output('F', $outputDir.'/LIP-DUMMY.pdf');
echo "✓ LIP-DUMMY.pdf created\n";

// 5. Generate LAB 1 (Hasil Lab - Hematologi)
echo "Generating LAB-1-DUMMY.pdf...\n";
$pdf = new Fpdi();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'HASIL PEMERIKSAAN LABORATORIUM', 0, 1, 'C');
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 7, 'Hematologi Rutin', 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('Arial', '', 11);
$pdf->Cell(50, 7, 'Nama Pasien', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, 'Jep Besos', 0, 1);
$pdf->Cell(50, 7, 'No. RM', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, '0242424', 0, 1);
$pdf->Cell(50, 7, 'Tgl. Pemeriksaan', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, '01 Januari 2021', 0, 1);
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(70, 7, 'PEMERIKSAAN', 1, 0, 'C');
$pdf->Cell(35, 7, 'HASIL', 1, 0, 'C');
$pdf->Cell(45, 7, 'NILAI RUJUKAN', 1, 0, 'C');
$pdf->Cell(40, 7, 'SATUAN', 1, 1, 'C');

$pdf->SetFont('Arial', '', 10);
$labResults = [
    ['Hemoglobin', '13.5', '12.0 - 16.0', 'g/dL'],
    ['Leukosit', '8.200', '4.000 - 10.000', '/uL'],
    ['Eritrosit', '4.8', '4.0 - 5.5', 'juta/uL'],
    ['Hematokrit', '42', '37 - 47', '%'],
    ['Trombosit', '285.000', '150.000 - 400.000', '/uL'],
    ['LED', '12', '0 - 20', 'mm/jam'],
];

foreach ($labResults as $result) {
    $pdf->Cell(70, 6, $result[0], 1);
    $pdf->Cell(35, 6, $result[1], 1, 0, 'C');
    $pdf->Cell(45, 6, $result[2], 1, 0, 'C');
    $pdf->Cell(40, 6, $result[3], 1, 1, 'C');
}

$pdf->Ln(5);
$pdf->SetFont('Arial', 'I', 9);
$pdf->MultiCell(0, 5, 'Hasil laboratorium dummy untuk testing. Data adalah fiktif.');

$pdf->Output('F', $outputDir.'/LAB-1-DUMMY.pdf');
echo "✓ LAB-1-DUMMY.pdf created\n";

// 6. Generate LAB 2 (Hasil Lab - Kimia Klinik)
echo "Generating LAB-2-DUMMY.pdf...\n";
$pdf = new Fpdi();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'HASIL PEMERIKSAAN LABORATORIUM', 0, 1, 'C');
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 7, 'Kimia Klinik', 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('Arial', '', 11);
$pdf->Cell(50, 7, 'Nama Pasien', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, 'Jep Besos', 0, 1);
$pdf->Cell(50, 7, 'No. RM', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, '0242424', 0, 1);
$pdf->Cell(50, 7, 'Tgl. Pemeriksaan', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, '01 Januari 2021', 0, 1);
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(70, 7, 'PEMERIKSAAN', 1, 0, 'C');
$pdf->Cell(35, 7, 'HASIL', 1, 0, 'C');
$pdf->Cell(45, 7, 'NILAI RUJUKAN', 1, 0, 'C');
$pdf->Cell(40, 7, 'SATUAN', 1, 1, 'C');

$pdf->SetFont('Arial', '', 10);
$labResults = [
    ['Glukosa Puasa', '95', '70 - 100', 'mg/dL'],
    ['Ureum', '28', '10 - 50', 'mg/dL'],
    ['Kreatinin', '0.9', '0.6 - 1.2', 'mg/dL'],
    ['SGOT', '24', '< 35', 'U/L'],
    ['SGPT', '28', '< 45', 'U/L'],
    ['Kolesterol Total', '185', '< 200', 'mg/dL'],
];

foreach ($labResults as $result) {
    $pdf->Cell(70, 6, $result[0], 1);
    $pdf->Cell(35, 6, $result[1], 1, 0, 'C');
    $pdf->Cell(45, 6, $result[2], 1, 0, 'C');
    $pdf->Cell(40, 6, $result[3], 1, 1, 'C');
}

$pdf->Ln(5);
$pdf->SetFont('Arial', 'I', 9);
$pdf->MultiCell(0, 5, 'Hasil laboratorium dummy untuk testing. Data adalah fiktif.');

$pdf->Output('F', $outputDir.'/LAB-2-DUMMY.pdf');
echo "✓ LAB-2-DUMMY.pdf created\n";

echo "\n========================================\n";
echo "✓ All dummy PDF files generated successfully!\n";
echo "Location: {$outputDir}\n";
echo "========================================\n";
echo "\nFiles created:\n";
echo "1. SEP-DUMMY.pdf (Surat Eligibilitas Peserta)\n";
echo "2. RESUME-DUMMY.pdf (Resume Medis)\n";
echo "3. BILLING-DUMMY.pdf (Rincian Biaya)\n";
echo "4. LIP-DUMMY.pdf (Laporan Individu Pasien)\n";
echo "5. LAB-1-DUMMY.pdf (Hasil Lab Hematologi)\n";
echo "6. LAB-2-DUMMY.pdf (Hasil Lab Kimia Klinik)\n";
echo "\nThese PDFs are compatible with FPDI and ready for testing!\n";

<?php

require __DIR__.'/vendor/autoload.php';

use setasign\Fpdi\Fpdi;

/**
 * Generate realistic SEP (Surat Eligibilitas Peserta) PDF
 * Mimicking the actual BPJS SEP document structure
 */

class SepGenerator
{
    protected Fpdi $pdf;
    protected array $data;

    public function __construct(array $data = [])
    {
        $this->pdf = new Fpdi();
        $this->pdf->SetCompression(false); // Ensure FPDI compatibility
        
        // Default data
        $this->data = array_merge([
            'no_sep' => '0069G0020212X123426',
            'tgl_sep' => '01-01-2021',
            'no_kartu' => '0009999999999',
            'no_rm' => '0242424',
            'nama_peserta' => 'Jep Besos',
            'tgl_lahir' => '15-05-1985',
            'jenis_kelamin' => 'Laki-laki',
            'poli_tujuan' => 'Poli Penyakit Dalam',
            'asal_faskes' => 'Puskesmas Kecamatan',
            'diagnosa' => 'K29.0 - Gastritis Akut',
            'catatan' => 'Kontrol rutin',
            'no_telp' => '081234567890',
            'kelas_rawat' => 'Kelas 3',
            'hak_kelas' => 'Kelas 3',
            'jenis_rawat' => 'Rawat Jalan',
            'jenis_pelayanan' => 'Rawat Jalan',
            'keluhan' => 'Nyeri ulu hati',
            'penjamin' => 'BPJS Kesehatan',
            'tgl_pelayanan' => '01-01-2021',
            'tgl_rujukan' => '28-12-2020',
            'ppk_pelayanan' => 'RSUD Kota',
            'ppk_rujukan' => 'Puskesmas Kecamatan',
            'no_rujukan' => '001/RUJ/XII/2020',
        ], $data);
    }

    public function generate(): void
    {
        $this->pdf->AddPage();
        
        // Header
        $this->drawHeader();
        
        // Title
        $this->drawTitle();
        
        // Data Peserta Section
        $this->drawDataPeserta();
        
        // Data Pelayanan Section
        $this->drawDataPelayanan();
        
        // Data Rujukan Section
        $this->drawDataRujukan();
        
        // Footer
        $this->drawFooter();
    }

    protected function drawHeader(): void
    {
        $this->pdf->SetFont('Arial', 'B', 14);
        $this->pdf->Cell(0, 8, 'BPJS KESEHATAN', 0, 1, 'C');
        $this->pdf->SetFont('Arial', '', 10);
        $this->pdf->Cell(0, 5, 'Badan Penyelenggara Jaminan Sosial Kesehatan', 0, 1, 'C');
        $this->pdf->Ln(3);
        
        // Line separator
        $this->pdf->SetLineWidth(0.5);
        $this->pdf->Line(10, $this->pdf->GetY(), 200, $this->pdf->GetY());
        $this->pdf->Ln(5);
    }

    protected function drawTitle(): void
    {
        $this->pdf->SetFont('Arial', 'B', 13);
        $this->pdf->Cell(0, 8, 'SURAT ELIGIBILITAS PESERTA (SEP)', 0, 1, 'C');
        $this->pdf->Ln(2);
        
        // SEP Number and Date - format yang bisa di-parse
        $this->pdf->SetFont('Arial', '', 10);
        $sepDate = date('Y-m-d', strtotime(str_replace('-', '/', $this->data['tgl_sep'])));
        
        // Single line format: No.SEP : value    Tgl.SEP : value
        $line = 'No.SEP : '.$this->data['no_sep'].'    Tgl.SEP : '.$sepDate;
        $this->pdf->Cell(0, 6, $line, 0, 1, 'C');
        
        $this->pdf->Ln(3);
    }

    protected function drawDataPeserta(): void
    {
        $this->pdf->SetFont('Arial', 'B', 11);
        $this->pdf->SetFillColor(230, 230, 230);
        $this->pdf->Cell(0, 7, 'I. DATA PESERTA', 0, 1, 'L');
        $this->pdf->Ln(2);
        
        $this->pdf->SetFont('Arial', '', 10);
        
        // Critical format for parser: No.Kartu : 0009999999999 (MR. 0242424) : Nama Peserta
        $this->pdf->MultiCell(0, 5, 'No.Kartu : '.$this->data['no_kartu'].' (MR. '.$this->data['no_rm'].') : '.$this->data['nama_peserta']);
        $this->pdf->Ln(1);
        
        $fields = [
            ['Tanggal Lahir', $this->data['tgl_lahir']],
            ['Jenis Kelamin', $this->data['jenis_kelamin']],
            ['No. Telp/HP', $this->data['no_telp']],
            ['Hak Kelas', $this->data['hak_kelas']],
            ['Kelas Rawat', $this->extractKelasNumber($this->data['kelas_rawat'])],
        ];

        foreach ($fields as $field) {
            $this->pdf->Cell(50, 5, $field[0], 0, 0);
            $this->pdf->Cell(3, 5, ':', 0, 0);
            $this->pdf->Cell(0, 5, $field[1], 0, 1);
        }
        
        $this->pdf->Ln(3);
    }
    
    protected function extractKelasNumber(string $kelas): string
    {
        if (preg_match('/(\d)/', $kelas, $m)) {
            return $m[1];
        }
        return '3'; // default
    }

    protected function drawDataPelayanan(): void
    {
        $this->pdf->SetFont('Arial', 'B', 11);
        $this->pdf->SetFillColor(230, 230, 230);
        $this->pdf->Cell(0, 7, 'II. DATA PELAYANAN', 0, 1, 'L');
        $this->pdf->Ln(2);
        
        $this->pdf->SetFont('Arial', '', 10);
        
        // Determine jenis rawat text
        $jenisRawatText = 'R.Jalan';
        if (stripos($this->data['jenis_rawat'], 'Inap') !== false) {
            $jenisRawatText = 'R.Inap';
        }
        
        $fields = [
            ['Tanggal Pelayanan', $this->data['tgl_pelayanan']],
            ['Jenis Rawat', $jenisRawatText],
            ['Poli Tujuan', $this->data['poli_tujuan']],
            ['PPK Pelayanan', $this->data['ppk_pelayanan']],
            ['Diagnosa', $this->data['diagnosa']],
            ['Keluhan', $this->data['keluhan']],
            ['Catatan', $this->data['catatan']],
        ];

        foreach ($fields as $field) {
            $this->pdf->Cell(50, 5, $field[0], 0, 0);
            $this->pdf->Cell(3, 5, ':', 0, 0);
            $this->pdf->Cell(0, 5, $field[1], 0, 1);
        }
        
        $this->pdf->Ln(3);
    }

    protected function drawDataRujukan(): void
    {
        $this->pdf->SetFont('Arial', 'B', 11);
        $this->pdf->SetFillColor(230, 230, 230);
        $this->pdf->Cell(0, 7, 'III. DATA RUJUKAN', 1, 1, 'L', true);
        
        $this->pdf->SetFont('Arial', '', 10);
        
        $fields = [
            ['No. Rujukan', $this->data['no_rujukan']],
            ['Tanggal Rujukan', $this->data['tgl_rujukan']],
            ['PPK Rujukan', $this->data['ppk_rujukan']],
            ['Asal Faskes', $this->data['asal_faskes']],
        ];

        foreach ($fields as $field) {
            $this->pdf->Cell(60, 6, $field[0], 1, 0);
            $this->pdf->Cell(5, 6, ':', 0, 0, 'C');
            $this->pdf->Cell(0, 6, $field[1], 1, 1);
        }
        
        $this->pdf->Ln(5);
    }

    protected function drawFooter(): void
    {
        $this->pdf->SetFont('Arial', 'I', 9);
        $this->pdf->SetTextColor(100, 100, 100);
        $this->pdf->MultiCell(0, 5, 
            "CATATAN:\n".
            "1. SEP ini adalah bukti pelayanan kesehatan yang dijamin oleh BPJS Kesehatan\n".
            "2. Harap disimpan dengan baik sebagai bukti pelayanan\n".
            "3. SEP ini sah untuk satu kali pelayanan\n".
            "4. Untuk informasi lebih lanjut hubungi BPJS Care Center 1500 400"
        );
        
        $this->pdf->Ln(5);
        $this->pdf->SetFont('Arial', 'I', 8);
        $this->pdf->SetTextColor(150, 150, 150);
        $this->pdf->Cell(0, 5, 'Dokumen ini dicetak secara otomatis oleh sistem BPJS Kesehatan', 0, 1, 'C');
        $this->pdf->Cell(0, 5, 'Tanggal Cetak: '.date('d-m-Y H:i:s'), 0, 1, 'C');
    }

    public function save(string $path): void
    {
        $this->pdf->Output('F', $path);
    }

    public function output(): void
    {
        $this->pdf->Output('I', 'SEP-'.$this->data['no_sep'].'.pdf');
    }
}

// Generate default SEP
echo "Generating realistic SEP-DUMMY-REAL.pdf...\n\n";

$outputDir = __DIR__.'/storage/app/public/dummy-pdfs';
if (! is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

$generator = new SepGenerator();
$generator->generate();
$generator->save($outputDir.'/SEP-DUMMY-REAL.pdf');

echo "✓ SEP-DUMMY-REAL.pdf created successfully!\n";
echo "Location: {$outputDir}/SEP-DUMMY-REAL.pdf\n\n";

// Generate multiple variations for testing
echo "Generating additional SEP variations...\n\n";

$variations = [
    [
        'no_sep' => '0069G0020212X999888',
        'nama_peserta' => 'Budi Santoso',
        'no_kartu' => '0001234567890',
        'no_rm' => '098765',
        'diagnosa' => 'I10 - Hipertensi Esensial',
        'poli_tujuan' => 'Poli Jantung',
    ],
    [
        'no_sep' => '0069G0020212X777666',
        'nama_peserta' => 'Siti Nurhaliza',
        'no_kartu' => '0009876543210',
        'no_rm' => '123456',
        'diagnosa' => 'E11 - Diabetes Melitus Tipe 2',
        'poli_tujuan' => 'Poli Endokrin',
        'jenis_kelamin' => 'Perempuan',
    ],
];

foreach ($variations as $index => $data) {
    $num = $index + 2;
    $generator = new SepGenerator($data);
    $generator->generate();
    $generator->save($outputDir."/SEP-DUMMY-{$num}.pdf");
    echo "✓ SEP-DUMMY-{$num}.pdf created (No. SEP: {$data['no_sep']})\n";
}

echo "\n========================================\n";
echo "✓ All SEP variations generated!\n";
echo "========================================\n";
echo "\nGenerated files:\n";
echo "1. SEP-DUMMY-REAL.pdf - Primary test file\n";
echo "2. SEP-DUMMY-2.pdf - Variation with different patient\n";
echo "3. SEP-DUMMY-3.pdf - Variation with female patient\n";
echo "\nThese PDFs closely match the actual BPJS SEP format!\n";

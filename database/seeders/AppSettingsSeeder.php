<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AppSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Clinic Settings
            [
                'key' => 'clinic_name',
                'value' => 'Klinik Utama Bukit Raya',
                'type' => 'string',
                'group' => 'clinic',
                'description' => 'Nama Klinik/Faskes',
            ],
            [
                'key' => 'clinic_code',
                'value' => '',
                'type' => 'string',
                'group' => 'clinic',
                'description' => 'Kode BPJS Faskes',
            ],
            [
                'key' => 'clinic_address',
                'value' => '',
                'type' => 'string',
                'group' => 'clinic',
                'description' => 'Alamat Klinik',
            ],
            [
                'key' => 'clinic_phone',
                'value' => '',
                'type' => 'string',
                'group' => 'clinic',
                'description' => 'Nomor Telepon',
            ],

            // Storage Settings
            [
                'key' => 'folder_shared',
                'value' => 'Z:/FOLDER KLAIM REGULER BPJS SINTA',
                'type' => 'string',
                'group' => 'storage',
                'description' => 'Path folder penyimpanan utama',
            ],
            [
                'key' => 'folder_backup',
                'value' => 'D:/Backup Folder Klaim BPJS/Folder Klaim Reguler BPJS',
                'type' => 'string',
                'group' => 'storage',
                'description' => 'Path folder backup',
            ],
            [
                'key' => 'auto_backup',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'storage',
                'description' => 'Aktifkan backup otomatis',
            ],

            // General Settings
            [
                'key' => 'app_name',
                'value' => 'FastClaim',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Nama Aplikasi',
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('app_settings')->insert(array_merge($setting, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}

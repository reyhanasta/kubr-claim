<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, boolean, integer, json
            $table->string('group')->default('general'); // general, clinic, storage
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        $defaults = [
            // Clinic Settings
            ['key' => 'clinic_name', 'value' => 'Klinik Utama Bunda Restu', 'type' => 'string', 'group' => 'clinic', 'description' => 'Nama Klinik/Faskes'],
            ['key' => 'clinic_code', 'value' => '', 'type' => 'string', 'group' => 'clinic', 'description' => 'Kode BPJS Faskes'],
            ['key' => 'clinic_address', 'value' => '', 'type' => 'string', 'group' => 'clinic', 'description' => 'Alamat Klinik'],
            ['key' => 'clinic_phone', 'value' => '', 'type' => 'string', 'group' => 'clinic', 'description' => 'Nomor Telepon'],

            // Storage Settings
            ['key' => 'folder_shared', 'value' => 'Z:/FOLDER KLAIM REGULER BPJS SINTA', 'type' => 'string', 'group' => 'storage', 'description' => 'Path folder penyimpanan utama'],
            ['key' => 'folder_backup', 'value' => 'D:/Backup Folder Klaim BPJS/Folder Klaim Reguler BPJS', 'type' => 'string', 'group' => 'storage', 'description' => 'Path folder backup'],
            ['key' => 'auto_backup', 'value' => '1', 'type' => 'boolean', 'group' => 'storage', 'description' => 'Aktifkan backup otomatis'],

            // General Settings
            ['key' => 'app_name', 'value' => 'FastClaim', 'type' => 'string', 'group' => 'general', 'description' => 'Nama Aplikasi'],
        ];

        foreach ($defaults as $setting) {
            DB::table('app_settings')->insert(array_merge($setting, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};

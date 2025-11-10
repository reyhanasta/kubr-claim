<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bpjs_claims', function (Blueprint $table) {
            // Add indexes for commonly queried fields
            $table->index(['tanggal_rawatan', 'jenis_rawatan'], 'idx_tanggal_jenis');
            $table->index('no_sep', 'idx_no_sep');
            $table->index('no_rm', 'idx_no_rm');
            $table->index('created_at', 'idx_created_at');
        });

        Schema::table('claim_documents', function (Blueprint $table) {
            $table->index('bpjs_claims_id', 'idx_claim_id');
            $table->index('order', 'idx_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bpjs_claims', function (Blueprint $table) {
            $table->dropIndex('idx_tanggal_jenis');
            $table->dropIndex('idx_no_sep');
            $table->dropIndex('idx_no_rm');
            $table->dropIndex('idx_created_at');
        });

        Schema::table('claim_documents', function (Blueprint $table) {
            $table->dropIndex('idx_claim_id');
            $table->dropIndex('idx_order');
        });
    }
};

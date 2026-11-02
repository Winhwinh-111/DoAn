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
        Schema::create('muontra', function (Blueprint $table) {
            $table->string('ma_giao_dich', 30)->primary();
            $table->string('mssv', 12);
            $table->string('ma_tai_lieu', 30)->index('fk_muontra_tailieu');
            $table->dateTime('ngay_muon')->nullable()->useCurrent();
            $table->dateTime('ngay_tra')->nullable();
            $table->string('trang_thai', 50)->nullable()->default('DangMuon');
            $table->string('ghi_chu', 500)->nullable();

            $table->index(['mssv', 'ma_tai_lieu'], 'idx_muontra_mssv_tailieu');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('muontra');
    }
};

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
        Schema::create('phanhoi', function (Blueprint $table) {
            $table->string('ma_phan_hoi', 30)->primary();
            $table->string('mssv', 12);
            $table->string('ma_tai_lieu', 30)->index('fk_phanhoi_tailieu');
            $table->string('muc_danh_gia', 50);
            $table->dateTime('thoi_diem')->nullable()->useCurrent();

            $table->index(['mssv', 'ma_tai_lieu'], 'idx_phanhoi_mssv_tailieu');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phanhoi');
    }
};

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
        Schema::create('danhdau', function (Blueprint $table) {
            $table->string('ma_danh_dau', 30)->primary();
            $table->string('mssv', 12);
            $table->string('ma_tai_lieu', 30)->index('fk_danhdau_tailieu');
            $table->dateTime('thoi_diem_danh_dau')->nullable()->useCurrent();

            $table->unique(['mssv', 'ma_tai_lieu'], 'uq_sinhvien_tailieu_danhdau');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('danhdau');
    }
};

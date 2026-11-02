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
        Schema::create('tailieu', function (Blueprint $table) {
            $table->string('ma_tai_lieu', 30)->primary();
            $table->string('nhan_de', 300)->index('idx_tailieu_nhande');
            $table->string('tac_gia', 250)->nullable();
            $table->string('nha_xuat_ban', 150)->nullable();
            $table->integer('nam_xuat_ban')->nullable();
            $table->string('ngon_ngu', 30)->nullable()->default('Tiếng Việt');
            $table->string('isbn', 30)->nullable();
            $table->longText('thuat_ngu_chu_de')->nullable();
            $table->longText('mo_ta')->nullable();
            $table->string('noi_luu_giu', 150)->nullable();
            $table->string('trang_thai_su_dung', 50)->nullable()->default('Co_San');
            $table->string('anh_bia', 255)->nullable();

            $table->fullText(['nhan_de', 'thuat_ngu_chu_de', 'mo_ta'], 'ft_tailieu_noidung');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tailieu');
    }
};

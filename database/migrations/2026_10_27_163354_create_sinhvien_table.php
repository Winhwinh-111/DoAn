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
        Schema::create('sinhvien', function (Blueprint $table) {
            $table->string('mssv', 12)->primary();
            $table->string('ho_ten', 100);
            $table->date('ngay_sinh')->nullable();
            $table->string('gioi_tinh', 10)->nullable();
            $table->string('so_dien_thoai', 15)->nullable();
            $table->date('han_the')->nullable();
            $table->string('ma_nganh', 20)->index('fk_sinhvien_nganhhoc');
            $table->string('ma_tai_khoan', 30)->unique('ma_tai_khoan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sinhvien');
    }
};

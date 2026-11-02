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
        Schema::create('taikhoan', function (Blueprint $table) {
            $table->string('ma_tai_khoan', 30)->primary();
            $table->string('ten_dang_nhap', 50)->unique('ten_dang_nhap');
            $table->string('mat_khau', 255);
            $table->enum('vai_tro', ['SinhVien', 'QuanTriThuVien', 'QuanTriHeThong']);
            $table->dateTime('ngay_tao')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taikhoan');
    }
};

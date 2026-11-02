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
        Schema::create('mohinhgoiy', function (Blueprint $table) {
            $table->string('ma_mo_hinh', 30)->primary();
            $table->string('ma_tai_khoan', 30)->index('fk_mohinh_taikhoan');
            $table->string('ma_cau_hinh', 30)->index('fk_mohinh_cauhinh');
            $table->dateTime('thoi_diem_tao')->nullable()->useCurrent();
            $table->string('duong_dan_tep', 255);
            $table->boolean('la_hien_hanh')->nullable()->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mohinhgoiy');
    }
};

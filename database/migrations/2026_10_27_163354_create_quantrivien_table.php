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
        Schema::create('quantrivien', function (Blueprint $table) {
            $table->string('ma_quan_tri', 20)->primary();
            $table->string('ho_ten', 100);
            $table->string('email', 100)->nullable();
            $table->string('so_dien_thoai', 15)->nullable();
            $table->string('chuc_vu', 100)->nullable();
            $table->string('ma_tai_khoan', 30)->unique('ma_tai_khoan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quantrivien');
    }
};

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
        Schema::create('lichsutimkiem', function (Blueprint $table) {
            $table->string('ma_luot_tim_kiem', 30)->primary();
            $table->string('mssv', 12)->index('idx_lstk_mssv');
            $table->string('tu_khoa', 255)->index('idx_lstk_tukhoa');
            $table->dateTime('thoi_diem_tim_kiem')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lichsutimkiem');
    }
};

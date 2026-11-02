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
        Schema::create('cauhinhgoiy', function (Blueprint $table) {
            $table->string('ma_cau_hinh', 30)->primary();
            $table->string('ma_tai_khoan', 30)->index('fk_cauhinh_taikhoan');
            $table->integer('top_n')->default(10);
            $table->float('nguong_cosine')->default(0.15);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cauhinhgoiy');
    }
};

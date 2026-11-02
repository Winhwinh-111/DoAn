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
        Schema::create('decuonghocphan', function (Blueprint $table) {
            $table->string('ma_hoc_phan', 20);
            $table->string('ma_tai_lieu', 30)->index('fk_decuong_tailieu');
            $table->longText('noi_dung_de_cuong')->nullable();
            $table->string('loai_tai_lieu', 50)->nullable()->default('ThamKhao');

            $table->primary(['ma_hoc_phan', 'ma_tai_lieu']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('decuonghocphan');
    }
};

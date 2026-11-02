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
        Schema::create('sinhvienhocphan', function (Blueprint $table) {
            $table->string('mssv', 12);
            $table->string('ma_hoc_phan', 20)->index('fk_svhp_hocphan');
            $table->string('trang_thai', 50)->nullable()->default('DangHoc');

            $table->primary(['mssv', 'ma_hoc_phan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sinhvienhocphan');
    }
};

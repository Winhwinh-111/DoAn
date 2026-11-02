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
        Schema::create('chuongtrinhdaotao', function (Blueprint $table) {
            $table->string('ma_nganh', 20);
            $table->string('ma_hoc_phan', 20)->index('fk_ctdt_hocphan');

            $table->primary(['ma_nganh', 'ma_hoc_phan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chuongtrinhdaotao');
    }
};

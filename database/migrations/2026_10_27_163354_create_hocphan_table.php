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
        Schema::create('hocphan', function (Blueprint $table) {
            $table->string('ma_hoc_phan', 20)->primary();
            $table->string('ten_hoc_phan', 200);
            $table->integer('so_tin_chi')->default(3);
            $table->longText('mo_ta')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hocphan');
    }
};

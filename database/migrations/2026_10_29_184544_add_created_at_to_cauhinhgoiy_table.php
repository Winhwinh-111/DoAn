<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('CauHinhGoiY', function (Blueprint $table) {
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        // Gán thời điểm cho các cấu hình cũ để có thể sắp xếp.
        DB::table('CauHinhGoiY')
            ->whereNull('created_at')
            ->update(['created_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('CauHinhGoiY', function (Blueprint $table) {
            $table->dropColumn('created_at');
        });
    }
};

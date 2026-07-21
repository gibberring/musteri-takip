<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 'Servis İşlemleri' (ID 5) kaydının 'yon' sütununu 0 olarak güncelliyoruz.
        DB::table('kasa_odeme_turu')
            ->where('id', 5) 
            ->update(['yon' => 0, 'updated_at' => now()]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Değişikliği geri almak için 'yon' sütununu orijinal değeri olan 1'e döndürüyoruz.
        DB::table('kasa_odeme_turu')
            ->where('id', 5) 
            ->update(['yon' => 1, 'updated_at' => now()]);
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $idsToDelete = [9787, 9786, 9784, 9526];
        DB::table('servis_durum')->whereIn('id', $idsToDelete)->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Belirli ID'lere sahip verilerin silinmesini geri almak için,
        // bu kayıtların orijinal değerlerini bilmek ve yeniden eklemek gerekir.
        // Bu migrasyon, bu tür bir geri yükleme işlemini otomatik olarak yapmamaktadır.
        // Gerekirse, bu ID'lere sahip kayıtlar manuel olarak veya başka bir yöntemle eklenebilir.
        // Örnek: DB::table('servis_durum')->insert([['id' => 9787, 'ad' => 'eski deger' ...], ...]);
    }
}; 
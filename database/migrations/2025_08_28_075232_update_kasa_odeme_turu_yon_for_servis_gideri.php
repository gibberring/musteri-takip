<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\KasaOdemeTuru; // KasaOdemeTuru modelini kullanmak için eklendi

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // "Servis Gideri" adlı ödeme türünün 'yon' değerini -1 olarak güncelle
        KasaOdemeTuru::where('ad', 'Servis Gideri')->update(['yon' => -1]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 'yon' değerini eski haline getir (eğer -1 olarak güncellenmişse)
        KasaOdemeTuru::where('ad', 'Servis Gideri')->update(['yon' => 0]); // Eski varsayılan değer 0 olduğunu varsayıyorum
    }
};

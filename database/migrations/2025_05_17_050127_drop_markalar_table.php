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
        Schema::dropIfExists('markalar');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // İsteğe bağlı: Tabloyu geri oluşturmak için kod eklenebilir
        // Schema::create('markalar', function (Blueprint $table) {
        //     $table->id();
        //     // Diğer kolonlar...
        //     $table->timestamps();
        // });
    }
};

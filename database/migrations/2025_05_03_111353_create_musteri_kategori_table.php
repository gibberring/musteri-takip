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
        Schema::create('musteri_kategori', function (Blueprint $table) {
            $table->id();
            $table->foreignId('uye_firma_id')->nullable()->constrained('its_uye_firmalar');
            $table->string('ad', 500)->nullable();
            $table->foreignId('ust_id')->nullable()->constrained('musteri_kategori');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('musteri_kategori');
    }
};

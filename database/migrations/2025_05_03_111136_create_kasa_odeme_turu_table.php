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
        Schema::create('kasa_odeme_turu', function (Blueprint $table) {
            $table->id();
            $table->foreignId('uye_firma_id')->nullable()->constrained('its_uye_firmalar');
            $table->string('ad', 50)->nullable();
            $table->string('muhattap', 50)->nullable();
            $table->integer('yon')->nullable();
            $table->integer('sira')->nullable();
            $table->integer('servisler_kategorisi')->nullable();
            $table->integer('stok_kategorisi')->nullable();
            $table->integer('garanti_geliri_kategorisi')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kasa_odeme_turu');
    }
};

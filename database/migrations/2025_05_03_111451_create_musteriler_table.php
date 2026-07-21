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
        Schema::create('musteriler', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('uye_firma_id')->nullable();
            $table->unsignedBigInteger('personel_id')->nullable();
            $table->integer('musteri_tip')->nullable();
            $table->string('ad', 500)->nullable();
            $table->string('tel1', 50)->nullable();
            $table->string('tel2', 50)->nullable();
            $table->unsignedBigInteger('il_id')->nullable();
            $table->unsignedBigInteger('ilce_id')->nullable();
            $table->string('adres', 500)->nullable();
            $table->string('vno', 50)->nullable();
            $table->string('vdaire', 50)->nullable();
            $table->string('tarih', 50)->nullable();
            $table->string('saat', 50)->nullable();
            $table->unsignedBigInteger('kat')->nullable();
            $table->integer('tedarikciler')->nullable();
            $table->integer('eski_id')->nullable();
            $table->integer('silindi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('musteriler');
    }
};

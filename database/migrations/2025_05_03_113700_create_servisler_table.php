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
        Schema::create('servisler', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('uye_firma_id')->nullable();
            $table->unsignedBigInteger('personel_id')->nullable();
            $table->string('tarih', 50)->nullable();
            $table->string('saat', 50)->nullable();
            $table->unsignedBigInteger('kaynak_id')->nullable();
            $table->unsignedBigInteger('musteri_id')->nullable();
            $table->unsignedBigInteger('marka_id')->nullable();
            $table->unsignedBigInteger('cihaz_tur_id')->nullable();
            $table->string('cihaz_model', 50)->nullable();
            $table->string('cihaz_arizasi', 500)->nullable();
            $table->string('operator_not', 500)->nullable();
            $table->unsignedBigInteger('servis_durum_id')->nullable();
            $table->string('musait_tarih', 50)->nullable();
            $table->string('musait_saat1', 50)->nullable();
            $table->string('musait_saat2', 50)->nullable();
            $table->string('seri_no', 50)->nullable();
            $table->integer('yazdirildi_mi')->nullable();
            $table->integer('eski_id')->nullable();
            $table->integer('silindi')->nullable();
            $table->unsignedBigInteger('silen_kisi_id')->nullable();
            $table->string('silinme_tarihi', 50)->nullable();
            $table->integer('kilitli')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servisler');
    }
};

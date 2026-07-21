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
        Schema::create('its_uye_firmalar', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('personel_id')->nullable();
            $table->string('firma_adi', 150)->nullable();
            $table->integer('aktif')->nullable();
            $table->string('tel1', 50)->nullable();
            $table->string('tel2', 50)->nullable();
            $table->string('email', 50)->nullable();
            $table->string('web', 50)->nullable();
            $table->string('adres', 250)->nullable();
            $table->unsignedBigInteger('ilce_id')->nullable();
            $table->unsignedBigInteger('il_id')->nullable();
            $table->string('tarih', 50)->nullable();
            $table->string('saat', 50)->nullable();
            $table->string('vno', 50)->nullable();
            $table->string('vdaire', 50)->nullable();
            $table->string('firma_faaliyet', 150)->nullable();
            $table->string('yazici_genisligi', 50)->nullable();
            $table->text('yazici_icerigi')->nullable();
            $table->text('masaustu_yazici_notlar')->nullable();
            $table->integer('sms_aktif')->nullable();
            $table->string('sms_firmasi', 50)->nullable();
            $table->string('sms_baslik', 50)->nullable();
            $table->string('sms_kullanici', 50)->nullable();
            $table->string('sms_sifre', 50)->nullable();
            $table->text('sms_taslak')->nullable();
            $table->integer('multifirma')->nullable();
            $table->string('fis_firma', 500)->nullable();
            $table->string('fis_tel', 50)->nullable();
            $table->string('fis_adres', 500)->nullable();
            $table->text('fis_aciklamalar')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('its_uye_firmalar');
    }
};

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
        Schema::create('personel', function (Blueprint $table) {
            $table->id()->startingValue(3000);
            $table->unsignedBigInteger('uye_firma_id')->nullable();
            $table->unsignedBigInteger('kaydeden_personel_id')->nullable();
            $table->string('ad', 50)->nullable();
            $table->string('nick', 50)->nullable();
            $table->string('sifre');
            $table->unsignedBigInteger('poz_id')->nullable();
            $table->integer('aktif')->nullable();
            $table->string('tarih', 50)->nullable();
            $table->string('saat', 50)->nullable();
            $table->string('son_giris', 50)->nullable();
            $table->integer('toplam_giris')->nullable();
            $table->string('tel1', 50)->nullable();
            $table->string('tel2', 50)->nullable();
            $table->unsignedBigInteger('ilce_id')->nullable();
            $table->unsignedBigInteger('il_id')->nullable();
            $table->string('adres', 500)->nullable();
            $table->string('resim', 50)->nullable();
            $table->string('vno', 50)->nullable();
            $table->string('is_basi_tarih', 50)->nullable();
            $table->string('email', 150)->nullable();
            $table->integer('servis_fis_kullanabilir')->nullable();
            $table->text('yazici_icerigi')->nullable();
            $table->string('yazici_genisligi', 50)->nullable();
            $table->integer('mesai_basladimi')->nullable();
            $table->string('fis_firma', 500)->nullable();
            $table->string('fis_tel', 50)->nullable();
            $table->string('fis_adres', 500)->nullable();
            $table->integer('e_fis_verebilir')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personel');
    }
};

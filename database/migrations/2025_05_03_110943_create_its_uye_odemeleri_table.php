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
        Schema::create('its_uye_odemeleri', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('uye_firma_id')->nullable();
            $table->unsignedBigInteger('personel_id')->nullable();
            $table->decimal('tutar', 18, 2)->nullable();
            $table->string('odeme_tarihi', 50)->nullable();
            $table->string('aciklama', 50)->nullable();
            $table->string('baslangic_tarih', 50)->nullable();
            $table->string('baslangic_saat', 50)->nullable();
            $table->string('bitis_tarih', 50)->nullable();
            $table->string('bitis_saat', 50)->nullable();
            $table->integer('aktif')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('its_uye_odemeleri');
    }
};

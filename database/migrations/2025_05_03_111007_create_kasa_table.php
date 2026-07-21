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
        Schema::create('kasa', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('uye_firma_id')->nullable();
            $table->unsignedBigInteger('personel_id')->nullable();
            $table->string('tarih', 50)->nullable();
            $table->string('saat', 50)->nullable();
            $table->decimal('tutar', 18, 4)->nullable();
            $table->string('pb', 50)->nullable();
            $table->string('islem_tarihi', 50)->nullable();
            $table->string('islem_saati', 50)->nullable();
            $table->integer('gerceklesme')->nullable();
            $table->unsignedBigInteger('servis_id')->nullable();
            $table->integer('satis_id')->nullable();
            $table->unsignedBigInteger('tedarikci_id')->nullable();
            $table->unsignedBigInteger('ilgili_personel_id')->nullable();
            $table->string('aciklama', 500)->nullable();
            $table->unsignedBigInteger('odeme_turu_id')->nullable();
            $table->unsignedBigInteger('odeme_sekli_id')->nullable();
            $table->integer('taksitmi')->nullable();
            $table->integer('silindi')->nullable();
            $table->string('silinme_tarihi', 50)->nullable();
            $table->integer('sadece_kasa')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kasa');
    }
};

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
        Schema::create('mesajlar', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('uye_firma_id')->nullable();
            $table->unsignedBigInteger('gonderen_personel_id')->nullable();
            $table->unsignedBigInteger('alici_personel_id')->nullable();
            $table->unsignedBigInteger('mesaj_kisi_id')->nullable();
            $table->text('mesaj')->nullable();
            $table->string('tarih', 50)->nullable();
            $table->string('saat', 50)->nullable();
            $table->integer('okunma')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mesajlar');
    }
};

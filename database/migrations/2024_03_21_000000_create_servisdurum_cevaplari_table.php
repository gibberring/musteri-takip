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
        Schema::create('servisdurum_cevaplari', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('uye_firma_id')->nullable();
            $table->unsignedBigInteger('soru_id');
            $table->text('cevap');
            $table->unsignedBigInteger('durumCevap0_id');
            $table->timestamps();

            // Foreign key constraint'leri kaldırıyoruz
            // $table->foreign('soru_id')->references('id')->on('servisdurum_sorulari')->onDelete('cascade');
            // $table->foreign('durumCevap0_id')->references('id')->on('servisdurum_cevap0')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servisdurum_cevaplari');
    }
}; 
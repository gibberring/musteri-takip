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
        Schema::create('servisdurum_sorulari', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('uye_firma_id')->nullable();
            $table->unsignedBigInteger('servis_durum_id')->nullable();
            $table->string('soru', 50)->nullable();
            $table->string('cevap_format', 50)->nullable();
            $table->integer('sira')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servisdurum_sorulari');
    }
};

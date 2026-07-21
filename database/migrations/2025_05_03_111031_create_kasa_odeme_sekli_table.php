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
        Schema::create('kasa_odeme_sekli', function (Blueprint $table) {
            $table->id();
            $table->foreignId('uye_firma_id')->nullable()->constrained('its_uye_firmalar');
            $table->string('ad', 50)->nullable();
            $table->integer('sira')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kasa_odeme_sekli');
    }
};

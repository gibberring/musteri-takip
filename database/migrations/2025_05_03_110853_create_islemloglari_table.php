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
        Schema::create('islemloglari', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('islemi_yapan_personel_id')->nullable();
            $table->unsignedBigInteger('servis_id')->nullable();
            $table->string('tarih', 50)->nullable();
            $table->string('saat', 50)->nullable();
            $table->string('aciklama', 500)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('islemloglari');
    }
};

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
        Schema::create('personel_soru_cevaplari', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('personel_id')->nullable();
            $table->unsignedBigInteger('soru_id')->nullable();
            $table->string('cevap', 250)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personel_soru_cevaplari');
    }
};

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
        Schema::create('servis_durum', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('uye_firma_id')->nullable();
            $table->string('ad', 500)->nullable();
            $table->string('hangi_asamlarda_goruınur', 500)->nullable();
            $table->integer('sira')->nullable();
            $table->integer('sikayetci')->nullable();
            $table->integer('yonlendirme_var')->nullable();
            $table->integer('atolyeci')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servis_durum');
    }
};

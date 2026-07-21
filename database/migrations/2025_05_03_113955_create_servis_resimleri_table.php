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
        Schema::create('servis_resimleri', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('servis_id');
            $table->string('dosya_yolu');
            $table->text('aciklama')->nullable();
            $table->unsignedBigInteger('ekleyen_personel_id')->nullable();
            $table->timestamps();

            $table->foreign('servis_id')->references('id')->on('servisler')->onDelete('cascade');
            $table->foreign('ekleyen_personel_id')->references('id')->on('personel')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servis_resimleri');
    }
};

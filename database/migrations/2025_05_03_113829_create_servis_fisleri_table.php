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
        Schema::create('servis_fisleri', function (Blueprint $table) {
            $table->id();
            $table->foreignId('servis_id')->constrained('servisler')->onDelete('cascade');
            $table->string('pdf');
            $table->date('tarih');
            $table->time('saat');
            $table->foreignId('olusturan_personel_id')->nullable()->constrained('personel')->onDelete('set null');
            $table->text('mus_imza')->nullable();
            $table->text('tek_imza')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servis_fisleri');
    }
};

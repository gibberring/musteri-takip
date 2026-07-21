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
        Schema::dropIfExists('servis_kaynaklari');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::create('servis_kaynaklari', function (Blueprint $table) {
        //     $table->id();
        //     // Gerekirse diğer kolonları buraya ekleyin
        //     $table->string('ad'); 
        //     $table->timestamps();
        // });
    }
};

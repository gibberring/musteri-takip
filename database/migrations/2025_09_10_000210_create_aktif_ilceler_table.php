<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aktif_ilceler', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ilce_id')->unique();
            $table->unsignedBigInteger('il_id');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->index(['il_id', 'ilce_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aktif_ilceler');
    }
};



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
        Schema::create('ilceler', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('il_id');
            $table->string('ad', 50)->nullable();
            $table->string('google_adi', 50)->nullable();
            $table->integer('bolge')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ilceler');
    }
};

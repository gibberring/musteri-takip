<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_abilities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id'); // tnm_personel_pozisyon id
            $table->string('ability'); // e.g., canEditLogs, canDeleteKasa
            $table->boolean('allowed')->default(true);
            $table->timestamps();
            $table->index(['role_id', 'ability']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_abilities');
    }
};



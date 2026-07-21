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
        Schema::table('servis_fisleri', function (Blueprint $table) {
            $table->string('pdf', 255)->change(); // Uzunluğu 255 yap
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('servis_fisleri', function (Blueprint $table) {
            //
        });
    }
};

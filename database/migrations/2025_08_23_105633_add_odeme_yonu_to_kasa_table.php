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
        Schema::table('kasa', function (Blueprint $table) {
            $table->tinyInteger('odeme_yonu')->after('gerceklesme')->comment('1: Gelen Ödeme (Gelir), -1: Giden Ödeme (Gider)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kasa', function (Blueprint $table) {
            $table->dropColumn('odeme_yonu');
        });
    }
};

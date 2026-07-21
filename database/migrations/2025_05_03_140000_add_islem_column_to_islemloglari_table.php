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
        Schema::table('islemloglari', function (Blueprint $table) {
            $table->unsignedBigInteger('servis_durum_id')->nullable()->after('servis_id');
            $table->foreign('servis_durum_id')->references('id')->on('servis_durum')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('islemloglari', function (Blueprint $table) {
            $table->dropForeign(['servis_durum_id']);
            $table->dropColumn('servis_durum_id');
        });
    }
}; 
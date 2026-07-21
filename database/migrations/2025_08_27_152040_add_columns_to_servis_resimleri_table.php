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
        Schema::table('servis_resimleri', function (Blueprint $table) {
            $table->unsignedBigInteger('servis_id')->after('id');
            $table->string('dosya_yolu')->after('servis_id');
            $table->text('aciklama')->nullable()->after('dosya_yolu');
            $table->unsignedBigInteger('ekleyen_personel_id')->nullable()->after('aciklama');

            $table->foreign('servis_id')->references('id')->on('servisler')->onDelete('cascade');
            $table->foreign('ekleyen_personel_id')->references('id')->on('personel')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('servis_resimleri', function (Blueprint $table) {
            $table->dropForeign(['servis_id']);
            $table->dropForeign(['ekleyen_personel_id']);
            $table->dropColumn(['servis_id', 'dosya_yolu', 'aciklama', 'ekleyen_personel_id']);
        });
    }
};

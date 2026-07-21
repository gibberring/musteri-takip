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
        Schema::table('tnm_personel_pozisyon', function (Blueprint $table) {
            $table->renameColumn('izin_tumfirmalarigorme', 'izin_servis_islem_silme');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tnm_personel_pozisyon', function (Blueprint $table) {
            $table->renameColumn('izin_servis_islem_silme', 'izin_tumfirmalarigorme');
        });
    }
};

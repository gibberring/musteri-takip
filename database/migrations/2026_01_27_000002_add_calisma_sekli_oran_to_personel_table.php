<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personel', function (Blueprint $table) {
            $table->unsignedTinyInteger('calisma_sekli_oran')->nullable()->after('e_fis_verebilir');
        });
    }

    public function down(): void
    {
        Schema::table('personel', function (Blueprint $table) {
            $table->dropColumn('calisma_sekli_oran');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('tnm_personel_pozisyon')
            ->where('id', 1076)
            ->update(['ad' => 'Harici Operatör']);
    }

    public function down(): void
    {
        DB::table('tnm_personel_pozisyon')
            ->where('id', 1076)
            ->update(['ad' => 'İdari İşler']);
    }
};

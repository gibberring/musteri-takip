<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('servisler', function (Blueprint $table) {
            if (!Schema::hasColumn('servisler', 'olusturan_personel_id')) {
                $table->unsignedBigInteger('olusturan_personel_id')->nullable()->after('personel_id');
                $table->index('olusturan_personel_id');
            }
        });

        // Mevcut kayıtları ilk işlem logundan doldur
        DB::statement("
            UPDATE servisler s
            SET s.olusturan_personel_id = (
                SELECT il.islemi_yapan_personel_id
                FROM islemloglari il
                WHERE il.servis_id = s.id
                ORDER BY il.id ASC
                LIMIT 1
            )
            WHERE s.olusturan_personel_id IS NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('servisler', function (Blueprint $table) {
            if (Schema::hasColumn('servisler', 'olusturan_personel_id')) {
                $table->dropIndex(['olusturan_personel_id']);
                $table->dropColumn('olusturan_personel_id');
            }
        });
    }
};

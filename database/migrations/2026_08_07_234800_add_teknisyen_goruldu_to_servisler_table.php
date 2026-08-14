<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('servisler')) {
            return;
        }

        Schema::table('servisler', function (Blueprint $table) {
            if (!Schema::hasColumn('servisler', 'teknisyen_goruldu_at')) {
                $table->timestamp('teknisyen_goruldu_at')->nullable()->after('personel_id');
            }
            if (!Schema::hasColumn('servisler', 'teknisyen_goruldu_personel_id')) {
                $table->unsignedBigInteger('teknisyen_goruldu_personel_id')->nullable()->after('teknisyen_goruldu_at');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('servisler')) {
            return;
        }

        Schema::table('servisler', function (Blueprint $table) {
            if (Schema::hasColumn('servisler', 'teknisyen_goruldu_personel_id')) {
                $table->dropColumn('teknisyen_goruldu_personel_id');
            }
            if (Schema::hasColumn('servisler', 'teknisyen_goruldu_at')) {
                $table->dropColumn('teknisyen_goruldu_at');
            }
        });
    }
};

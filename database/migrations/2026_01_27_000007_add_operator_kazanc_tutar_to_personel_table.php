<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personel', function (Blueprint $table) {
            if (!Schema::hasColumn('personel', 'operator_kazanc_tutar')) {
                $table->decimal('operator_kazanc_tutar', 12, 2)
                    ->default(0)
                    ->after('calisma_sekli_adet_tutar');
            }
        });
    }

    public function down(): void
    {
        Schema::table('personel', function (Blueprint $table) {
            if (Schema::hasColumn('personel', 'operator_kazanc_tutar')) {
                $table->dropColumn('operator_kazanc_tutar');
            }
        });
    }
};

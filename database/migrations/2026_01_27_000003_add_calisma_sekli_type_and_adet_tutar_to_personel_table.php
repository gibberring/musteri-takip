<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personel', function (Blueprint $table) {
            $table->string('calisma_sekli_type', 20)->nullable()->after('calisma_sekli_oran');
            $table->decimal('calisma_sekli_adet_tutar', 10, 2)->nullable()->after('calisma_sekli_type');
        });
    }

    public function down(): void
    {
        Schema::table('personel', function (Blueprint $table) {
            $table->dropColumn(['calisma_sekli_type', 'calisma_sekli_adet_tutar']);
        });
    }
};

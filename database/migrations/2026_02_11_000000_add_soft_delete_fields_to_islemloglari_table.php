<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('islemloglari', function (Blueprint $table) {
            if (!Schema::hasColumn('islemloglari', 'silindi')) {
                $table->tinyInteger('silindi')->nullable()->after('aciklama');
            }
            if (!Schema::hasColumn('islemloglari', 'silen_kisi_id')) {
                $table->unsignedBigInteger('silen_kisi_id')->nullable()->after('silindi');
            }
            if (!Schema::hasColumn('islemloglari', 'silinme_tarihi')) {
                $table->string('silinme_tarihi', 50)->nullable()->after('silen_kisi_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('islemloglari', function (Blueprint $table) {
            if (Schema::hasColumn('islemloglari', 'silinme_tarihi')) {
                $table->dropColumn('silinme_tarihi');
            }
            if (Schema::hasColumn('islemloglari', 'silen_kisi_id')) {
                $table->dropColumn('silen_kisi_id');
            }
            if (Schema::hasColumn('islemloglari', 'silindi')) {
                $table->dropColumn('silindi');
            }
        });
    }
};

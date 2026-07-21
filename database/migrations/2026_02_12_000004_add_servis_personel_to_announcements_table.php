<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            if (!Schema::hasColumn('announcements', 'personel_id')) {
                $table->unsignedBigInteger('personel_id')->nullable()->after('hedef_rol');
                $table->index('personel_id');
            }
            if (!Schema::hasColumn('announcements', 'servis_id')) {
                $table->unsignedBigInteger('servis_id')->nullable()->after('personel_id');
                $table->index('servis_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            if (Schema::hasColumn('announcements', 'servis_id')) {
                $table->dropIndex(['servis_id']);
                $table->dropColumn('servis_id');
            }
            if (Schema::hasColumn('announcements', 'personel_id')) {
                $table->dropIndex(['personel_id']);
                $table->dropColumn('personel_id');
            }
        });
    }
};

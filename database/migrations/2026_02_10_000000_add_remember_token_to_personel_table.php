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
        Schema::table('personel', function (Blueprint $table) {
            if (!Schema::hasColumn('personel', 'remember_token')) {
                $table->rememberToken()->nullable()->after('sifre');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personel', function (Blueprint $table) {
            if (Schema::hasColumn('personel', 'remember_token')) {
                $table->dropColumn('remember_token');
            }
        });
    }
};

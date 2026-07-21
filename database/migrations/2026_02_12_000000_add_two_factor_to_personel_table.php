<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personel', function (Blueprint $table) {
            if (!Schema::hasColumn('personel', 'two_factor_secret')) {
                $table->text('two_factor_secret')->nullable()->after('sifre');
            }
            if (!Schema::hasColumn('personel', 'two_factor_enabled')) {
                $table->boolean('two_factor_enabled')->default(false)->after('two_factor_secret');
            }
            if (!Schema::hasColumn('personel', 'two_factor_verified_at')) {
                $table->timestamp('two_factor_verified_at')->nullable()->after('two_factor_enabled');
            }
        });
    }

    public function down(): void
    {
        Schema::table('personel', function (Blueprint $table) {
            if (Schema::hasColumn('personel', 'two_factor_verified_at')) {
                $table->dropColumn('two_factor_verified_at');
            }
            if (Schema::hasColumn('personel', 'two_factor_enabled')) {
                $table->dropColumn('two_factor_enabled');
            }
            if (Schema::hasColumn('personel', 'two_factor_secret')) {
                $table->dropColumn('two_factor_secret');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            if (!Schema::hasColumn('announcements', 'processed_at')) {
                $table->timestamp('processed_at')->nullable()->after('published_at');
                $table->index('processed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            if (Schema::hasColumn('announcements', 'processed_at')) {
                $table->dropIndex(['processed_at']);
                $table->dropColumn('processed_at');
            }
        });
    }
};

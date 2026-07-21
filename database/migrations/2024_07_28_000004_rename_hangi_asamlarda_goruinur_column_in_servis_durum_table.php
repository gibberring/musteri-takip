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
        Schema::table('servis_durum', function (Blueprint $table) {
            if (Schema::hasColumn('servis_durum', 'hangi_asamlarda_goruınur') && !Schema::hasColumn('servis_durum', 'hangi_asamalarda_gorunur')) {
                $table->renameColumn('hangi_asamlarda_goruınur', 'hangi_asamalarda_gorunur');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('servis_durum', function (Blueprint $table) {
            if (Schema::hasColumn('servis_durum', 'hangi_asamalarda_gorunur') && !Schema::hasColumn('servis_durum', 'hangi_asamlarda_goruınur')) {
                $table->renameColumn('hangi_asamalarda_gorunur', 'hangi_asamlarda_goruınur');
            }
        });
    }
}; 
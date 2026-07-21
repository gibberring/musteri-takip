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
            if (Schema::hasColumn('servis_durum', 'sikayetci')) {
                $table->dropColumn('sikayetci');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('servis_durum', function (Blueprint $table) {
            // Sütunun orijinal tipini ve özelliklerini bilmediğimiz için
            // genel bir string ve nullable olarak ekliyoruz.
            // Gerekirse bu kısmı orijinal şemaya göre düzenleyebilirsiniz.
            if (!Schema::hasColumn('servis_durum', 'sikayetci')) {
                $table->string('sikayetci')->nullable()->comment('Bu sütun daha önce şikayetçi bilgisini tutuyordu');
            }
        });
    }
}; 
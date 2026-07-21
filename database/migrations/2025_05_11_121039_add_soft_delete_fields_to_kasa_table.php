<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('kasa', function (Blueprint $table) {
            $sadeceKasaKolonuVar = Schema::hasColumn('kasa', 'sadece_kasa');
            $timestampsKolonuVar = Schema::hasColumn('kasa', 'created_at');

            if (!Schema::hasColumn('kasa', 'silindi')) {
                if ($sadeceKasaKolonuVar) {
                    $table->boolean('silindi')->default(false)->after('sadece_kasa')->comment('Kayıt silindi mi? (0=Hayır, 1=Evet)');
                } else if ($timestampsKolonuVar) {
                    $table->boolean('silindi')->default(false)->before('created_at')->comment('Kayıt silindi mi? (0=Hayır, 1=Evet)');
                } else {
                    $table->boolean('silindi')->default(false)->comment('Kayıt silindi mi? (0=Hayır, 1=Evet)');
                }
            }

            if (!Schema::hasColumn('kasa', 'silen_kisi_id')) {
                if (Schema::hasColumn('kasa', 'silindi')) {
                    $table->foreignId('silen_kisi_id')->nullable()->after('silindi')->constrained('personel')->onDelete('set null');
                } else if ($sadeceKasaKolonuVar) {
                    $table->foreignId('silen_kisi_id')->nullable()->after('sadece_kasa')->constrained('personel')->onDelete('set null');
                } else if ($timestampsKolonuVar) {
                    $table->foreignId('silen_kisi_id')->nullable()->before('created_at')->constrained('personel')->onDelete('set null');
                } else {
                    $table->foreignId('silen_kisi_id')->nullable()->constrained('personel')->onDelete('set null');
                }
            }

            if (!Schema::hasColumn('kasa', 'silinme_tarihi')) {
                if (Schema::hasColumn('kasa', 'silen_kisi_id')) {
                    $table->timestamp('silinme_tarihi')->nullable()->after('silen_kisi_id');
                } else if (Schema::hasColumn('kasa', 'silindi')) {
                    $table->timestamp('silinme_tarihi')->nullable()->after('silindi');
                } else if ($sadeceKasaKolonuVar) {
                    $table->timestamp('silinme_tarihi')->nullable()->after('sadece_kasa');
                } else if ($timestampsKolonuVar) {
                    $table->timestamp('silinme_tarihi')->nullable()->before('created_at');
                } else {
                    $table->timestamp('silinme_tarihi')->nullable();
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kasa', function (Blueprint $table) {
            if (Schema::hasColumn('kasa', 'silen_kisi_id')) {
                try {
                    $table->dropForeign(['silen_kisi_id']);
                    Log::info("Foreign key for 'silen_kisi_id' dropped successfully from 'kasa' table during rollback.");
                } catch (\Exception $e) {
                    Log::warning("Could not drop foreign key for 'silen_kisi_id' from 'kasa' table using default array method. It might not exist or have a custom name. Error: " . $e->getMessage());
                }
            }

            $columnsToDrop = [];
            if (Schema::hasColumn('kasa', 'silinme_tarihi')) {
                $columnsToDrop[] = 'silinme_tarihi';
            }
            if (Schema::hasColumn('kasa', 'silen_kisi_id')) {
                $columnsToDrop[] = 'silen_kisi_id';
            }
            if (Schema::hasColumn('kasa', 'silindi')) {
                $columnsToDrop[] = 'silindi';
            }
            
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};

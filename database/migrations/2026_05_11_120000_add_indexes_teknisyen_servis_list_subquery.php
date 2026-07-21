<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Servis listesi (teknisyen filtresi) alt sorgusunu hızlandırmak için bileşik indeksler.
     */
    public function up(): void
    {
        Schema::table('servisdurum_cevap0', function (Blueprint $table) {
            if (!$this->indexExists('servisdurum_cevap0', 'idx_c0_durum_servis')) {
                $table->index(['servis_durum_id', 'servis_id'], 'idx_c0_durum_servis');
            }
        });

        Schema::table('servisdurum_cevaplari', function (Blueprint $table) {
            if (!$this->indexExists('servisdurum_cevaplari', 'idx_cevap_soru_durum0')) {
                $table->index(['soru_id', 'durumCevap0_id'], 'idx_cevap_soru_durum0');
            }
        });
    }

    public function down(): void
    {
        Schema::table('servisdurum_cevap0', function (Blueprint $table) {
            if ($this->indexExists('servisdurum_cevap0', 'idx_c0_durum_servis')) {
                $table->dropIndex('idx_c0_durum_servis');
            }
        });

        Schema::table('servisdurum_cevaplari', function (Blueprint $table) {
            if ($this->indexExists('servisdurum_cevaplari', 'idx_cevap_soru_durum0')) {
                $table->dropIndex('idx_cevap_soru_durum0');
            }
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();

        $row = $connection->selectOne(
            'SELECT 1 FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ? LIMIT 1',
            [$database, $table, $indexName]
        );

        return $row !== null;
    }
};

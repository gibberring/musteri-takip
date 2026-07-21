<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    public function up(): void
    {
        $now = Carbon::now();
        $odemeTurleri = [
            [
                'ad' => 'Uğur Bey Harcama',
                'muhattap' => 'ACIKLAMA',
                'yon' => -1,
                'sira' => 4,
                'servisler_kategorisi' => null,
                'stok_kategorisi' => null,
            ],
            [
                'ad' => 'Maaş Ödemesi',
                'muhattap' => 'ACIKLAMA',
                'yon' => -1,
                'sira' => 5,
                'servisler_kategorisi' => null,
                'stok_kategorisi' => null,
            ],
        ];

        foreach ($odemeTurleri as $tur) {
            DB::table('kasa_odeme_turu')->updateOrInsert(
                ['ad' => $tur['ad']],
                [
                    'uye_firma_id' => null,
                    'muhattap' => $tur['muhattap'],
                    'yon' => $tur['yon'],
                    'sira' => $tur['sira'],
                    'servisler_kategorisi' => $tur['servisler_kategorisi'],
                    'stok_kategorisi' => $tur['stok_kategorisi'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    public function down(): void
    {
        DB::table('kasa_odeme_turu')
            ->whereIn('ad', ['Uğur Bey Harcama', 'Maaş Ödemesi'])
            ->delete();
    }
};

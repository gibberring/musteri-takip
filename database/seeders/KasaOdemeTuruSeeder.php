<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KasaOdemeTuruSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $odemeTurleri = [
            [
                'ad' => 'Ofis Gideri',
                'muhattap' => 'ACIKLAMA', // Sadece ACIKLAMA
                'yon' => -1, // Gider
                'sira' => 1,
                'servisler_kategorisi' => null,
                'stok_kategorisi' => null,
            ],
            [
                'ad' => 'Reklam Ödemesi',
                'muhattap' => 'ACIKLAMA', // Sadece ACIKLAMA
                'yon' => -1, // Gider
                'sira' => 2,
                'servisler_kategorisi' => null,
                'stok_kategorisi' => null,
            ],
            [
                'ad' => 'Servis İşlemleri', // Yeni eklendi
                'muhattap' => 'SERVIS,ACIKLAMA,PERSONEL',
                'yon' => 1, // Gelir
                'sira' => 3,
                'servisler_kategorisi' => null, // Veya true/1 eğer bu bir servis kategorisi ise
                'stok_kategorisi' => null,
            ],
            [
                'ad' => 'Uğur Bey Harcama',
                'muhattap' => 'ACIKLAMA',
                'yon' => -1, // Gider
                'sira' => 4,
                'servisler_kategorisi' => null,
                'stok_kategorisi' => null,
            ],
            [
                'ad' => 'Maaş Ödemesi',
                'muhattap' => 'ACIKLAMA',
                'yon' => -1, // Gider
                'sira' => 5,
                'servisler_kategorisi' => null,
                'stok_kategorisi' => null,
            ],
        ];

        // Önce mevcut kayıtları silmek isteyebilirsiniz (opsiyonel, dikkatli olun!)
        // DB::table('kasa_odeme_turu')->delete(); 

        foreach ($odemeTurleri as $tur) {
            DB::table('kasa_odeme_turu')->updateOrInsert(
                ['ad' => $tur['ad']], // Bu ada sahip kayıt varsa güncelle, yoksa ekle
                [
                    'uye_firma_id' => null,
                    'muhattap' => $tur['muhattap'],
                    'yon' => $tur['yon'],
                    'sira' => $tur['sira'],
                    'servisler_kategorisi' => $tur['servisler_kategorisi'],
                    'stok_kategorisi' => $tur['stok_kategorisi'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                    // 'garanti_geliri_kategorisi' migration'da vardı, gerekirse ekleyin
                ]
            );
        }
    }
}

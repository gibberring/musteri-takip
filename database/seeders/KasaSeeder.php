<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Personel; // Personel modeli için
use App\Models\Servis;   // Servis modeli için
use App\Models\KasaOdemeSekli; // Geri eklendi
use App\Models\KasaOdemeTuru;   // Geri eklendi
// KasaOdemeSekli ve KasaOdemeTuru modelleri yerine DB::table kullanılacak

class KasaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $personelIds = Personel::pluck('id')->all();
        $servisIds = Servis::pluck('id')->all();
        $odemeSekliIds = KasaOdemeSekli::pluck('id')->all(); // Model üzerinden pluck
        $odemeTuruIds = KasaOdemeTuru::pluck('id')->all();   // Model üzerinden pluck

        if (empty($personelIds) || empty($servisIds) || empty($odemeSekliIds) || empty($odemeTuruIds)) {
            $this->command->info('KasaSeeder: Gerekli ilişkisel veriler (Personel, Servis, Ödeme Şekli/Türü) bulunamadı. Tohumlama atlanıyor.');
            return;
        }

        // Durumları Kasa migration'ındaki 'gerceklesme' sütununa göre ayarlayabiliriz (Örn: 1 Tamamlandı, 0 Beklemede)
        $gerceklesmeDurumlari = [0, 1]; 

        for ($i = 0; $i < 5; $i++) {
            $selectedPersonelId = $personelIds[array_rand($personelIds)];
            $personel = Personel::find($selectedPersonelId);
            $personelAdi = $personel ? $personel->ad : 'Bilinmeyen Personel';
            $personelTel = $personel ? ($personel->tel1 ?? 'Telefon Yok') : 'Telefon Yok';
            
            $selectedServisId = $servisIds[array_rand($servisIds)];
            $selectedOdemeSekliId = $odemeSekliIds[array_rand($odemeSekliIds)];
            $selectedOdemeTuruId = $odemeTuruIds[array_rand($odemeTuruIds)];

            $aciklama = sprintf("-Personel : %s (%s) -Servis No : %s", 
                                $personelAdi, 
                                $personelTel, 
                                $selectedServisId
                            );

            DB::table('kasa')->insert([
                'uye_firma_id' => null,
                'personel_id' => $selectedPersonelId,
                'servis_id' => $selectedServisId,
                'odeme_turu_id' => $selectedOdemeTuruId,
                'odeme_sekli_id' => $selectedOdemeSekliId,
                'tarih' => Carbon::today()->subDays(rand(0, 30))->format('Y-m-d'),
                'saat' => sprintf("%02d:%02d:%02d", rand(0,23), rand(0,59), rand(0,59)),
                'aciklama' => $aciklama,
                'tutar' => round(rand(10000, 100000) / 100, 2),
                'pb' => 'TL', // Para birimi eklendi (varsayım)
                'islem_tarihi' => Carbon::today()->format('Y-m-d'), // Gerçekleşme tarihi yerine
                'islem_saati' => sprintf("%02d:%02d:%02d", rand(0,23), rand(0,59), rand(0,59)),
                'gerceklesme' => $gerceklesmeDurumlari[array_rand($gerceklesmeDurumlari)], // 'durum' yerine
                // 'yon' sütunu migration'da yoktu.
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}

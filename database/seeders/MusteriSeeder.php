<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MusteriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $musteriAdlari = [
            'Ahmet Yılmaz', 'Ayşe Kaya', 'Mehmet Demir', 'Fatma Çelik', 'Mustafa Şahin',
            'Emine Yıldız', 'Ali Öztürk', 'Zeynep Aydın', 'Hüseyin Arslan', 'Hatice Doğan',
            'İbrahim Kılıç', 'Elif Çetin', 'Hasan Polat', 'Sultan Korkmaz', 'Murat Yazıcı'
        ];

        $dataToInsert = [];
        $idCounter = 1;

        foreach ($musteriAdlari as $ad) {
            $dataToInsert[] = [
                'id' => $idCounter++,
                'ad' => $ad,
                'tel1' => '0534 399 71 91',
                'il_id' => 34,
                'ilce_id' => 1103,
                'adres' => 'adalar mah. 1453 sk no:1453',
                // Diğer nullable sütunlar varsayılan olarak null olacak
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        // Eski verileri sil (ID'lerin 1'den başlaması için truncate yerine delete)
        DB::table('musteriler')->delete();
        // Otomatik artan ID'yi sıfırlamak gerekebilir (veritabanı türüne bağlı)
        // DB::statement('ALTER TABLE musteriler AUTO_INCREMENT = 1;'); // MySQL için örnek

        // Toplu ekleme
        DB::table('musteriler')->insert($dataToInsert);

        $this->command->info(count($musteriAdlari) . ' müşteri başarıyla eklendi!');
    }
}

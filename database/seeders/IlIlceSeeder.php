<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class IlIlceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // JSON dosyasının yolu (projenin kök dizininde olduğunu varsayıyoruz)
        $jsonPath = base_path('iller_ve_ilceler.json');

        // JSON dosyası var mı kontrol et
        if (!File::exists($jsonPath)) {
            $this->command->error('iller_ve_ilceler.json dosyası bulunamadı!');
            return;
        }

        // JSON içeriğini oku ve diziye çevir
        $jsonData = File::get($jsonPath);
        $data = json_decode($jsonData, true); // true ile associative array olarak alırız

        if (is_null($data)) {
            $this->command->error('JSON dosyası okunamadı veya hatalı format!');
            return;
        }

        $iller = [];
        $ilceler = [];

        $this->command->info('İller ve ilçeler veritabanına ekleniyor...');

        // Verileri işle ve dizilere doldur
        foreach ($data as $il) {
            $iller[] = [
                'id' => $il['value'],
                'ad' => $il['text']
                // Diğer il sütunları varsa buraya eklenebilir (varsayılan null veya değer)
            ];

            if (isset($il['districts']) && is_array($il['districts'])) {
                foreach ($il['districts'] as $ilce) {
                    $ilceler[] = [
                        'id' => $ilce['value'],
                        'il_id' => $il['value'], // Bağlı olduğu ilin ID'si
                        'ad' => $ilce['text']
                        // Diğer ilçe sütunları varsa buraya eklenebilir
                    ];
                }
            }
        }

        // Eski verileri temizle (isteğe bağlı, --seed ile fresh migrate yapılıyorsa gerekmeyebilir)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;'); // Kontrolleri geçici kapat
        DB::table('ilceler')->truncate();
        DB::table('iller')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;'); // Kontrolleri tekrar aç

        // Toplu ekleme (Bulk Insert)
        // Küçük parçalara bölerek eklemek daha performanslı olabilir büyük verilerde
        foreach (array_chunk($iller, 200) as $chunk) { // 200'lük parçalar halinde
            DB::table('iller')->insert($chunk);
        }

        foreach (array_chunk($ilceler, 500) as $chunk) { // 500'lük parçalar halinde
            DB::table('ilceler')->insert($chunk);
        }

        $this->command->info('İller ve ilçeler başarıyla eklendi!');
    }
}

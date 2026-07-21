<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ServisDurumSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verileri yapılandırılmış dizi olarak tanımla
        $servisDurumlar = [
            [9097, 2491, 'Beklemede', '0', 2, 0, null, null],
            [9098, 2491, 'Teknisyen Yönlendirildi', '9477,9526,9528,9783,9334,9097,9103,9110,9113,9114,9116,9099', 4, 0, 1, 0],
            [9099, 2491, 'Servisi Sonlandırıldı', '9224,9524,9334,9358,9359,9105,9106,9107,9102,9104,9110,9115,9116', 999, 0, null, 0],
            [9100, 2491, 'Atölyeye Alındı', '9477,9098,9101', 8, 0, null, 1],
            [9102, 2491, 'Müşteriye Ulaşılamadı', '9477,9098,9101', 23, 0, null, 0],
            [9103, 2491, 'Parça Gidecek', '9477,9357,9098,9108,9112', 18, 0, null, 0],
            [9104, 2491, 'Müşteri İptal Etti', '9477,9334,9098,9103,9101,9108', 23, 0, null, 0],
            [9105, 2491, 'Yerinde Bakım Yapıldı', '9477,9786,9098,9103', 18, 0, null, 0],
            [9106, 2491, 'Fiyatta Anlaşılamadı', '9477,9785,9098', 20, 0, null, 0],
            [9110, 2491, 'Haber Verecek', '9477,9097,9098,9103,9106,9102,9104,9114', 28, 0, null, 0],
            [9113, 2491, 'Atölyede Tamir Ediliyor', '9385,9522,9526,9528,9357,9100,9269,9103,9112', 34, 0, null, 1],
            [9114, 2491, 'Teslimata Hazır (Tamamlandı)', '9522,9526,9109,9113', 36, 0, null, 1],
            [9115, 2491, 'Cihaz Teslim Edildi', '9526', 38, 0, null, 0],
            [9116, 2491, 'Tekrar Servis', '9224,9357,9358,9359,9115,9099', 60, 0, null, 0],
            [9117, 2491, 'Nakliyede', '9784,9787,9110,9114', 35, 0, 1, 0],
            [9334, 2491, 'Teyid Araması', '9785,9097,9106,9102,9104,9110,9114,9115,9099', 1, 0, null, 0],
            [9358, 2491, 'Ücret İadesi Gerçekleşti', '9524,9116,9099', 1, 0, null, 0],
            [9359, 2491, 'Bize Kalan Ürünler', '9522,9116,9099', 1, 0, null, 0],
            [9477, 2491, 'Yarın Gidilecek', '9477,9786,9098,9100,9103,9102,9110,9117,9114,9116', 0, 0, null, 0],
            [9524, 2491, 'Ücret İadesi Sürecinde', '9224,9524,9358,9105,9109,9115,9116,9099', 0, 0, null, 0],
            [9526, 2491, 'T.Atölyeye Alındı', '9477,9786,9098', 0, 0, null, 0],
            [9784, 2491, 'Tv Süpürge Atölyesi', '9098,9117', 0, 0, null, 0],
            [9786, 2491, 'T.Nakliyede', '9115', 0, 0, null, 0],
            [9787, 2491, 'İzmir Atölye', '9098', 0, 0, null, 0]
        ];

        $dataToInsert = [];
        $now = Carbon::now();

        foreach ($servisDurumlar as $durum) {
            $dataToInsert[] = [
                'id' => $durum[0],
                'uye_firma_id' => $durum[1],
                'ad' => trim($durum[2]),
                'hangi_asamlarda_goruınur' => trim($durum[3]),
                'sira' => $durum[4],
                'sikayetci' => $durum[5],
                'yonlendirme_var' => $durum[6],
                'atolyeci' => $durum[7],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Eski verileri temizle
        // Migration'da timestamps eklenmemişse bu tablo için truncate yeterli olabilir.
        // Eğer timestamps varsa ve korumak isterseniz truncate yerine updateOrInsert kullanılabilir.
        // DB::table('servis_durum')->truncate(); 

        // Toplu ekleme
        foreach ($dataToInsert as $data) {
            DB::table('servis_durum')->updateOrInsert(
                ['id' => $data['id']],
                $data
            );
        }

        $this->command->info('Servis durumları başarıyla eklendi!');
    }
}

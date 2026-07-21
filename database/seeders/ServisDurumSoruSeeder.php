<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServisDurumSoruSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sorular = [
            ['id' => 13234, 'uye_firma_id' => 2491, 'servis_durum_id' => 9098, 'soru' => 'Teknisyen', 'cevap_format' => '[personelSor]', 'sira' => 0],
            ['id' => 13235, 'uye_firma_id' => 2491, 'servis_durum_id' => 9098, 'soru' => 'Gidiş Tarihi', 'cevap_format' => '[tarihSor]', 'sira' => 3],
            ['id' => 13244, 'uye_firma_id' => 2491, 'servis_durum_id' => 9102, 'soru' => 'Açıklama', 'cevap_format' => '[aciklamaSor]', 'sira' => 1],
            ['id' => 13247, 'uye_firma_id' => 2491, 'servis_durum_id' => 9104, 'soru' => 'Açıklama', 'cevap_format' => '[aciklamaSor]', 'sira' => 1],
            ['id' => 13249, 'uye_firma_id' => 2491, 'servis_durum_id' => 9105, 'soru' => 'Müşteriye Verilen Fiyat', 'cevap_format' => '[fiyatSor]', 'sira' => 2],
            ['id' => 13250, 'uye_firma_id' => 2491, 'servis_durum_id' => 9105, 'soru' => 'Yapılan İşlemler', 'cevap_format' => '[aciklamaSor]', 'sira' => 1],
            ['id' => 13251, 'uye_firma_id' => 2491, 'servis_durum_id' => 9106, 'soru' => 'Açıklama', 'cevap_format' => '[aciklamaSor]', 'sira' => 1],
            ['id' => 13257, 'uye_firma_id' => 2491, 'servis_durum_id' => 9110, 'soru' => 'Açıklama', 'cevap_format' => '[aciklamaSor]', 'sira' => 1],
            ['id' => 13258, 'uye_firma_id' => 2491, 'servis_durum_id' => 9110, 'soru' => 'Aranacak Tarih', 'cevap_format' => '[tarihSor]', 'sira' => 2],
            ['id' => 13259, 'uye_firma_id' => 2491, 'servis_durum_id' => 9110, 'soru' => 'Saat Aralığı', 'cevap_format' => '[saatAraligiSec]', 'sira' => 3],
            ['id' => 13268, 'uye_firma_id' => 2491, 'servis_durum_id' => 9116, 'soru' => 'Açıklama', 'cevap_format' => '[aciklamaSor]', 'sira' => 1],
            ['id' => 13441, 'uye_firma_id' => 2491, 'servis_durum_id' => 9099, 'soru' => null, 'cevap_format' => '[aciklamaSor]', 'sira' => 0],
            ['id' => 13634, 'uye_firma_id' => 2491, 'servis_durum_id' => 9334, 'soru' => 'Açıklama', 'cevap_format' => '[aciklamaSor]', 'sira' => 1],
            ['id' => 13876, 'uye_firma_id' => 2491, 'servis_durum_id' => 9477, 'soru' => 'Neden ?', 'cevap_format' => '[aciklamaSor]', 'sira' => 0],
            ['id' => 14225, 'uye_firma_id' => 2491, 'servis_durum_id' => 9690, 'soru' => '?', 'cevap_format' => '[aciklamaSor]', 'sira' => 0],
            ['id' => 14226, 'uye_firma_id' => 2491, 'servis_durum_id' => 9697, 'soru' => '?', 'cevap_format' => '[aciklamaSor]', 'sira' => 0],
            ['id' => 14227, 'uye_firma_id' => 2491, 'servis_durum_id' => 9695, 'soru' => '?', 'cevap_format' => '[aciklamaSor]', 'sira' => 0],
            ['id' => 14228, 'uye_firma_id' => 2491, 'servis_durum_id' => 9700, 'soru' => '?', 'cevap_format' => '[aciklamaSor]', 'sira' => 0],
            ['id' => 14230, 'uye_firma_id' => 2491, 'servis_durum_id' => 9696, 'soru' => '?', 'cevap_format' => '[aciklamaSor]', 'sira' => 0],
            ['id' => 14231, 'uye_firma_id' => 2491, 'servis_durum_id' => 9691, 'soru' => '?', 'cevap_format' => '[aciklamaSor]', 'sira' => 0],
            ['id' => 14232, 'uye_firma_id' => 2491, 'servis_durum_id' => 9694, 'soru' => '?', 'cevap_format' => '[aciklamaSor]', 'sira' => 0],
            ['id' => 14233, 'uye_firma_id' => 2491, 'servis_durum_id' => 9701, 'soru' => '?', 'cevap_format' => '[aciklamaSor]', 'sira' => 0],
            ['id' => 14234, 'uye_firma_id' => 2491, 'servis_durum_id' => 9699, 'soru' => '?', 'cevap_format' => '[aciklamaSor]', 'sira' => 0],
            ['id' => 14235, 'uye_firma_id' => 2491, 'servis_durum_id' => 9693, 'soru' => '?', 'cevap_format' => '[aciklamaSor]', 'sira' => 0],
            ['id' => 14236, 'uye_firma_id' => 2491, 'servis_durum_id' => 9702, 'soru' => '?', 'cevap_format' => '[aciklamaSor]', 'sira' => 0],
            ['id' => 14237, 'uye_firma_id' => 2491, 'servis_durum_id' => 9703, 'soru' => '?', 'cevap_format' => '[aciklamaSor]', 'sira' => 0],
            ['id' => 14238, 'uye_firma_id' => 2491, 'servis_durum_id' => 9704, 'soru' => '?', 'cevap_format' => '[aciklamaSor]', 'sira' => 0],
            ['id' => 14239, 'uye_firma_id' => 2491, 'servis_durum_id' => 9100, 'soru' => '?', 'cevap_format' => '[aciklamaSor]', 'sira' => 0],
            ['id' => 14241, 'uye_firma_id' => 2491, 'servis_durum_id' => 9526, 'soru' => '?', 'cevap_format' => '[aciklamaSor]', 'sira' => 0],
            ['id' => 14242, 'uye_firma_id' => 2491, 'servis_durum_id' => 9524, 'soru' => '?', 'cevap_format' => '[aciklamaSor]', 'sira' => 0],
            ['id' => 14243, 'uye_firma_id' => 2491, 'servis_durum_id' => 9359, 'soru' => '?', 'cevap_format' => '[aciklamaSor]', 'sira' => 0],
            ['id' => 14245, 'uye_firma_id' => 2491, 'servis_durum_id' => 9358, 'soru' => '?', 'cevap_format' => '[aciklamaSor]', 'sira' => 0],
            ['id' => 14248, 'uye_firma_id' => 2491, 'servis_durum_id' => 9103, 'soru' => '?', 'cevap_format' => '[aciklamaSor]', 'sira' => 0],
            ['id' => 14254, 'uye_firma_id' => 2491, 'servis_durum_id' => 9113, 'soru' => '?', 'cevap_format' => '[aciklamaSor]', 'sira' => 0],
            ['id' => 14256, 'uye_firma_id' => 2491, 'servis_durum_id' => 9114, 'soru' => '?', 'cevap_format' => '[aciklamaSor]', 'sira' => 0],
            ['id' => 14257, 'uye_firma_id' => 2491, 'servis_durum_id' => 9115, 'soru' => '?', 'cevap_format' => '[aciklamaSor]', 'sira' => 0],
            ['id' => 14258, 'uye_firma_id' => 2491, 'servis_durum_id' => 9117, 'soru' => 'Teknisyen', 'cevap_format' => '[personelSor]', 'sira' => 1],
            ['id' => 14259, 'uye_firma_id' => 2491, 'servis_durum_id' => 9117, 'soru' => 'Tarih', 'cevap_format' => '[tarihSor]', 'sira' => 0],
            ['id' => 14420, 'uye_firma_id' => 2491, 'servis_durum_id' => 9784, 'soru' => '?', 'cevap_format' => '[aciklamaSor]', 'sira' => 0],
            ['id' => 14423, 'uye_firma_id' => 2491, 'servis_durum_id' => 9786, 'soru' => 'Gidecek Teknisyen', 'cevap_format' => '[personelSor]', 'sira' => 0],
            ['id' => 14424, 'uye_firma_id' => 2491, 'servis_durum_id' => 9786, 'soru' => 'Gidilecek Tarih', 'cevap_format' => '[tarihSor]', 'sira' => 0],
            ['id' => 14425, 'uye_firma_id' => 2491, 'servis_durum_id' => 9787, 'soru' => '?', 'cevap_format' => '[aciklamaSor]', 'sira' => 0],
            ['id' => 14426, 'uye_firma_id' => 2491, 'servis_durum_id' => 9786, 'soru' => '?', 'cevap_format' => '[aciklamaSor]', 'sira' => 0],
            ['id' => 14427, 'uye_firma_id' => 2491, 'servis_durum_id' => 9526, 'soru' => 'Teslimat Tarihi', 'cevap_format' => '[tarihSor]', 'sira' => 0],
        ];

        // Eski verileri temizle
        DB::table('servisdurum_sorulari')->delete();
        // ID'ler manuel atandığı için truncate yerine delete kullanıldı.

        // Toplu ekleme
        DB::table('servisdurum_sorulari')->insert($sorular);

        $this->command->info('Servis durum soruları başarıyla eklendi!');
    }
}

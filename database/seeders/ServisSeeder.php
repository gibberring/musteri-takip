<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ServisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // İlişkili tablolardaki ID'leri al
        $musteriIds = DB::table('musteriler')->pluck('id');
        $markaIds = DB::table('tnm_markalar')->pluck('id');
        $cihazTuruIds = DB::table('tnm_cihazturleri')->pluck('id');

        if ($musteriIds->isEmpty() || $markaIds->isEmpty() || $cihazTuruIds->isEmpty()) {
            $this->command->error('Servis eklemek için gerekli müşteri, marka veya cihaz türü bulunamadı!');
            return;
        }

        $dataToInsert = [];
        $idCounter = 1;
        $now = Carbon::now();

        foreach ($musteriIds as $musteriId) {
            $dataToInsert[] = [
                'id' => $idCounter++,
                'musteri_id' => $musteriId,
                'marka_id' => $markaIds->random(),
                'cihaz_tur_id' => $cihazTuruIds->random(),
                'cihaz_model' => 'BK5404',
                'cihaz_arizasi' => 'çalışmıyor',
                'servis_durum_id' => 9097, // Beklemede durumu ID'si
                'tarih' => $now->format('Y-m-d'), // Örnek tarih formatı
                'saat' => $now->format('H:i:s'), // Örnek saat formatı
                // Diğer nullable sütunlar varsayılan olarak null olacak
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Eski verileri sil
        DB::table('servisler')->delete();
        // DB::statement('ALTER TABLE servisler AUTO_INCREMENT = 1;'); // MySQL için örnek

        // Toplu ekleme
        DB::table('servisler')->insert($dataToInsert);

        $this->command->info(count($musteriIds) . ' servis kaydı başarıyla eklendi!');
    }
}

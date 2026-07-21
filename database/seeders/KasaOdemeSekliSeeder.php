<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KasaOdemeSekliSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $odemeSekilleri = [
            ['ad' => 'Nakit', 'sira' => 1],
            ['ad' => 'EFT/Havale', 'sira' => 2],
            ['ad' => 'Kredi Kartı', 'sira' => 3],
            ['ad' => 'Senet', 'sira' => 4],
            ['ad' => 'Diğer', 'sira' => 5], 
        ];

        foreach ($odemeSekilleri as $sekil) {
            DB::table('kasa_odeme_sekli')->insert([
                'ad' => $sekil['ad'],
                'uye_firma_id' => null,
                'sira' => $sekil['sira'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}

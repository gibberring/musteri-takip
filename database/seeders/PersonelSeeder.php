<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Personel;
use Illuminate\Support\Facades\Hash;

class PersonelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Personel::create([
            'id' => 1,
            'ad' => 'Yasin',
            'nick' => 'yasin',
            'sifre' => Hash::make('deneme123!'),
            'aktif' => 1,
            'servis_fis_kullanabilir' => 1,
            'e_fis_verebilir' => 1,
            'tarih' => now()->format('Y-m-d'),
            'saat' => now()->format('H:i:s'),
            'son_giris' => now()->format('Y-m-d H:i:s'),
            'toplam_giris' => 0
        ]);
    }
} 
<?php

namespace Database\Seeders;

// use App\Models\User; // User modeli yerine Personel kullanacağız
use App\Models\Personel; // Personel modelini ekledik
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash; // Hash facade'ını ekledik

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User factory'si yerine Personel oluşturuyoruz
        // User::factory(10)->create();
        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // İstediğiniz test personelini oluşturun
        Personel::create([
            'uye_firma_id' => null, // Varsa bir firma ID'si atanabilir
            'kaydeden_personel_id' => null, // Kendisi ilk personel olabilir
            'ad' => 'Yasin İnan',
            'nick' => 'yasin',
            'sifre' => Hash::make('deneme123!'), // Şifre hash'lendi
            'poz_id' => 1071,
            'aktif' => 1,
            'tel1' => null,
            'tel2' => null,
            'ilce_id' => null,
            'il_id' => null,
            'adres' => null,
            'resim' => null,
            'vno' => null,
            'is_basi_tarih' => null,
            'email' => 'yasin.inan@example.com', // Örnek bir email, null olabilir veya gerçek olabilir
            'servis_fis_kullanabilir' => null,
            'yazici_icerigi' => null,
            'yazici_genisligi' => null,
            'mesai_basladimi' => 1,
            'fis_firma' => null,
            'fis_tel' => null,
            'fis_adres' => null,
            'e_fis_verebilir' => null,
        ]);

        // İl/İlçe Seeder'ını çağırın
        $this->call([
            IlIlceSeeder::class,
            MarkaSeeder::class,
            CihazTuruSeeder::class,
            ServisDurumSeeder::class,
            ServisDurumSoruSeeder::class,
            MusteriSeeder::class,
            ServisSeeder::class,
            PersonelSeeder::class,
            TnmPersonelPozisyonSeeder::class,
            KasaOdemeSekliSeeder::class,
            KasaOdemeTuruSeeder::class,
            KasaSeeder::class,
        ]);
    }
}

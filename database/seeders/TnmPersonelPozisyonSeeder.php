<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TnmPersonelPozisyonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // --- Patron (ID 1071) rolünü özel izinlerle yönet ---
        $patronId = 1071;
        $patronSpecificData = [
            'ad' => 'Patron', // Bu ID için adın 'Patron' olduğundan emin olalım
            'uye_firma_id' => null,
            'izin_servisler' => true,
            'izin_musteriler' => true,
            'izin_personel' => true,
            'izin_stoklar' => true,
            'izin_kasa' => true,
            'izin_ayar' => true,
            'izin_uyelik' => true,
            'izin_servissilme' => true,
            'izin_servis_islem_silme' => true,
            'asamayetkileri' => ',9097,9098,9099,9100,9102,9103,9104,9105,9106,9110,9113,9114,9115,9116,9117,9334,9358,9359,9477,9524,9526,',
            'izin_topluyonlendirme' => true,
            // 'izin_servis_islem_silme' alanı kullanıcı tarafından belirtilmedi,
            // bu nedenle eklenirse veritabanı varsayılanını (false) kullanacak veya güncellemede mevcut değerini koruyacaktır.
            'updated_at' => $now,
        ];

        // Patron (ID 1071) kaydı yoksa created_at alanını ayarla
        if (!DB::table('tnm_personel_pozisyon')->where('id', $patronId)->exists()) {
            $patronSpecificData['created_at'] = $now;
        }

        DB::table('tnm_personel_pozisyon')->updateOrInsert(
            ['id' => $patronId],
            $patronSpecificData
        );

        // --- Operatör (ID 1073) rolünü özel izinlerle yönet ---
        $operatorId = 1073;
        $operatorSpecificData = [
            'ad' => 'Operatör', // Bu ID için adın 'Operatör' olduğundan emin olalım
            'uye_firma_id' => null,
            'izin_servisler' => true, // 1 ise true
            'izin_musteriler' => 2,    // Değer 2 olarak kalacak
            'izin_personel' => false, // 0 ise false
            'izin_stoklar' => false,
            'izin_kasa' => false,
            'izin_ayar' => false,
            'izin_uyelik' => false,
            'izin_servissilme' => false,
            'izin_servis_islem_silme' => true,
            'asamayetkileri' => ',9097,9099,9100,9103,9104,9106,9110,9114,9115,9116,9117,9334,9784,',
            'izin_topluyonlendirme' => false,
            // 'izin_servis_islem_silme' alanı kullanıcı tarafından belirtilmedi,
            // bu nedenle eklenirse veritabanı varsayılanını (false) kullanacak veya güncellemede mevcut değerini koruyacaktır.
            'updated_at' => $now,
        ];

        // Operatör (ID 1073) kaydı yoksa created_at alanını ayarla
        if (!DB::table('tnm_personel_pozisyon')->where('id', $operatorId)->exists()) {
            $operatorSpecificData['created_at'] = $now;
        }

        DB::table('tnm_personel_pozisyon')->updateOrInsert(
            ['id' => $operatorId],
            $operatorSpecificData
        );

        // --- Teknisyen (ID 1074) rolünü özel izinlerle yönet ---
        $teknisyenId = 1074;
        $teknisyenSpecificData = [
            'ad' => 'Teknisyen', // Bu ID için adın 'Teknisyen' olduğundan emin olalım
            'uye_firma_id' => null,
            'izin_servisler' => true,    // 2 -> true (izin var)
            'izin_musteriler' => false,  // 0 -> false (izin yok)
            'izin_personel' => false,   // 0 -> false
            'izin_stoklar' => false,    // 0 -> false
            'izin_kasa' => true,       // 2 -> true (izin var)
            'izin_ayar' => false,       // 0 -> false
            'izin_uyelik' => false,      // 0 -> false
            'izin_servissilme' => false, // 0 -> false
            'izin_servis_islem_silme' => false,
            'asamayetkileri' => ',9100,9102,9103,9104,9105,9106,9110,9115,9477,9528,9784,9787,',
            'izin_topluyonlendirme' => false, // 0 -> false
            // 'izin_servis_islem_silme' alanı kullanıcı tarafından belirtilmedi,
            // bu nedenle eklenirse veritabanı varsayılanını (false) kullanacak veya güncellemede mevcut değerini koruyacaktır.
            'updated_at' => $now,
        ];

        // Teknisyen (ID 1074) kaydı yoksa created_at alanını ayarla
        if (!DB::table('tnm_personel_pozisyon')->where('id', $teknisyenId)->exists()) {
            $teknisyenSpecificData['created_at'] = $now;
        }

        DB::table('tnm_personel_pozisyon')->updateOrInsert(
            ['id' => $teknisyenId],
            $teknisyenSpecificData
        );

        // --- G.Müdür (ID 1075) rolünü özel izinlerle yönet ---
        $gMudurId = 1075;
        $gMudurSpecificData = [
            'ad' => 'G.Müdür', // Bu ID için adın 'G.Müdür' olduğundan emin olalım
            'uye_firma_id' => null,
            'izin_servisler' => true,    // 1 -> true
            'izin_musteriler' => true,   // 1 -> true
            'izin_personel' => true,    // 1 -> true
            'izin_stoklar' => true,     // 1 -> true
            'izin_kasa' => true,        // 3 -> true
            'izin_ayar' => false,       // 0 -> false
            'izin_uyelik' => false,      // 0 -> false
            'izin_servissilme' => false, // 0 -> false
            'izin_servis_islem_silme' => true,
            'asamayetkileri' => ',9097,9098,9099,9100,9102,9103,9104,9105,9106,9110,9113,9114,9115,9116,9117,9334,9358,9359,9477,9524,9526,9528,9784,9785,9786,9787,',
            'izin_topluyonlendirme' => true, // 1 -> true
            // 'izin_servis_islem_silme' alanı kullanıcı tarafından belirtilmedi,
            // bu nedenle eklenirse veritabanı varsayılanını (false) kullanacak veya güncellemede mevcut değerini koruyacaktır.
            'updated_at' => $now,
        ];

        // G.Müdür (ID 1075) kaydı yoksa created_at alanını ayarla
        if (!DB::table('tnm_personel_pozisyon')->where('id', $gMudurId)->exists()) {
            $gMudurSpecificData['created_at'] = $now;
        }

        DB::table('tnm_personel_pozisyon')->updateOrInsert(
            ['id' => $gMudurId],
            $gMudurSpecificData
        );

        // --- Harici Operatör (ID 1076) rolünü özel izinlerle yönet ---
        $idariIslerId = 1076;
        $idariIslerSpecificData = [
            'ad' => 'Harici Operatör', // Bu ID için adın 'Harici Operatör' olduğundan emin olalım
            'uye_firma_id' => null,
            'izin_servisler' => true,    // 1 -> true
            'izin_musteriler' => true,   // 1 -> true
            'izin_personel' => false,   // 0 -> false
            'izin_stoklar' => false,    // 0 -> false
            'izin_kasa' => false,       // 0 -> false
            'izin_ayar' => false,       // 0 -> false
            'izin_uyelik' => false,      // 0 -> false
            'izin_servissilme' => false, // 0 -> false
            'izin_servis_islem_silme' => true,
            'asamayetkileri' => ',9097,9098,9099,9100,9102,9103,9104,9105,9106,9110,9113,9114,9115,9116,9117,9334,9358,9477,9524,9526,9784,9785,9786,',
            'izin_topluyonlendirme' => false, // 0 -> false
            // 'izin_servis_islem_silme' alanı kullanıcı tarafından belirtilmedi,
            // bu nedenle eklenirse veritabanı varsayılanını (false) kullanacak veya güncellemede mevcut değerini koruyacaktır.
            'updated_at' => $now,
        ];

        // İdari İşler (ID 1076) kaydı yoksa created_at alanını ayarla
        if (!DB::table('tnm_personel_pozisyon')->where('id', $idariIslerId)->exists()) {
            $idariIslerSpecificData['created_at'] = $now;
        }

        DB::table('tnm_personel_pozisyon')->updateOrInsert(
            ['id' => $idariIslerId],
            $idariIslerSpecificData
        );

        // --- T.Ş.R.N Teknisyen (ID 1077) rolünü özel izinlerle yönet ---
        $tsrnTeknisyenId = 1077;
        $tsrnTeknisyenSpecificData = [
            'ad' => 'T.Ş.R.N Teknisyen', // Bu ID için adın 'T.Ş.R.N Teknisyen' olduğundan emin olalım (seeder'daki mevcut isimlendirme)
            'uye_firma_id' => null,
            'izin_servisler' => true,    // 2 -> true
            'izin_musteriler' => false,   // 0 -> false
            'izin_personel' => false,   // 0 -> false
            'izin_stoklar' => false,    // 0 -> false
            'izin_kasa' => true,       // 2 -> true
            'izin_ayar' => false,       // 0 -> false
            'izin_uyelik' => false,      // 0 -> false
            'izin_servissilme' => false, // 0 -> false
            'izin_servis_islem_silme' => false,
            'asamayetkileri' => ',9102,9103,9104,9105,9106,9110,9115,9477,9526,',
            'izin_topluyonlendirme' => false, // 0 -> false
            // 'izin_servis_islem_silme' alanı kullanıcı tarafından belirtilmedi,
            // bu nedenle eklenirse veritabanı varsayılanını (false) kullanacak veya güncellemede mevcut değerini koruyacaktır.
            'updated_at' => $now,
        ];

        // T.Ş.R.N Teknisyen (ID 1077) kaydı yoksa created_at alanını ayarla
        if (!DB::table('tnm_personel_pozisyon')->where('id', $tsrnTeknisyenId)->exists()) {
            $tsrnTeknisyenSpecificData['created_at'] = $now;
        }

        DB::table('tnm_personel_pozisyon')->updateOrInsert(
            ['id' => $tsrnTeknisyenId],
            $tsrnTeknisyenSpecificData
        );

        // --- Atölye (ID 1078) rolünü özel izinlerle yönet ---
        $atolyeId = 1078;
        $atolyeSpecificData = [
            'ad' => 'Atölye', // Bu ID için adın 'Atölye' olduğundan emin olalım
            'uye_firma_id' => null,
            'izin_servisler' => true,    // 3 -> true
            'izin_musteriler' => true,   // 2 -> true
            'izin_personel' => false,   // 0 -> false
            'izin_stoklar' => false,    // 0 -> false
            'izin_kasa' => false,       // 0 -> false
            'izin_ayar' => false,       // 0 -> false
            'izin_uyelik' => false,      // 0 -> false
            'izin_servissilme' => false, // 0 -> false
            'izin_servis_islem_silme' => false,
            'asamayetkileri' => ',9113,9114,9117,9526,9784,',
            'izin_topluyonlendirme' => false, // 0 -> false
            // 'izin_servis_islem_silme' alanı kullanıcı tarafından belirtilmedi,
            // bu nedenle eklenirse veritabanı varsayılanını (false) kullanacak veya güncellemede mevcut değerini koruyacaktır.
            'updated_at' => $now,
        ];

        // Atölye (ID 1078) kaydı yoksa created_at alanını ayarla
        if (!DB::table('tnm_personel_pozisyon')->where('id', $atolyeId)->exists()) {
            $atolyeSpecificData['created_at'] = $now;
        }

        DB::table('tnm_personel_pozisyon')->updateOrInsert(
            ['id' => $atolyeId],
            $atolyeSpecificData
        );

        // --- Harici Operatör (ID 1079) rolünü özel izinlerle yönet ---
        $hariciOperatorId = 1079;
        $hariciOperatorSpecificData = [
            'ad' => 'Harici Operatör', // Bu ID için adın 'Harici Operatör' olduğundan emin olalım
            'uye_firma_id' => null,
            'izin_servisler' => true,    // 4 -> true
            'izin_musteriler' => false,  // 0 -> false
            'izin_personel' => false,   // 0 -> false
            'izin_stoklar' => false,    // 0 -> false
            'izin_kasa' => false,       // 0 -> false
            'izin_ayar' => false,       // 0 -> false
            'izin_uyelik' => false,      // 0 -> false
            'izin_servissilme' => false, // 0 -> false
            'izin_servis_islem_silme' => false,
            'asamayetkileri' => ',9097,9100,9103,9104,9106,9110,9113,9114,9115,9116,9117,9334,9358,9359,9524,9784,9785,9786,',
            'izin_topluyonlendirme' => false, // 0 -> false
            // 'izin_servis_islem_silme' alanı kullanıcı tarafından belirtilmedi,
            // bu nedenle eklenirse veritabanı varsayılanını (false) kullanacak veya güncellemede mevcut değerini koruyacaktır.
            'updated_at' => $now,
        ];

        // Harici Operatör (ID 1079) kaydı yoksa created_at alanını ayarla
        if (!DB::table('tnm_personel_pozisyon')->where('id', $hariciOperatorId)->exists()) {
            $hariciOperatorSpecificData['created_at'] = $now;
        }

        DB::table('tnm_personel_pozisyon')->updateOrInsert(
            ['id' => $hariciOperatorId],
            $hariciOperatorSpecificData
        );

        // --- Muhasebe (ID 1080) rolünü özel izinlerle yönet ---
        $muhasebeId = 1080;
        $muhasebeSpecificData = [
            'ad' => 'Muhasebe', // Bu ID için adın 'Muhasebe' olduğundan emin olalım
            'uye_firma_id' => null,
            'izin_servisler' => true,    // 1 -> true
            'izin_musteriler' => true,   // 1 -> true
            'izin_personel' => true,    // 1 -> true
            'izin_stoklar' => false,    // 0 -> false
            'izin_kasa' => true,        // 1 -> true
            'izin_ayar' => false,       // 0 -> false
            'izin_uyelik' => false,      // 0 -> false
            'izin_servissilme' => true,  // 1 -> true
            'izin_servis_islem_silme' => true,
            'asamayetkileri' => ',9097,9098,9099,9100,9102,9103,9104,9105,9106,9110,9113,9114,9115,9116,9117,9334,9358,9359,9477,9524,9526,',
            'izin_topluyonlendirme' => true, // 1 -> true
            // 'izin_servis_islem_silme' alanı kullanıcı tarafından belirtilmedi,
            // bu nedenle eklenirse veritabanı varsayılanını (false) kullanacak veya güncellemede mevcut değerini koruyacaktır.
            'updated_at' => $now,
        ];

        // Muhasebe (ID 1080) kaydı yoksa created_at alanını ayarla
        if (!DB::table('tnm_personel_pozisyon')->where('id', $muhasebeId)->exists()) {
            $muhasebeSpecificData['created_at'] = $now;
        }

        DB::table('tnm_personel_pozisyon')->updateOrInsert(
            ['id' => $muhasebeId],
            $muhasebeSpecificData
        );

        // --- Diğer pozisyonları yönet ---
        $otherPozisyonlar = [
            // 'Patron', 'Operatör', 'Teknisyen', 'G.Müdür', 'İdari İşler', 'T.Ş.R.N Teknisyen', 'Atölye', 'Harici Operatör' ve 'Muhasebe' zaten yukarıda ID ile yönetildiği için bu listede olmamalı
            // 'Operatör', // Çıkarıldı
            // 'Teknisyen', // Çıkarıldı
            // 'G.Müdür', // Çıkarıldı
            // 'İdari İşler', // Çıkarıldı
            // 'T.Ş.R.N Teknisyen', // Çıkarıldı
            // 'Atölye', // Çıkarıldı
            // 'Harici Operatör', // Çıkarıldı
            // 'Muhasebe', // Çıkarıldı
        ];

        foreach ($otherPozisyonlar as $pozisyonAdi) {
            $defaultDataForOther = [
                'uye_firma_id' => null,
                'izin_servisler' => false,
                'izin_musteriler' => false,
                'izin_personel' => false,
                'izin_stoklar' => false,
                'izin_kasa' => false,
                'izin_ayar' => false,
                'izin_uyelik' => false,
                'izin_servissilme' => false,
                'izin_servis_islem_silme' => false, // Orijinal seeder'dan gelen varsayılan
                'asamayetkileri' => null,
                'izin_topluyonlendirme' => false,
                'updated_at' => $now,
            ];

            // Pozisyon adına göre kayıt yoksa created_at alanını ayarla
            if (!DB::table('tnm_personel_pozisyon')->where('ad', $pozisyonAdi)->exists()) {
                $defaultDataForOther['created_at'] = $now;
            }

            DB::table('tnm_personel_pozisyon')->updateOrInsert(
                ['ad' => $pozisyonAdi], // Ada göre bul veya ekle
                $defaultDataForOther  // Bu değerleri ayarla
            );
        }
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Foreign key constraint'leri burada topluca tanımlıyoruz.
        // Tüm tabloların oluşturulduğundan emin olduktan sonra çalışır.

        Schema::table('ilceler', function (Blueprint $table) {
            $table->foreign('il_id')->references('id')->on('iller')->onDelete('cascade'); // veya nullOnDelete() vs.
        });

        Schema::table('islemloglari', function (Blueprint $table) {
            $table->foreign('servis_id')->references('id')->on('servisler')->onDelete('cascade');
            $table->foreign('islemi_yapan_personel_id')->references('id')->on('personel')->onDelete('set null');
        });

        Schema::table('its_uye_firmalar', function (Blueprint $table) {
            $table->foreign('il_id')->references('id')->on('iller')->onDelete('set null');
            $table->foreign('ilce_id')->references('id')->on('ilceler')->onDelete('set null');
            $table->foreign('personel_id')->references('id')->on('personel')->onDelete('set null');
        });

        Schema::table('its_uye_odemeleri', function (Blueprint $table) {
            $table->foreign('uye_firma_id')->references('id')->on('its_uye_firmalar')->onDelete('cascade');
            $table->foreign('personel_id')->references('id')->on('personel')->onDelete('set null');
        });

        Schema::table('kasa', function (Blueprint $table) {
            $table->foreign('servis_id')->references('id')->on('servisler')->onDelete('set null');
            $table->foreign('odeme_turu_id')->references('id')->on('kasa_odeme_turu')->onDelete('set null');
            $table->foreign('odeme_sekli_id')->references('id')->on('kasa_odeme_sekli')->onDelete('set null');
            $table->foreign('uye_firma_id')->references('id')->on('its_uye_firmalar')->onDelete('cascade');
            $table->foreign('personel_id')->references('id')->on('personel')->onDelete('set null');
            $table->foreign('tedarikci_id')->references('id')->on('servis_tedarikciler')->onDelete('set null');
            $table->foreign('ilgili_personel_id')->references('id')->on('personel')->onDelete('set null');
            $table->foreign('silen_kisi_id')->references('id')->on('personel')->onDelete('set null');
        });

        Schema::table('kasa_odeme_turu', function (Blueprint $table) {
            $table->foreign('firma_id')->references('id')->on('its_uye_firmalar')->onDelete('cascade');
        });

        Schema::table('mesajlar', function (Blueprint $table) {
            $table->foreign('mesaj_kisi_id')->references('id')->on('mesaj_kisiler')->onDelete('cascade');
            $table->foreign('uye_firma_id')->references('id')->on('its_uye_firmalar')->onDelete('cascade');
            $table->foreign('gonderen_personel_id')->references('id')->on('personel')->onDelete('cascade');
            $table->foreign('alici_personel_id')->references('id')->on('personel')->onDelete('cascade');
        });

        Schema::table('mesaj_kisiler', function (Blueprint $table) {
            $table->foreign('uye_firma_id')->references('id')->on('its_uye_firmalar')->onDelete('cascade');
            $table->foreign('personel1_id')->references('id')->on('personel')->onDelete('cascade');
            $table->foreign('personel2_id')->references('id')->on('personel')->onDelete('cascade');
        });

        Schema::table('musteriler', function (Blueprint $table) {
            $table->foreign('il_id')->references('id')->on('iller')->onDelete('set null');
            $table->foreign('ilce_id')->references('id')->on('ilceler')->onDelete('set null');
            $table->foreign('kat')->references('id')->on('musteri_kategori')->onDelete('set null');
            $table->foreign('uye_firma_id')->references('id')->on('its_uye_firmalar')->onDelete('cascade');
            $table->foreign('personel_id')->references('id')->on('personel')->onDelete('set null');
        });

        Schema::table('musteri_soru_cevaplari', function (Blueprint $table) {
            $table->foreign('musteri_id')->references('id')->on('musteriler')->onDelete('cascade');
            $table->foreign('soru_id')->references('id')->on('musteri_sorulari')->onDelete('cascade');
        });

        Schema::table('personel', function (Blueprint $table) {
            $table->foreign('poz_id')->references('id')->on('tnm_personel_pozisyon')->onDelete('set null');
            $table->foreign('il_id')->references('id')->on('iller')->onDelete('set null');
            $table->foreign('ilce_id')->references('id')->on('ilceler')->onDelete('set null');
            $table->foreign('uye_firma_id')->references('id')->on('its_uye_firmalar')->onDelete('cascade');
            $table->foreign('kaydeden_personel_id')->references('id')->on('personel')->onDelete('set null');
        });

        Schema::table('personel_soru_cevaplari', function (Blueprint $table) {
            $table->foreign('personel_id')->references('id')->on('personel')->onDelete('cascade');
            $table->foreign('soru_id')->references('id')->on('personel_sorulari')->onDelete('cascade');
        });

        Schema::table('personel_sorulari', function (Blueprint $table) {
            $table->foreign('uye_firma_id')->references('id')->on('its_uye_firmalar')->onDelete('cascade');
        });

        Schema::table('servisdurum_cevap0', function (Blueprint $table) {
            $table->foreign('servis_id')->references('id')->on('servisler')->onDelete('cascade');
            $table->foreign('servis_durum_id')->references('id')->on('servis_durum')->onDelete('cascade');
            $table->foreign('uye_firma_id')->references('id')->on('its_uye_firmalar')->onDelete('cascade');
            $table->foreign('personel_id')->references('id')->on('personel')->onDelete('set null');
        });

        Schema::table('servisdurum_cevaplari', function (Blueprint $table) {
            $table->foreign('soru_id')->references('id')->on('servisdurum_sorulari')->onDelete('cascade');
            $table->foreign('durum_cevap0_id')->references('id')->on('servisdurum_cevap0')->onDelete('cascade');
            $table->foreign('uye_firma_id')->references('id')->on('its_uye_firmalar')->onDelete('cascade');
        });

        Schema::table('servisdurum_sorulari', function (Blueprint $table) {
            $table->foreign('servis_durum_id')->references('id')->on('servis_durum')->onDelete('cascade');
            $table->foreign('uye_firma_id')->references('id')->on('its_uye_firmalar')->onDelete('cascade');
        });

        Schema::table('servis_fisleri', function (Blueprint $table) {
            $table->foreign('servis_id')->references('id')->on('servisler')->onDelete('cascade');
            $table->foreign('olusturan_personel_id')->references('id')->on('personel')->onDelete('set null');
        });

        Schema::table('servisler', function (Blueprint $table) {
            $table->foreign('kaynak_id')->references('id')->on('servis_kaynaklari')->onDelete('set null');
            $table->foreign('musteri_id')->references('id')->on('musteriler')->onDelete('set null');
            $table->foreign('marka_id')->references('id')->on('tnm_markalar')->onDelete('set null');
            $table->foreign('cihaz_tur_id')->references('id')->on('tnm_cihazturleri')->onDelete('set null');
            $table->foreign('servis_durum_id')->references('id')->on('servis_durum')->onDelete('set null');
            $table->foreign('uye_firma_id')->references('id')->on('its_uye_firmalar')->onDelete('cascade');
            $table->foreign('personel_id')->references('id')->on('personel')->onDelete('set null');
            $table->foreign('silen_kisi_id')->references('id')->on('personel')->onDelete('set null');
        });

        Schema::table('servis_resimleri', function (Blueprint $table) {
            $table->foreign('servis_id')->references('id')->on('servisler')->onDelete('cascade');
        });

        Schema::table('sms_gecmisi', function (Blueprint $table) {
            $table->foreign('servis_id')->references('id')->on('servisler')->onDelete('cascade');
        });

        Schema::table('yonlendirilen_personeller', function (Blueprint $table) {
            $table->foreign('giden_personel_id')->references('id')->on('personel')->onDelete('cascade');
        });

        Schema::table('servis_kaynaklari', function (Blueprint $table) {
            $table->foreign('uye_firma_id')->references('id')->on('its_uye_firmalar')->onDelete('cascade');
        });

        Schema::table('servis_durum', function (Blueprint $table) {
            $table->foreign('uye_firma_id')->references('id')->on('its_uye_firmalar')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Foreign key'leri kaldırmak için ilgili tablolarda dropForeign kullanılmalı.
        // Örnekler:
        Schema::table('ilceler', function (Blueprint $table) { $table->dropForeign(['il_id']); });
        Schema::table('islemloglari', function (Blueprint $table) { $table->dropForeign(['servis_id', 'islemi_yapan_personel_id']); });
        Schema::table('its_uye_firmalar', function (Blueprint $table) { $table->dropForeign(['il_id', 'ilce_id', 'personel_id']); });
        Schema::table('its_uye_odemeleri', function (Blueprint $table) { $table->dropForeign(['uye_firma_id', 'personel_id']); });
        Schema::table('kasa', function (Blueprint $table) { $table->dropForeign(['servis_id', 'odeme_turu_id', 'odeme_sekli_id', 'uye_firma_id', 'personel_id', 'tedarikci_id', 'ilgili_personel_id', 'silen_kisi_id']); });
        Schema::table('kasa_odeme_turu', function (Blueprint $table) { $table->dropForeign(['firma_id']); });
        Schema::table('mesajlar', function (Blueprint $table) { $table->dropForeign(['mesaj_kisi_id', 'uye_firma_id', 'gonderen_personel_id', 'alici_personel_id']); });
        Schema::table('mesaj_kisiler', function (Blueprint $table) { $table->dropForeign(['uye_firma_id', 'personel1_id', 'personel2_id']); });
        Schema::table('musteriler', function (Blueprint $table) { $table->dropForeign(['il_id', 'ilce_id', 'kat', 'uye_firma_id', 'personel_id']); });
        Schema::table('musteri_soru_cevaplari', function (Blueprint $table) { $table->dropForeign(['musteri_id', 'soru_id']); });
        Schema::table('personel', function (Blueprint $table) { $table->dropForeign(['poz_id', 'il_id', 'ilce_id', 'uye_firma_id', 'kaydeden_personel_id']); });
        Schema::table('personel_soru_cevaplari', function (Blueprint $table) { $table->dropForeign(['personel_id', 'soru_id']); });
        Schema::table('personel_sorulari', function (Blueprint $table) { $table->dropForeign(['uye_firma_id']); });
        Schema::table('servisdurum_cevap0', function (Blueprint $table) { $table->dropForeign(['servis_id', 'servis_durum_id', 'uye_firma_id', 'personel_id']); });
        Schema::table('servisdurum_cevaplari', function (Blueprint $table) { $table->dropForeign(['soru_id', 'durum_cevap0_id', 'uye_firma_id']); });
        Schema::table('servisdurum_sorulari', function (Blueprint $table) { $table->dropForeign(['servis_durum_id', 'uye_firma_id']); });
        Schema::table('servis_fisleri', function (Blueprint $table) { $table->dropForeign(['servis_id', 'olusturan_personel_id']); });
        Schema::table('servis_durum', function (Blueprint $table) { $table->dropForeign(['uye_firma_id']); });
        Schema::table('servisler', function (Blueprint $table) { $table->dropForeign(['kaynak_id', 'musteri_id', 'marka_id', 'cihaz_tur_id', 'servis_durum_id', 'uye_firma_id', 'personel_id', 'silen_kisi_id']); });
        Schema::table('servis_resimleri', function (Blueprint $table) { $table->dropForeign(['servis_id']); });
        Schema::table('sms_gecmisi', function (Blueprint $table) { $table->dropForeign(['servis_id']); });
        Schema::table('yonlendirilen_personeller', function (Blueprint $table) { $table->dropForeign(['giden_personel_id']); });
        Schema::table('servis_kaynaklari', function (Blueprint $table) { $table->dropForeign(['uye_firma_id']); });
    }
}; 
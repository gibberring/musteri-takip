<?php

namespace Tests\Feature;

use App\Models\Kasa;
use App\Models\Personel;
use App\Models\Servis;
use App\Services\TeknisyenGunlukKasaHesaplayici;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TeknisyenGunlukKasaHesaplayiciTest extends TestCase
{
    private const GUN = '2026-09-24';

    protected function setUp(): void
    {
        parent::setUp();
        $this->createSchema();
    }

    public function test_yuzdeli_teknisyende_ciro_teknisyen_ve_firma_payi(): void
    {
        $t = $this->makeTeknisyen('Yuzdeli', '60_40');
        $this->makeKasa($t->id, 1000, 1);
        $this->makeKasa($t->id, 200, -1);

        $o = $this->hesapla($t)[$t->id];

        $this->assertEquals(1000.0, $o['gelir']);
        $this->assertEquals(200.0, $o['gider']);
        $this->assertFalse($o['adetli']);
        $this->assertEquals(480.0, $o['teknisyen_payi']); // (1000-200)*60/100
        $this->assertEquals(320.0, $o['firma_payi']);     // (1000-200)*40/100
    }

    public function test_tipi_olmayan_teknisyende_net_tutar_firmaya_yazilir(): void
    {
        $t = $this->makeTeknisyen('Tipsiz', null);
        $this->makeKasa($t->id, 500, 1);

        $o = $this->hesapla($t)[$t->id];

        $this->assertEquals(0.0, $o['teknisyen_payi']);
        $this->assertEquals(500.0, $o['firma_payi']);
    }

    public function test_adetli_teknisyende_firma_payi_adet_ile_hesaplanir(): void
    {
        $t = $this->makeTeknisyen('Adetli', 'adetli', 100);
        $this->makeServis($t->id, self::GUN, 9098);
        $this->makeServis($t->id, self::GUN, 9099);
        $this->makeServis($t->id, self::GUN, 9106); // adetli dışı durum
        $this->makeServis($t->id, '2026-09-23', 9098); // başka gün
        $this->makeKasa($t->id, 300, -1);

        $o = $this->hesapla($t)[$t->id];

        $this->assertTrue($o['adetli']);
        $this->assertEquals(0.0, $o['teknisyen_payi']);
        $this->assertEquals(-100.0, $o['firma_payi']); // 2*100 - 300 gider
    }

    public function test_silinmis_bekleyen_ve_baska_gunun_kayitlari_sayilmaz(): void
    {
        $t = $this->makeTeknisyen('Filtre', '50_50');
        $this->makeKasa($t->id, 100, 1);
        $this->makeKasa($t->id, 999, 1, silindi: 1);
        $this->makeKasa($t->id, 999, 1, gerceklesme: 0);
        $this->makeKasa($t->id, 999, 1, gun: '2026-09-23');

        $o = $this->hesapla($t)[$t->id];

        $this->assertEquals(100.0, $o['gelir']);
        $this->assertEquals(50.0, $o['teknisyen_payi']);
        $this->assertEquals(50.0, $o['firma_payi']);
    }

    public function test_tahsil_eden_ilgili_personel_uzerinden_ayrilir(): void
    {
        $a = $this->makeTeknisyen('A', '50_50');
        $b = $this->makeTeknisyen('B', '50_50');
        $this->makeKasa($a->id, 100, 1);
        $this->makeKasa($b->id, 400, 1);

        $sonuc = $this->hesapla($a, $b);

        $this->assertEquals(100.0, $sonuc[$a->id]['gelir']);
        $this->assertEquals(400.0, $sonuc[$b->id]['gelir']);
    }

    public function test_hareketi_olmayan_teknisyen_sifir_doner(): void
    {
        $t = $this->makeTeknisyen('Bos', '60_40');

        $o = $this->hesapla($t)[$t->id];

        $this->assertEquals(0.0, $o['gelir']);
        $this->assertEquals(0.0, $o['teknisyen_payi']);
        $this->assertEquals(0.0, $o['firma_payi']);
    }

    private function hesapla(Personel ...$teknisyenler): array
    {
        return app(TeknisyenGunlukKasaHesaplayici::class)->hesapla(collect($teknisyenler), self::GUN);
    }

    private function makeTeknisyen(string $ad, ?string $calismaSekli, ?float $adetTutar = null): Personel
    {
        $p = new Personel();
        $p->ad = $ad;
        $p->sifre = 'x';
        $p->poz_id = 1077;
        $p->aktif = 1;
        $p->calisma_sekli_type = $calismaSekli;
        $p->calisma_sekli_adet_tutar = $adetTutar;
        $p->save();

        return $p;
    }

    private function makeKasa(int $ilgiliPersonelId, float $tutar, int $yon, int $silindi = 0, int $gerceklesme = 1, string $gun = self::GUN): void
    {
        $k = new Kasa();
        $k->ilgili_personel_id = $ilgiliPersonelId;
        $k->tutar = $tutar;
        $k->odeme_yonu = $yon;
        $k->islem_tarihi = $gun;
        $k->gerceklesme = $gerceklesme;
        $k->silindi = $silindi;
        $k->save();
    }

    private function makeServis(int $personelId, string $tarih, int $durumId): void
    {
        $s = new Servis();
        $s->personel_id = $personelId;
        $s->tarih = $tarih;
        $s->servis_durum_id = $durumId;
        $s->silindi = 0;
        $s->save();
    }

    private function createSchema(): void
    {
        Schema::dropIfExists('kasa');
        Schema::dropIfExists('servisler');
        Schema::dropIfExists('personel');

        Schema::create('personel', function (Blueprint $table) {
            $table->id();
            $table->string('ad')->nullable();
            $table->string('sifre');
            $table->unsignedBigInteger('poz_id')->nullable();
            $table->integer('aktif')->nullable();
            $table->string('calisma_sekli_type', 20)->nullable();
            $table->decimal('calisma_sekli_adet_tutar', 10, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('servisler', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('personel_id')->nullable();
            $table->unsignedBigInteger('servis_durum_id')->nullable();
            $table->string('tarih', 50)->nullable();
            $table->integer('silindi')->nullable();
            $table->timestamps();
        });

        Schema::create('kasa', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ilgili_personel_id')->nullable();
            $table->decimal('tutar', 18, 4)->nullable();
            $table->string('islem_tarihi', 50)->nullable();
            $table->integer('gerceklesme')->nullable();
            $table->tinyInteger('odeme_yonu')->nullable();
            $table->integer('silindi')->nullable();
            $table->timestamps();
        });
    }
}

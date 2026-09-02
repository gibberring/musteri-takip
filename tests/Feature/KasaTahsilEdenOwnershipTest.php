<?php

namespace Tests\Feature;

use App\Http\Controllers\KasaController;
use App\Models\Kasa;
use App\Models\Personel;
use App\Models\Servis;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use Tests\TestCase;

class KasaTahsilEdenOwnershipTest extends TestCase
{
    private const GUN = '2026-08-28';
    private const TAHSILAT = 11250.0;
    private const YENI_TEKNISYEN_DIGER_GELIR = 24825.0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createSchema();
    }

    public function test_servis_personel_degisse_kasa_tahsil_edende_kalir(): void
    {
        [$operator, $tahsilEden, $yeniTeknisyen, $kasa] = $this->seedYonlendirilmisTahsilat();

        $servis = Servis::find($kasa->servis_id);
        $servis->personel_id = $yeniTeknisyen->id;
        $servis->save();

        $kasa->refresh();

        $this->assertSame($tahsilEden->id, (int) $kasa->ilgili_personel_id);
        $this->assertSame($operator->id, (int) $kasa->personel_id);
        $this->assertSame($yeniTeknisyen->id, (int) Servis::find($kasa->servis_id)->personel_id);

        $this->assertEquals(self::TAHSILAT, $this->netKasaGelir($tahsilEden->id));
        $this->assertEquals(self::YENI_TEKNISYEN_DIGER_GELIR, $this->netKasaGelir($yeniTeknisyen->id));
        $this->assertEquals(0.0, $this->netKasaGelir($operator->id));
    }

    public function test_net_kasa_payi_taban_geliri_ilgili_personel_ile_hesaplar(): void
    {
        [, , $yeniTeknisyen] = $this->seedYonlendirilmisTahsilat();

        $servis = Servis::query()->first();
        $servis->personel_id = $yeniTeknisyen->id;
        $servis->save();

        $controller = app(KasaController::class);
        $method = new ReflectionMethod(KasaController::class, 'calculateTeknisyenPaylariToplam');
        $method->setAccessible(true);

        $pay = (float) $method->invoke($controller, self::GUN, self::GUN, [$yeniTeknisyen->id]);

        // 60/40: (24825 - 0) * 60 / 100 — 11250 yeni teknisyene taşınmaz
        $this->assertEquals(14895.0, $pay);
    }

    private function netKasaGelir(int $personelId): float
    {
        return (float) Kasa::query()
            ->forTahsilEden($personelId)
            ->whereBetween('islem_tarihi', [self::GUN, self::GUN])
            ->where('gerceklesme', 1)
            ->notDeleted()
            ->where('odeme_yonu', 1)
            ->sum('tutar');
    }

    /**
     * @return array{0: Personel, 1: Personel, 2: Personel, 3: Kasa}
     */
    private function seedYonlendirilmisTahsilat(): array
    {
        $operator = $this->makePersonel('Operator', 1073);
        $tahsilEden = $this->makePersonel('Aydin Efsun', 1077, '60_40');
        $yeniTeknisyen = $this->makePersonel('Ankara Efsun', 1077, '60_40');

        $servis = new Servis();
        $servis->personel_id = $tahsilEden->id;
        $servis->save();

        $kasa = new Kasa();
        $kasa->personel_id = $operator->id;
        $kasa->ilgili_personel_id = $tahsilEden->id;
        $kasa->servis_id = $servis->id;
        $kasa->tutar = self::TAHSILAT;
        $kasa->islem_tarihi = self::GUN;
        $kasa->gerceklesme = 1;
        $kasa->odeme_yonu = 1;
        $kasa->silindi = 0;
        $kasa->save();

        $diger = new Kasa();
        $diger->personel_id = $operator->id;
        $diger->ilgili_personel_id = $yeniTeknisyen->id;
        $diger->tutar = self::YENI_TEKNISYEN_DIGER_GELIR;
        $diger->islem_tarihi = self::GUN;
        $diger->gerceklesme = 1;
        $diger->odeme_yonu = 1;
        $diger->silindi = 0;
        $diger->save();

        return [$operator, $tahsilEden, $yeniTeknisyen, $kasa];
    }

    private function makePersonel(string $ad, int $pozId, ?string $calismaSekli = null): Personel
    {
        $personel = new Personel();
        $personel->ad = $ad;
        $personel->sifre = 'x';
        $personel->poz_id = $pozId;
        $personel->aktif = 1;
        $personel->mesai_basladimi = 1;
        $personel->calisma_sekli_type = $calismaSekli;
        $personel->save();

        return $personel;
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
            $table->integer('mesai_basladimi')->nullable();
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
            $table->unsignedBigInteger('personel_id')->nullable();
            $table->unsignedBigInteger('ilgili_personel_id')->nullable();
            $table->unsignedBigInteger('servis_id')->nullable();
            $table->decimal('tutar', 18, 4)->nullable();
            $table->string('islem_tarihi', 50)->nullable();
            $table->integer('gerceklesme')->nullable();
            $table->tinyInteger('odeme_yonu')->nullable();
            $table->integer('silindi')->nullable();
            $table->timestamps();
        });
    }
}

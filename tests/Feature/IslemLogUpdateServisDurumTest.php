<?php

namespace Tests\Feature;

use App\Http\Controllers\IslemLogController;
use App\Models\Islemloglari;
use App\Models\Personel;
use App\Models\Servis;
use App\Models\ServisDurumCevap;
use App\Models\ServisDurumCevap0;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

class IslemLogUpdateServisDurumTest extends TestCase
{
    private const DURUM_YONLENDIRILDI = 9098;
    private const DURUM_IPTAL = 9104;
    private const DURUM_FIYAT = 9106;
    private const SORU_TEKNISYEN = 13234;
    private const SORU_GIDIS = 13235;
    private const SORU_IPTAL = 13247;
    private const SORU_FIYAT = 13251;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createSchema();
        $this->seedLookupRows();
    }

    public function test_son_log_durumu_degisince_servis_ve_cevap_hizalanir(): void
    {
        $user = $this->makeOperator();
        $servis = $this->makeServis(self::DURUM_IPTAL);
        $this->makeLog($servis->id, self::DURUM_IPTAL, $user->id); // ara
        $sonLog = $this->makeLog($servis->id, self::DURUM_IPTAL, $user->id);
        $cevap0 = $this->makeCevap0($servis->id, self::DURUM_IPTAL);
        $cevap = $this->makeCevap($cevap0->id, self::SORU_IPTAL, 'iptal notu');

        $response = $this->updateLog($user, $sonLog->id, self::DURUM_FIYAT);

        $this->assertTrue($response->getData(true)['success'] ?? false);
        $this->assertSame(self::DURUM_FIYAT, (int) Servis::find($servis->id)->servis_durum_id);
        $this->assertSame(self::DURUM_FIYAT, (int) Islemloglari::find($sonLog->id)->servis_durum_id);
        $this->assertSame(self::DURUM_FIYAT, (int) ServisDurumCevap0::find($cevap0->id)->servis_durum_id);
        $this->assertSame(self::SORU_FIYAT, (int) ServisDurumCevap::find($cevap->id)->soru_id);
    }

    public function test_ara_log_duzenlenince_servis_durumu_degismez(): void
    {
        $user = $this->makeOperator();
        $servis = $this->makeServis(self::DURUM_IPTAL);
        $araLog = $this->makeLog($servis->id, self::DURUM_IPTAL, $user->id);
        $this->makeLog($servis->id, self::DURUM_IPTAL, $user->id); // son
        $cevap0 = $this->makeCevap0($servis->id, self::DURUM_IPTAL);
        $cevap = $this->makeCevap($cevap0->id, self::SORU_IPTAL, 'iptal notu');

        $response = $this->updateLog($user, $araLog->id, self::DURUM_FIYAT);

        $this->assertTrue($response->getData(true)['success'] ?? false);
        $this->assertSame(self::DURUM_FIYAT, (int) Islemloglari::find($araLog->id)->servis_durum_id);
        $this->assertSame(self::DURUM_IPTAL, (int) Servis::find($servis->id)->servis_durum_id);
        $this->assertSame(self::DURUM_IPTAL, (int) ServisDurumCevap0::find($cevap0->id)->servis_durum_id);
        $this->assertSame(self::SORU_IPTAL, (int) ServisDurumCevap::find($cevap->id)->soru_id);
    }

    public function test_son_log_silinince_servis_onceki_loga_geri_doner(): void
    {
        $user = $this->makeOperator();
        $servis = $this->makeServis(self::DURUM_FIYAT);
        $onceki = $this->makeLog($servis->id, self::DURUM_IPTAL, $user->id);
        $sonLog = $this->makeLog($servis->id, self::DURUM_FIYAT, $user->id);

        Auth::login($user);
        $response = app(IslemLogController::class)->destroy($sonLog->id);

        $this->assertTrue($response->getData(true)['success'] ?? false);
        $this->assertSame(1, (int) Islemloglari::find($sonLog->id)->silindi);
        $this->assertSame(self::DURUM_IPTAL, (int) Servis::find($servis->id)->servis_durum_id);
        $this->assertSame(self::DURUM_IPTAL, (int) Islemloglari::find($onceki->id)->servis_durum_id);
    }

    public function test_ara_log_silinince_servis_son_logda_kalir(): void
    {
        $user = $this->makeOperator();
        $servis = $this->makeServis(self::DURUM_FIYAT);
        $araLog = $this->makeLog($servis->id, self::DURUM_IPTAL, $user->id);
        $this->makeLog($servis->id, self::DURUM_FIYAT, $user->id);

        Auth::login($user);
        $response = app(IslemLogController::class)->destroy($araLog->id);

        $this->assertTrue($response->getData(true)['success'] ?? false);
        $this->assertSame(self::DURUM_FIYAT, (int) Servis::find($servis->id)->servis_durum_id);
    }

    public function test_son_log_9098den_cikinca_teknisyen_cevabi_baska_soruya_tasinmaz(): void
    {
        $user = $this->makeOperator();
        $servis = $this->makeServis(self::DURUM_YONLENDIRILDI);
        $servis->personel_id = 4001;
        $servis->save();
        $sonLog = $this->makeLog($servis->id, self::DURUM_YONLENDIRILDI, $user->id);
        $cevap0 = $this->makeCevap0($servis->id, self::DURUM_YONLENDIRILDI);
        $cevap = $this->makeCevap($cevap0->id, self::SORU_TEKNISYEN, '4001');

        $response = $this->updateLog($user, $sonLog->id, self::DURUM_FIYAT);

        $this->assertTrue($response->getData(true)['success'] ?? false);
        $this->assertSame(self::DURUM_FIYAT, (int) Servis::find($servis->id)->servis_durum_id);
        $this->assertSame(4001, (int) Servis::find($servis->id)->personel_id);
        $this->assertSame(self::DURUM_FIYAT, (int) ServisDurumCevap0::find($cevap0->id)->servis_durum_id);
        $this->assertSame(self::SORU_TEKNISYEN, (int) ServisDurumCevap::find($cevap->id)->soru_id);
    }

    private function updateLog(Personel $user, int $logId, int $yeniDurum)
    {
        Auth::login($user);
        $request = Request::create('/islemlog/' . $logId, 'PUT', [
            'servis_durum_id' => $yeniDurum,
            'tarih' => '2026-08-17',
            'saat' => '10:00',
            'aciklama' => 'duzenlendi',
        ]);

        return app(IslemLogController::class)->update($request, $logId);
    }

    private function makeOperator(): Personel
    {
        $user = new Personel();
        $user->ad = 'Test Operator';
        $user->sifre = 'x';
        $user->poz_id = 1073;
        $user->aktif = 1;
        $user->mesai_basladimi = 1;
        $user->save();

        return $user;
    }

    private function makeServis(int $durumId): Servis
    {
        $servis = new Servis();
        $servis->servis_durum_id = $durumId;
        $servis->save();

        return $servis;
    }

    private function makeLog(int $servisId, int $durumId, int $personelId): Islemloglari
    {
        $log = new Islemloglari();
        $log->servis_id = $servisId;
        $log->servis_durum_id = $durumId;
        $log->islemi_yapan_personel_id = $personelId;
        $log->tarih = '2026-08-17';
        $log->saat = '10:00';
        $log->aciklama = 'log';
        $log->silindi = 0;
        $log->save();

        return $log;
    }

    private function makeCevap0(int $servisId, int $durumId): ServisDurumCevap0
    {
        $cevap0 = new ServisDurumCevap0();
        $cevap0->servis_id = $servisId;
        $cevap0->servis_durum_id = $durumId;
        $cevap0->tarih = '2026-08-17';
        $cevap0->saat = '10:00:00';
        $cevap0->save();

        return $cevap0;
    }

    private function makeCevap(int $cevap0Id, int $soruId, string $cevapText): ServisDurumCevap
    {
        $cevap = new ServisDurumCevap();
        $cevap->durumCevap0_id = $cevap0Id;
        $cevap->soru_id = $soruId;
        $cevap->cevap = $cevapText;
        $cevap->save();

        return $cevap;
    }

    private function seedLookupRows(): void
    {
        DB::table('servis_durum')->insert([
            ['id' => self::DURUM_YONLENDIRILDI, 'ad' => 'Teknisyen Yonlendirildi'],
            ['id' => self::DURUM_IPTAL, 'ad' => 'Musteri Iptal Etti'],
            ['id' => self::DURUM_FIYAT, 'ad' => 'Fiyatta Anlasilamadi'],
        ]);
        DB::table('servisdurum_sorulari')->insert([
            ['id' => self::SORU_TEKNISYEN, 'servis_durum_id' => self::DURUM_YONLENDIRILDI, 'soru' => 'Teknisyen', 'cevap_format' => '[personelSor]', 'sira' => 0],
            ['id' => self::SORU_GIDIS, 'servis_durum_id' => self::DURUM_YONLENDIRILDI, 'soru' => 'Gidis Tarihi', 'cevap_format' => '[tarihSor]', 'sira' => 3],
            ['id' => self::SORU_IPTAL, 'servis_durum_id' => self::DURUM_IPTAL, 'soru' => 'Aciklama', 'cevap_format' => '[aciklamaSor]', 'sira' => 1],
            ['id' => self::SORU_FIYAT, 'servis_durum_id' => self::DURUM_FIYAT, 'soru' => 'Aciklama', 'cevap_format' => '[aciklamaSor]', 'sira' => 1],
        ]);
    }

    private function createSchema(): void
    {
        Schema::dropIfExists('servisdurum_cevaplari');
        Schema::dropIfExists('servisdurum_cevap0');
        Schema::dropIfExists('servisdurum_sorulari');
        Schema::dropIfExists('islemloglari');
        Schema::dropIfExists('servisler');
        Schema::dropIfExists('servis_durum');
        Schema::dropIfExists('personel');

        Schema::create('personel', function (Blueprint $table) {
            $table->id();
            $table->string('ad')->nullable();
            $table->string('sifre');
            $table->unsignedBigInteger('poz_id')->nullable();
            $table->integer('aktif')->nullable();
            $table->integer('mesai_basladimi')->nullable();
            $table->timestamps();
        });

        Schema::create('servis_durum', function (Blueprint $table) {
            $table->id();
            $table->string('ad')->nullable();
            $table->timestamps();
        });

        Schema::create('servisler', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('servis_durum_id')->nullable();
            $table->unsignedBigInteger('personel_id')->nullable();
            $table->timestamps();
        });

        Schema::create('islemloglari', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('islemi_yapan_personel_id')->nullable();
            $table->unsignedBigInteger('servis_id')->nullable();
            $table->unsignedBigInteger('servis_durum_id')->nullable();
            $table->string('tarih')->nullable();
            $table->string('saat')->nullable();
            $table->string('aciklama', 500)->nullable();
            $table->tinyInteger('silindi')->nullable();
            $table->unsignedBigInteger('silen_kisi_id')->nullable();
            $table->string('silinme_tarihi')->nullable();
        });

        Schema::create('servisdurum_sorulari', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('servis_durum_id')->nullable();
            $table->string('soru')->nullable();
            $table->string('cevap_format')->nullable();
            $table->integer('sira')->nullable();
        });

        Schema::create('servisdurum_cevap0', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('servis_id')->nullable();
            $table->unsignedBigInteger('servis_durum_id')->nullable();
            $table->string('tarih')->nullable();
            $table->string('saat')->nullable();
        });

        Schema::create('servisdurum_cevaplari', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('uye_firma_id')->nullable();
            $table->unsignedBigInteger('soru_id');
            $table->text('cevap');
            $table->unsignedBigInteger('durumCevap0_id');
            $table->timestamps();
        });
    }
}

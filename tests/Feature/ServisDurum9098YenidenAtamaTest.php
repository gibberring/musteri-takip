<?php

namespace Tests\Feature;

use App\Http\Controllers\IslemLogController;
use App\Http\Controllers\ServisController;
use App\Models\Islemloglari;
use App\Models\Personel;
use App\Models\Servis;
use App\Models\ServisDurumCevap;
use App\Models\ServisDurumCevap0;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ServisDurum9098YenidenAtamaTest extends TestCase
{
    private const DURUM_YONLENDIRILDI = 9098;
    private const DURUM_IPTAL = 9104;
    private const SORU_TEKNISYEN = 13234;
    private const SORU_GIDIS = 13235;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createSchema();
        $this->seedLookupRows();
    }

    public function test_9098_yeniden_atama_yeni_personel_ve_13234_eski_log_silinmez(): void
    {
        $operator = $this->makePersonel('Operator', 1073);
        $eskiTeknisyen = $this->makePersonel('Eski Teknisyen', 1077);
        $yeniTeknisyen = $this->makePersonel('Yeni Teknisyen', 1077);

        $servis = $this->makeServis(self::DURUM_YONLENDIRILDI, $eskiTeknisyen->id, '2026-09-02');
        $servis->teknisyen_goruldu_at = '2026-09-02 10:00:00';
        $servis->teknisyen_goruldu_personel_id = $eskiTeknisyen->id;
        $servis->save();

        $eskiLog = $this->makeLog($servis->id, self::DURUM_YONLENDIRILDI, $operator->id);
        $eskiCevap0 = $this->makeCevap0($servis->id, self::DURUM_YONLENDIRILDI);
        $eski13234 = $this->makeCevap($eskiCevap0->id, self::SORU_TEKNISYEN, (string) $eskiTeknisyen->id);
        $this->makeCevap($eskiCevap0->id, self::SORU_GIDIS, '2026-09-02');

        $yeniGidisTarihi = Carbon::today()->addDays(3)->format('Y-m-d');
        $response = $this->guncelleDurum($operator, $servis, $yeniTeknisyen->id, $yeniGidisTarihi);
        $payload = $response->getData(true);

        $this->assertTrue($payload['success'] ?? false, $payload['message'] ?? '');
        $this->assertSame(self::DURUM_YONLENDIRILDI, (int) Servis::find($servis->id)->servis_durum_id);
        $this->assertSame($yeniTeknisyen->id, (int) Servis::find($servis->id)->personel_id);
        $this->assertSame($yeniGidisTarihi, (string) Servis::find($servis->id)->tarih);

        $this->assertSame(0, (int) Islemloglari::find($eskiLog->id)->silindi);
        $this->assertSame(self::DURUM_YONLENDIRILDI, (int) Islemloglari::find($eskiLog->id)->servis_durum_id);
        $this->assertSame(2, Islemloglari::where('servis_id', $servis->id)->notDeleted()->count());

        $this->assertNotNull(ServisDurumCevap0::find($eskiCevap0->id));
        $this->assertSame((string) $yeniTeknisyen->id, (string) ServisDurumCevap::find($eski13234->id)->cevap);

        $son13234 = ServisDurumCevap::where('soru_id', self::SORU_TEKNISYEN)
            ->whereHas('durumCevap0', function ($q) use ($servis) {
                $q->where('servis_id', $servis->id)->where('servis_durum_id', self::DURUM_YONLENDIRILDI);
            })
            ->orderByDesc('id')
            ->first();
        $this->assertNotNull($son13234);
        $this->assertSame((string) $yeniTeknisyen->id, (string) $son13234->cevap);
        $this->assertNotSame($eski13234->id, $son13234->id);

        $fresh = Servis::find($servis->id);
        $this->assertNull($fresh->teknisyen_goruldu_at);
        $this->assertNull($fresh->teknisyen_goruldu_personel_id);
    }

    public function test_9098_ayni_teknisyen_ayni_tarih_noop(): void
    {
        $operator = $this->makePersonel('Operator', 1073);
        $teknisyen = $this->makePersonel('Teknisyen', 1077);
        $servis = $this->makeServis(self::DURUM_YONLENDIRILDI, $teknisyen->id, '2026-09-05');
        $eskiLog = $this->makeLog($servis->id, self::DURUM_YONLENDIRILDI, $operator->id);
        $cevap0 = $this->makeCevap0($servis->id, self::DURUM_YONLENDIRILDI);
        $this->makeCevap($cevap0->id, self::SORU_TEKNISYEN, (string) $teknisyen->id);
        $this->makeCevap($cevap0->id, self::SORU_GIDIS, '2026-09-05');

        $response = $this->guncelleDurum($operator, $servis, $teknisyen->id, '2026-09-05');
        $payload = $response->getData(true);

        $this->assertTrue($payload['success'] ?? false);
        $this->assertTrue($payload['noop'] ?? false);
        $this->assertSame(1, Islemloglari::where('servis_id', $servis->id)->notDeleted()->count());
        $this->assertSame($eskiLog->id, (int) Islemloglari::where('servis_id', $servis->id)->notDeleted()->value('id'));
        $this->assertSame(1, ServisDurumCevap0::where('servis_id', $servis->id)->count());
        $this->assertSame($teknisyen->id, (int) Servis::find($servis->id)->personel_id);
        $this->assertSame(self::DURUM_YONLENDIRILDI, (int) Servis::find($servis->id)->servis_durum_id);
    }

    public function test_teknisyen_9098_yeniden_atayamaz(): void
    {
        $teknisyen = $this->makePersonel('Teknisyen', 1077);
        $diger = $this->makePersonel('Diger', 1077);
        $servis = $this->makeServis(self::DURUM_YONLENDIRILDI, $teknisyen->id, '2026-09-05');
        $this->makeLog($servis->id, self::DURUM_YONLENDIRILDI, $teknisyen->id);
        $cevap0 = $this->makeCevap0($servis->id, self::DURUM_YONLENDIRILDI);
        $this->makeCevap($cevap0->id, self::SORU_TEKNISYEN, (string) $teknisyen->id);
        $this->makeCevap($cevap0->id, self::SORU_GIDIS, '2026-09-05');

        $response = $this->guncelleDurum($teknisyen, $servis, $diger->id, '2026-09-08');

        $this->assertSame(403, $response->getStatusCode());
        $this->assertSame($teknisyen->id, (int) Servis::find($servis->id)->personel_id);
        $this->assertSame(1, Islemloglari::where('servis_id', $servis->id)->notDeleted()->count());
    }

    public function test_diger_ayni_durum_hala_reddedilir(): void
    {
        $operator = $this->makePersonel('Operator', 1073);
        $servis = $this->makeServis(self::DURUM_IPTAL, null, '2026-09-05');
        $this->makeLog($servis->id, self::DURUM_IPTAL, $operator->id);

        Auth::login($operator);
        $request = Request::create('/servisler/' . $servis->id . '/durum-guncelle-detayli', 'PUT', [
            'servis_durum_id' => self::DURUM_IPTAL,
            'dinamik_veriler' => [],
        ]);
        $response = app(ServisController::class)->updateDurumDetayli($request, $servis);
        $payload = $response->getData(true);

        $this->assertFalse($payload['success'] ?? true);
        $this->assertSame('Durum değişikliği yapılmadı.', $payload['message'] ?? '');
        $this->assertSame(1, Islemloglari::where('servis_id', $servis->id)->notDeleted()->count());
    }

    public function test_yeniden_atama_sonra_eski_9098_logu_silinince_yeni_teknisyen_kalir(): void
    {
        $operator = $this->makePersonel('Operator', 1073);
        $eskiTeknisyen = $this->makePersonel('Eski Teknisyen', 1077);
        $yeniTeknisyen = $this->makePersonel('Yeni Teknisyen', 1077);

        $servis = $this->makeServis(self::DURUM_YONLENDIRILDI, $eskiTeknisyen->id, '2026-09-02');
        $eskiLog = $this->makeLog($servis->id, self::DURUM_YONLENDIRILDI, $operator->id);
        $eskiCevap0 = $this->makeCevap0($servis->id, self::DURUM_YONLENDIRILDI);
        $this->makeCevap($eskiCevap0->id, self::SORU_TEKNISYEN, (string) $eskiTeknisyen->id);
        $this->makeCevap($eskiCevap0->id, self::SORU_GIDIS, '2026-09-02');

        $guncelle = $this->guncelleDurum($operator, $servis, $yeniTeknisyen->id, Carbon::today()->addDays(3)->format('Y-m-d'));
        $this->assertTrue($guncelle->getData(true)['success'] ?? false);

        Auth::login($operator);
        $sil = app(IslemLogController::class)->destroy($eskiLog->id);
        $this->assertTrue($sil->getData(true)['success'] ?? false);

        $fresh = Servis::find($servis->id);
        $this->assertSame(self::DURUM_YONLENDIRILDI, (int) $fresh->servis_durum_id);
        $this->assertSame($yeniTeknisyen->id, (int) $fresh->personel_id);
        $this->assertSame(1, (int) Islemloglari::find($eskiLog->id)->silindi);
        $this->assertSame(1, Islemloglari::where('servis_id', $servis->id)->notDeleted()->where('servis_durum_id', self::DURUM_YONLENDIRILDI)->count());
        $this->assertNull(ServisDurumCevap0::find($eskiCevap0->id));

        $son13234 = ServisDurumCevap::where('soru_id', self::SORU_TEKNISYEN)
            ->whereHas('durumCevap0', function ($q) use ($servis) {
                $q->where('servis_id', $servis->id)->where('servis_durum_id', self::DURUM_YONLENDIRILDI);
            })
            ->orderByDesc('id')
            ->first();
        $this->assertNotNull($son13234);
        $this->assertSame((string) $yeniTeknisyen->id, (string) $son13234->cevap);
    }

    private function guncelleDurum(Personel $user, Servis $servis, int $teknisyenId, string $tarih)
    {
        Auth::login($user);
        $request = Request::create('/servisler/' . $servis->id . '/durum-guncelle-detayli', 'PUT', [
            'servis_durum_id' => self::DURUM_YONLENDIRILDI,
            'dinamik_veriler' => [
                'dinamik_soru[' . self::SORU_TEKNISYEN . ']' => (string) $teknisyenId,
                'dinamik_soru[' . self::SORU_GIDIS . ']' => $tarih,
            ],
        ]);

        return app(ServisController::class)->updateDurumDetayli($request, $servis);
    }

    private function makePersonel(string $ad, int $pozId): Personel
    {
        $user = new Personel();
        $user->ad = $ad;
        $user->sifre = 'x';
        $user->poz_id = $pozId;
        $user->aktif = 1;
        $user->mesai_basladimi = 1;
        $user->save();

        return $user;
    }

    private function makeServis(int $durumId, ?int $personelId, ?string $tarih): Servis
    {
        $servis = new Servis();
        $servis->servis_durum_id = $durumId;
        $servis->personel_id = $personelId;
        $servis->tarih = $tarih;
        $servis->save();

        return $servis;
    }

    private function makeLog(int $servisId, int $durumId, int $personelId): Islemloglari
    {
        $log = new Islemloglari();
        $log->servis_id = $servisId;
        $log->servis_durum_id = $durumId;
        $log->islemi_yapan_personel_id = $personelId;
        $log->tarih = '2026-09-02';
        $log->saat = '10:00:00';
        $log->aciklama = 'eski yonlendirme';
        $log->silindi = 0;
        $log->save();

        return $log;
    }

    private function makeCevap0(int $servisId, int $durumId): ServisDurumCevap0
    {
        $cevap0 = new ServisDurumCevap0();
        $cevap0->servis_id = $servisId;
        $cevap0->servis_durum_id = $durumId;
        $cevap0->tarih = '2026-09-02';
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
        ]);
        DB::table('servisdurum_sorulari')->insert([
            ['id' => self::SORU_TEKNISYEN, 'servis_durum_id' => self::DURUM_YONLENDIRILDI, 'soru' => 'Teknisyen', 'cevap_format' => '[personelSor]', 'sira' => 0],
            ['id' => self::SORU_GIDIS, 'servis_durum_id' => self::DURUM_YONLENDIRILDI, 'soru' => 'Gidis Tarihi', 'cevap_format' => '[tarihSor]', 'sira' => 3],
        ]);
    }

    private function createSchema(): void
    {
        Schema::dropIfExists('servisdurum_cevaplari');
        Schema::dropIfExists('servisdurum_cevap0');
        Schema::dropIfExists('servisdurum_sorulari');
        Schema::dropIfExists('islemloglari');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('app_settings');
        Schema::dropIfExists('servisler');
        Schema::dropIfExists('servis_durum');
        Schema::dropIfExists('musteriler');
        Schema::dropIfExists('personel');

        Schema::create('personel', function (Blueprint $table) {
            $table->id();
            $table->string('ad')->nullable();
            $table->string('sifre');
            $table->unsignedBigInteger('poz_id')->nullable();
            $table->integer('aktif')->nullable();
            $table->integer('mesai_basladimi')->nullable();
            $table->string('tel1')->nullable();
            $table->timestamps();
        });

        Schema::create('musteriler', function (Blueprint $table) {
            $table->id();
            $table->string('ad')->nullable();
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
            $table->unsignedBigInteger('musteri_id')->nullable();
            $table->unsignedBigInteger('uye_firma_id')->nullable();
            $table->string('tarih')->nullable();
            $table->timestamp('teknisyen_goruldu_at')->nullable();
            $table->unsignedBigInteger('teknisyen_goruldu_personel_id')->nullable();
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
            $table->unsignedBigInteger('uye_firma_id')->nullable();
            $table->unsignedBigInteger('personel_id')->nullable();
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

        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->nullable();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('baslik')->nullable();
            $table->text('icerik')->nullable();
            $table->string('hedef_rol', 32)->nullable();
            $table->boolean('aktif')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->unsignedBigInteger('olusturan_personel_id')->nullable();
            $table->unsignedBigInteger('personel_id')->nullable();
            $table->unsignedBigInteger('servis_id')->nullable();
            $table->timestamps();
        });
    }
}

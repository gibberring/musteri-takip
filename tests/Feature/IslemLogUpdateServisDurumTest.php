<?php

namespace Tests\Feature;

use App\Http\Controllers\IslemLogController;
use App\Models\Islemloglari;
use App\Models\Personel;
use App\Models\Servis;
use App\Models\ServisDurumCevap;
use App\Models\ServisDurumCevap0;
use Carbon\Carbon;
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

    public function test_eski_9098_logu_silinince_yeni_13234_ve_personel_kalir(): void
    {
        $user = $this->makeOperator();
        $servis = $this->makeServis(self::DURUM_YONLENDIRILDI);
        $servis->personel_id = 4002;
        $servis->save();

        $eskiLog = $this->makeLog($servis->id, self::DURUM_YONLENDIRILDI, $user->id);
        $eskiCevap0 = $this->makeCevap0($servis->id, self::DURUM_YONLENDIRILDI);
        $eski13234 = $this->makeCevap($eskiCevap0->id, self::SORU_TEKNISYEN, '4001');
        $this->makeCevap($eskiCevap0->id, self::SORU_GIDIS, '2026-09-02');

        $yeniLog = $this->makeLog($servis->id, self::DURUM_YONLENDIRILDI, $user->id);
        $yeniCevap0 = $this->makeCevap0($servis->id, self::DURUM_YONLENDIRILDI);
        $yeni13234 = $this->makeCevap($yeniCevap0->id, self::SORU_TEKNISYEN, '4002');
        $this->makeCevap($yeniCevap0->id, self::SORU_GIDIS, '2026-09-08');

        Auth::login($user);
        $response = app(IslemLogController::class)->destroy($eskiLog->id);

        $this->assertTrue($response->getData(true)['success'] ?? false);
        $this->assertSame(1, (int) Islemloglari::find($eskiLog->id)->silindi);
        $this->assertSame(0, (int) Islemloglari::find($yeniLog->id)->silindi);
        $this->assertSame(self::DURUM_YONLENDIRILDI, (int) Servis::find($servis->id)->servis_durum_id);
        $this->assertSame(4002, (int) Servis::find($servis->id)->personel_id);
        $this->assertNull(ServisDurumCevap0::find($eskiCevap0->id));
        $this->assertNull(ServisDurumCevap::find($eski13234->id));
        $this->assertNotNull(ServisDurumCevap0::find($yeniCevap0->id));
        $this->assertSame('4002', (string) ServisDurumCevap::find($yeni13234->id)->cevap);
    }

    public function test_eski_9098_logu_silinince_tek_kalan_yeni_cevap_silinmez(): void
    {
        $user = $this->makeOperator();
        $servis = $this->makeServis(self::DURUM_YONLENDIRILDI);
        $servis->personel_id = 4002;
        $servis->save();

        $eskiLog = $this->makeLog($servis->id, self::DURUM_YONLENDIRILDI, $user->id);
        $yeniLog = $this->makeLog($servis->id, self::DURUM_YONLENDIRILDI, $user->id);
        $yeniCevap0 = $this->makeCevap0($servis->id, self::DURUM_YONLENDIRILDI);
        $yeni13234 = $this->makeCevap($yeniCevap0->id, self::SORU_TEKNISYEN, '4002');

        Auth::login($user);
        $response = app(IslemLogController::class)->destroy($eskiLog->id);

        $this->assertTrue($response->getData(true)['success'] ?? false);
        $this->assertSame(1, (int) Islemloglari::find($eskiLog->id)->silindi);
        $this->assertSame(0, (int) Islemloglari::find($yeniLog->id)->silindi);
        $this->assertSame(self::DURUM_YONLENDIRILDI, (int) Servis::find($servis->id)->servis_durum_id);
        $this->assertSame(4002, (int) Servis::find($servis->id)->personel_id);
        $this->assertNotNull(ServisDurumCevap0::find($yeniCevap0->id));
        $this->assertSame('4002', (string) ServisDurumCevap::find($yeni13234->id)->cevap);
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

    public function test_show_9098_teknisyen_ve_gidis_dondurur(): void
    {
        $user = $this->makeOperator();
        $eski = $this->makeTeknisyen('Izmir Teknisyen Umit');
        $servis = $this->makeServis(self::DURUM_YONLENDIRILDI);
        $servis->personel_id = $eski->id;
        $servis->tarih = '2026-09-05';
        $servis->save();
        $log = $this->makeLog($servis->id, self::DURUM_YONLENDIRILDI, $user->id);
        $cevap0 = $this->makeCevap0($servis->id, self::DURUM_YONLENDIRILDI);
        $this->makeCevap($cevap0->id, self::SORU_TEKNISYEN, (string) $eski->id);
        $this->makeCevap($cevap0->id, self::SORU_GIDIS, '2026-09-05');

        Auth::login($user);
        $data = app(IslemLogController::class)->show($log->id)->getData(true);

        $this->assertSame($log->id, (int) $data['id']);
        $this->assertSame($eski->id, (int) $data['teknisyen_id']);
        $this->assertSame('2026-09-05', $data['gidis_tarihi']);
        $teknisyenIds = collect($data['teknisyenler'] ?? [])->pluck('id')->map(fn ($id) => (int) $id)->all();
        $this->assertContains($eski->id, $teknisyenIds);
    }

    public function test_9098_log_duzenlenince_13234_personel_ve_aciklama_guncellenir(): void
    {
        $user = $this->makeOperator();
        $eski = $this->makeTeknisyen('Izmir Teknisyen Umit');
        $yeni = $this->makeTeknisyen('Yeni Teknisyen Ali');
        $servis = $this->makeServis(self::DURUM_YONLENDIRILDI);
        $servis->personel_id = $eski->id;
        $servis->tarih = '2026-09-05';
        $servis->save();
        $sonLog = $this->makeLog($servis->id, self::DURUM_YONLENDIRILDI, $user->id);
        $cevap0 = $this->makeCevap0($servis->id, self::DURUM_YONLENDIRILDI);
        $cevap13234 = $this->makeCevap($cevap0->id, self::SORU_TEKNISYEN, (string) $eski->id);
        $cevap13235 = $this->makeCevap($cevap0->id, self::SORU_GIDIS, '2026-09-05');

        $yeniGidisTarihi = Carbon::today()->addDays(3)->format('Y-m-d');
        $response = $this->updateLog($user, $sonLog->id, self::DURUM_YONLENDIRILDI, [
            'teknisyen_id' => $yeni->id,
            'gidis_tarihi' => $yeniGidisTarihi,
            'aciklama' => '- Teknisyen: Izmir Teknisyen Umit<br>- Gidiş Tarihi: 2026-09-05',
        ]);

        $this->assertTrue($response->getData(true)['success'] ?? false, $response->getData(true)['message'] ?? '');
        $this->assertSame(self::DURUM_YONLENDIRILDI, (int) Servis::find($servis->id)->servis_durum_id);
        $this->assertSame($yeni->id, (int) Servis::find($servis->id)->personel_id);
        $this->assertSame($yeniGidisTarihi, (string) Servis::find($servis->id)->tarih);
        $this->assertSame((string) $yeni->id, (string) ServisDurumCevap::find($cevap13234->id)->cevap);
        $this->assertSame($yeniGidisTarihi, (string) ServisDurumCevap::find($cevap13235->id)->cevap);
        $this->assertSame(
            '- Teknisyen: Yeni Teknisyen Ali<br>- Gidis Tarihi: ' . $yeniGidisTarihi,
            (string) Islemloglari::find($sonLog->id)->aciklama
        );
    }

    public function test_ara_9098_log_duzenlenince_servis_personel_degismez(): void
    {
        $user = $this->makeOperator();
        $eski = $this->makeTeknisyen('Eski Teknisyen');
        $guncel = $this->makeTeknisyen('Guncel Teknisyen');
        $araTeknisyen = $this->makeTeknisyen('Ara Teknisyen');
        $servis = $this->makeServis(self::DURUM_YONLENDIRILDI);
        $servis->personel_id = $guncel->id;
        $servis->tarih = '2026-09-08';
        $servis->save();

        $araLog = $this->makeLog($servis->id, self::DURUM_YONLENDIRILDI, $user->id);
        $araCevap0 = $this->makeCevap0($servis->id, self::DURUM_YONLENDIRILDI);
        $ara13234 = $this->makeCevap($araCevap0->id, self::SORU_TEKNISYEN, (string) $eski->id);
        $this->makeCevap($araCevap0->id, self::SORU_GIDIS, '2026-09-02');

        $this->makeLog($servis->id, self::DURUM_YONLENDIRILDI, $user->id);
        $sonCevap0 = $this->makeCevap0($servis->id, self::DURUM_YONLENDIRILDI);
        $this->makeCevap($sonCevap0->id, self::SORU_TEKNISYEN, (string) $guncel->id);
        $this->makeCevap($sonCevap0->id, self::SORU_GIDIS, '2026-09-08');

        $response = $this->updateLog($user, $araLog->id, self::DURUM_YONLENDIRILDI, [
            'teknisyen_id' => $araTeknisyen->id,
            'gidis_tarihi' => Carbon::today()->addDay()->format('Y-m-d'),
        ]);

        $this->assertTrue($response->getData(true)['success'] ?? false);
        $this->assertSame($guncel->id, (int) Servis::find($servis->id)->personel_id);
        $this->assertSame('2026-09-08', (string) Servis::find($servis->id)->tarih);
        $this->assertSame(self::DURUM_YONLENDIRILDI, (int) Servis::find($servis->id)->servis_durum_id);
        $this->assertSame((string) $araTeknisyen->id, (string) ServisDurumCevap::find($ara13234->id)->cevap);
        $this->assertStringContainsString('Ara Teknisyen', (string) Islemloglari::find($araLog->id)->aciklama);
    }

    public function test_9098_olmayan_log_elle_aciklama_korunur(): void
    {
        $user = $this->makeOperator();
        $servis = $this->makeServis(self::DURUM_IPTAL);
        $sonLog = $this->makeLog($servis->id, self::DURUM_IPTAL, $user->id);

        $response = $this->updateLog($user, $sonLog->id, self::DURUM_IPTAL, [
            'aciklama' => "ilk satır<br>ikinci satır",
        ]);

        $this->assertTrue($response->getData(true)['success'] ?? false);
        $this->assertSame("ilk satır<br>ikinci satır", (string) Islemloglari::find($sonLog->id)->aciklama);
    }

    private function updateLog(Personel $user, int $logId, int $yeniDurum, array $extra = [])
    {
        Auth::login($user);
        $request = Request::create('/islemlog/' . $logId, 'PUT', array_merge([
            'servis_durum_id' => $yeniDurum,
            'tarih' => '2026-08-17',
            'saat' => '10:00',
            'aciklama' => 'duzenlendi',
        ], $extra));

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

    private function makeTeknisyen(string $ad): Personel
    {
        $user = new Personel();
        $user->ad = $ad;
        $user->sifre = 'x';
        $user->poz_id = 1077;
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
            $table->string('tarih')->nullable();
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

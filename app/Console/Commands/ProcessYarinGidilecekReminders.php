<?php

namespace App\Console\Commands;

use App\Models\Announcement;
use App\Models\Islemloglari;
use App\Models\Personel;
use App\Models\Servis;
use App\Models\ServisDurumCevap;
use App\Models\ServisDurumCevap0;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessYarinGidilecekReminders extends Command
{
    protected $signature = 'servis:yarin-gidilecek';
    protected $description = 'Yarin Gidilecek servisler icin hatirlatma ve yeniden yonlendirme islemi yapar.';

    public function handle(): int
    {
        $now = Carbon::now();
        $durumYarinGidilecek = 9477;
        $durumTeknisyenYonlendirildi = 9098;
        $teknisyenSoruId = 13234;

        $announcements = Announcement::where('hedef_rol', 'TEKNISYEN')
            ->whereNotNull('servis_id')
            ->where('aktif', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', $now)
            ->whereNull('processed_at')
            ->get();

        foreach ($announcements as $ann) {
            DB::transaction(function () use (
                $ann,
                $now,
                $durumYarinGidilecek,
                $durumTeknisyenYonlendirildi,
                $teknisyenSoruId
            ) {
                $servis = Servis::find($ann->servis_id);
                if (!$servis) {
                    $ann->processed_at = $now;
                    $ann->save();
                    return;
                }

                // Son teknisyen yönlendirme cevabını bul
                $latestCevap = ServisDurumCevap::where('soru_id', $teknisyenSoruId)
                    ->whereHas('durumCevap0', function ($q) use ($servis, $durumTeknisyenYonlendirildi) {
                        $q->where('servis_id', $servis->id)
                          ->where('servis_durum_id', $durumTeknisyenYonlendirildi);
                    })
                    ->orderByDesc('id')
                    ->first();

                $teknisyenId = $latestCevap && $latestCevap->cevap ? (int) $latestCevap->cevap : (int) ($servis->personel_id ?? 0);
                if ($teknisyenId <= 0) {
                    $ann->processed_at = $now;
                    $ann->save();
                    return;
                }

                // Servis durumu ve teknisyen ataması
                $servis->servis_durum_id = $durumTeknisyenYonlendirildi;
                $servis->personel_id = $teknisyenId;
                $servis->save();

                $cevap0 = new ServisDurumCevap0();
                $cevap0->servis_id = $servis->id;
                $cevap0->servis_durum_id = $durumTeknisyenYonlendirildi;
                $cevap0->uye_firma_id = $servis->uye_firma_id;
                $cevap0->personel_id = null;
                $cevap0->tarih = $now->format('Y-m-d');
                $cevap0->saat = $now->format('H:i:s');
                $cevap0->save();

                $cevap = new ServisDurumCevap();
                $cevap->soru_id = $teknisyenSoruId;
                $cevap->cevap = $teknisyenId;
                $cevap->durumCevap0_id = $cevap0->id;
                $cevap->uye_firma_id = $servis->uye_firma_id;
                $cevap->save();

                // Log 2: Teknisyen yönlendirildi
                $baseDate = $servis->tarih ? Carbon::parse($servis->tarih) : $now;
                $gidisTarihi = $baseDate->copy()->addDay()->format('d.m.Y');
                $teknisyenAd = Personel::where('id', $teknisyenId)->value('ad');
                $teknisyenAd = $teknisyenAd ?: ('#' . $teknisyenId);
                Islemloglari::create([
                    'islemi_yapan_personel_id' => null,
                    'servis_id' => $servis->id,
                    'servis_durum_id' => $durumTeknisyenYonlendirildi,
                    'tarih' => $now->format('Y-m-d'),
                    'saat' => $now->format('H:i:s'),
                    'aciklama' => 'Teknisyen: ' . $teknisyenAd . '<br>Gidiş Tarihi: ' . $gidisTarihi,
                ]);

                $ann->processed_at = $now;
                $ann->save();
            });
        }

        Log::info('Yarın Gidilecek hatırlatma işlemi tamamlandı.', ['count' => $announcements->count()]);
        $this->info('OK');
        return self::SUCCESS;
    }
}

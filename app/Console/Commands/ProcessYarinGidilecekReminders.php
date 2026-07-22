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
    protected $signature = 'servis:yarin-gidilecek
                            {--fix-tarih : Sadece sistem yonlendirmelerinde servis.tarih alanini duzelt}';

    protected $description = 'Yarin Gidilecek servisler icin hatirlatma, yeniden yonlendirme ve tarih onarimi yapar.';

    public function handle(): int
    {
        $now = Carbon::now();
        $processed = 0;

        if (!$this->option('fix-tarih')) {
            $processed = $this->processAnnouncements($now);
            Log::info('Yarın Gidilecek hatırlatma işlemi tamamlandı.', ['count' => $processed]);
            $this->info("Yonlendirilen: {$processed}");
        }

        $fixed = $this->fixAutoRedirectDates($now);
        Log::info('Yarın Gidilecek tarih onarımı tamamlandı.', ['fixed' => $fixed]);
        $this->info("Tarih duzeltilen: {$fixed}");
        $this->info('OK');

        return self::SUCCESS;
    }

    private function processAnnouncements(Carbon $now): int
    {
        $durumTeknisyenYonlendirildi = 9098;
        $teknisyenSoruId = 13234;
        $count = 0;

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
                $durumTeknisyenYonlendirildi,
                $teknisyenSoruId,
                &$count
            ) {
                $servis = Servis::find($ann->servis_id);
                if (!$servis) {
                    $ann->processed_at = $now;
                    $ann->save();
                    return;
                }

                $latestCevap = ServisDurumCevap::where('soru_id', $teknisyenSoruId)
                    ->whereHas('durumCevap0', function ($q) use ($servis, $durumTeknisyenYonlendirildi) {
                        $q->where('servis_id', $servis->id)
                          ->where('servis_durum_id', $durumTeknisyenYonlendirildi);
                    })
                    ->orderByDesc('id')
                    ->first();

                $teknisyenId = $latestCevap && $latestCevap->cevap
                    ? (int) $latestCevap->cevap
                    : (int) ($servis->personel_id ?? 0);

                if ($teknisyenId <= 0) {
                    $ann->processed_at = $now;
                    $ann->save();
                    return;
                }

                // Gidiş günü = cron'un çalıştığı gün
                $servis->servis_durum_id = $durumTeknisyenYonlendirildi;
                $servis->personel_id = $teknisyenId;
                $servis->tarih = $now->format('Y-m-d');
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

                $gidisTarihi = $now->format('d.m.Y');
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
                $count++;
            });
        }

        return $count;
    }

    /**
     * Sistem otomatik yönlendirmesinde servis.tarih güncellenmemiş kayıtları onarır.
     */
    private function fixAutoRedirectDates(Carbon $now): int
    {
        $from = $now->copy()->subDays(3)->format('Y-m-d');
        $logs = Islemloglari::query()
            ->where('servis_durum_id', 9098)
            ->whereNull('islemi_yapan_personel_id')
            ->whereDate('tarih', '>=', $from)
            ->orderByDesc('id')
            ->get(['servis_id', 'tarih']);

        $fixed = 0;
        $seen = [];

        foreach ($logs as $log) {
            $servisId = (int) $log->servis_id;
            if ($servisId <= 0 || isset($seen[$servisId])) {
                continue;
            }
            $seen[$servisId] = true;

            $targetDate = Carbon::parse($log->tarih)->format('Y-m-d');
            $updated = Servis::where('id', $servisId)
                ->where('servis_durum_id', 9098)
                ->where(function ($q) use ($targetDate) {
                    $q->whereNull('tarih')
                        ->orWhereDate('tarih', '!=', $targetDate);
                })
                ->update(['tarih' => $targetDate]);

            $fixed += (int) $updated;
        }

        return $fixed;
    }
}

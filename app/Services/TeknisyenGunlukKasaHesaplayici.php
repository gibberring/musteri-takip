<?php

namespace App\Services;

use App\Models\Kasa;
use App\Models\Servis;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Teknisyenin belirli bir günkü ciro / teknisyen payı / firma payı hesabı.
 * Kasa > Teknisyen Günlük Özet sayfasıyla (KasaController@technicianDailySummary) aynı formülü kullanır.
 */
class TeknisyenGunlukKasaHesaplayici
{
    // Müşteriye Ulaşılamadı, Yarın Gidilecek, Müşteri İptal Etti, Fiyatta Anlaşılamadı, Haber Verecek
    private const ADETLI_DISI_DURUMLAR = [9102, 9477, 9104, 9106, 9110];

    /**
     * @param  Collection  $teknisyenler  id, calisma_sekli_type, calisma_sekli_adet_tutar alanlı Personel kayıtları
     * @return array<int, array{gelir: float, gider: float, adetli: bool, teknisyen_payi: float, firma_payi: float}>  personel id => özet
     */
    public function hesapla(Collection $teknisyenler, string $gun): array
    {
        if ($teknisyenler->isEmpty()) {
            return [];
        }

        $ids = $teknisyenler->pluck('id')->map(fn ($id) => (int) $id)->all();

        $kasaToplamlari = Kasa::query()
            ->forTahsilEdenIn($ids)
            ->whereDate('islem_tarihi', $gun)
            ->where('gerceklesme', 1)
            ->where(function ($q) {
                $q->where('silindi', '!=', 1)->orWhereNull('silindi');
            })
            ->whereIn('odeme_yonu', [1, -1])
            ->groupBy('ilgili_personel_id', 'odeme_yonu')
            ->selectRaw('ilgili_personel_id, odeme_yonu, SUM(tutar) as toplam')
            ->get();

        $adetliIds = $teknisyenler
            ->filter(fn ($t) => $this->adetliMi($t->calisma_sekli_type ?? null))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $adetler = [];
        if (!empty($adetliIds)) {
            $adetler = Servis::query()
                ->whereIn('personel_id', $adetliIds)
                ->whereDate('tarih', $gun)
                ->whereNotIn('servis_durum_id', self::ADETLI_DISI_DURUMLAR)
                ->where(function ($q) {
                    $q->where('silindi', '!=', 1)->orWhereNull('silindi');
                })
                ->groupBy('personel_id')
                ->selectRaw('personel_id, COUNT(*) as adet')
                ->pluck('adet', 'personel_id')
                ->all();
        }

        $sonuc = [];
        foreach ($teknisyenler as $teknisyen) {
            $id = (int) $teknisyen->id;
            $gelir = (float) $kasaToplamlari->where('ilgili_personel_id', $id)->where('odeme_yonu', 1)->sum('toplam');
            $gider = (float) $kasaToplamlari->where('ilgili_personel_id', $id)->where('odeme_yonu', -1)->sum('toplam');
            $net = $gelir - $gider;

            $calismaSekliType = $teknisyen->calisma_sekli_type ?? null;
            $adetli = $this->adetliMi($calismaSekliType);
            $teknisyenPayi = 0.0;
            $firmaPayi = 0.0;

            if ($adetli) {
                $adet = (int) ($adetler[$id] ?? 0);
                $firmaPayi = ($adet * (float) ($teknisyen->calisma_sekli_adet_tutar ?? 0)) - $gider;
            } elseif ($calismaSekliType) {
                preg_match_all('/\d{1,3}/', (string) $calismaSekliType, $matches);
                $sayilar = $matches[0] ?? [];
                if (count($sayilar) >= 2) {
                    $teknisyenYuzde = (int) $sayilar[0];
                    $firmaYuzde = (int) $sayilar[1];
                    $toplamYuzde = $teknisyenYuzde + $firmaYuzde;
                    if ($toplamYuzde > 0 && $toplamYuzde <= 100) {
                        $teknisyenPayi = ($net * $teknisyenYuzde) / 100;
                        $firmaPayi = ($net * $firmaYuzde) / 100;
                    }
                }
            } else {
                $firmaPayi = $net;
            }

            $sonuc[$id] = [
                'gelir' => $gelir,
                'gider' => $gider,
                'adetli' => $adetli,
                'teknisyen_payi' => (float) $teknisyenPayi,
                'firma_payi' => (float) $firmaPayi,
            ];
        }

        return $sonuc;
    }

    private function adetliMi($calismaSekliType): bool
    {
        return Str::lower(trim((string) $calismaSekliType)) === 'adetli';
    }
}

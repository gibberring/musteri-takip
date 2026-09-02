<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Servis;
use App\Models\Kasa;
use App\Models\KasaOdemeTuru;
use App\Models\ServisDurum;
use App\Models\TnmMarka;
use App\Models\TnmCihazTuru;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\ServisDurumSoru;
use App\Models\Personel;
use App\Models\TnmPersonelPozisyon;
use Illuminate\Support\Str;

class PanelController extends Controller
{
    public function index(Request $request)
    {
        $loggedInUser = Auth::user();
        $operatorPozisyonId = 1073; // Operatör pozisyon ID'si
        $tsrnTeknisyenPozisyonId = 1077; // TŞRN Teknisyen pozisyon ID'si
        $idariIslerPozisyonId = 1076; // İdari İşler pozisyon ID'si

        // Operatör, TŞRN Teknisyen ve İdari İşler için /panel erişimini servisler sayfasına yönlendir
        if ($loggedInUser && in_array($loggedInUser->poz_id, [$operatorPozisyonId, $tsrnTeknisyenPozisyonId, $idariIslerPozisyonId])) {
            return redirect()->route('servisler.index')->with('info', 'Ana sayfanız servis kayıtlarıdır.');
        }

        // Bugünün başlangıcı ve sonu
        $bugunBaslangic = Carbon::today();
        $bugunSon = Carbon::today()->endOfDay();

        // Bugün tamamlanan servis durumları getGunlukServisKartOzetleri içinde ID ile eşleştirilir

        $workshopStatus = 'Atölyeye alındı';

        // Dünün başlangıcı ve sonu
        $dunBaslangic = Carbon::yesterday();
        $dunSon = Carbon::yesterday()->endOfDay();

        // Önceki günün başlangıcı ve sonu
        $oncekiGunBaslangic = Carbon::yesterday()->subDay();
        $oncekiGunSon = Carbon::yesterday()->subDay()->endOfDay();

        $teknisyenYonlendirildiStatusId = 9098;

        $gunlukKartOzet = $this->getGunlukServisKartOzetleri($loggedInUser, $tsrnTeknisyenPozisyonId, $teknisyenYonlendirildiStatusId);
        $bugunServisSayisi = $gunlukKartOzet['bugun']['total'];
        $bugunMusteriIptalSayisi = $gunlukKartOzet['bugun']['iptal'];
        $bugunMusteriIptalOrani = $gunlukKartOzet['bugun']['iptalOrani'];
        $bugunAtolyeSayisiKart = $gunlukKartOzet['bugun']['atolye'];
        $bugunAtolyeOraniKart = $gunlukKartOzet['bugun']['atolyeOrani'];
        $dunServisSayisi = $gunlukKartOzet['dun']['total'];
        $dunMusteriIptalSayisi = $gunlukKartOzet['dun']['iptal'];
        $dunMusteriIptalOrani = $gunlukKartOzet['dun']['iptalOrani'];
        $dunAtolyeSayisiKart = $gunlukKartOzet['dun']['atolye'];
        $dunAtolyeOraniKart = $gunlukKartOzet['dun']['atolyeOrani'];
        $oncekiGunServisSayisi = $gunlukKartOzet['oncekiGun']['total'];
        $oncekiGunMusteriIptalSayisi = $gunlukKartOzet['oncekiGun']['iptal'];
        $oncekiGunMusteriIptalOrani = $gunlukKartOzet['oncekiGun']['iptalOrani'];
        $oncekiGunAtolyeSayisiKart = $gunlukKartOzet['oncekiGun']['atolye'];
        $oncekiGunAtolyeOraniKart = $gunlukKartOzet['oncekiGun']['atolyeOrani'];
        $bugunTamamlananServisSayisi = $gunlukKartOzet['bugun']['tamamlanan'];
        $tamamlananOrani = $gunlukKartOzet['bugun']['tamamlananOrani'];

        // Dün iptal edilen servis sayısı
        $dunIptalServisSayisi = Servis::whereBetween('updated_at', [$dunBaslangic, $dunSon])
            ->where('silindi', 1);

        if ($loggedInUser && $loggedInUser->poz_id == $tsrnTeknisyenPozisyonId) {
            $dunIptalServisSayisi->where('personel_id', $loggedInUser->id); // Bu kısım belirsiz, iptal edilenler de teknisyene atanabilir mi?
        }
        $dunIptalServisSayisi = $dunIptalServisSayisi->count();

        // Önceki gün kaydedilen servis sayıları getGunlukServisKartOzetleri ile alındı

        // Önceki gün iptal edilen servis sayısı
        $oncekiGunIptalServisSayisi = Servis::whereBetween('updated_at', [$oncekiGunBaslangic, $oncekiGunSon])
            ->where('silindi', 1);

        if ($loggedInUser && $loggedInUser->poz_id == $tsrnTeknisyenPozisyonId) {
            $oncekiGunIptalServisSayisi->where('personel_id', $loggedInUser->id);
        }
        $oncekiGunIptalServisSayisi = $oncekiGunIptalServisSayisi->count();

        // Toplam servis sayısı (iptal edilenler dahil)
        // Bu metrik genel olduğu için filtresiz kalmalı
        $toplamServisSayisi = Servis::where(function($query) {
            $query->where('silindi', '!=', 1)
                ->orWhereNull('silindi');
        })->count();

        // İptal edilen servis sayısı
        // Bu metrik genel olduğu için filtresiz kalmalı
        $iptalServisSayisi = Servis::where('silindi', 1)->count();

        // İptal oranı hesaplama (Bu oran zaten yukarıdaki sayılara göre hesaplandığı için değişmeyecek)
        $iptalOrani = $toplamServisSayisi > 0 ? 
            round(($iptalServisSayisi / ($toplamServisSayisi + $iptalServisSayisi)) * 100, 2) : 0;

        // Bugünkü servisleri getir
        $bugunServislerQuery = Servis::where(function($query) {
                $query->whereDate('created_at', Carbon::today())
                    ->orWhereDate('updated_at', Carbon::today());
            })
            ->where(function ($query) {
                $query->where('silindi', '!=', 1)
                    ->orWhereNull('silindi');
            });

        if ($loggedInUser && $loggedInUser->poz_id == $tsrnTeknisyenPozisyonId) {
            $bugunServislerQuery->where('personel_id', $loggedInUser->id)
                                ->where('servis_durum_id', $teknisyenYonlendirildiStatusId); // Sadece teknisyene atanan ve yönlendirilenler
        }

        $bugunServisler = $bugunServislerQuery->get();

        $chartData = $this->getServisChartDataLast7Days($loggedInUser, $tsrnTeknisyenPozisyonId);

        // Bugün iptal edilen servis sayısı (bugün oluşturulan + Müşteri iptal etti)
        $bugunIptalEdilenQuery = Servis::whereDate('created_at', Carbon::today())
            ->whereHas('servisDurum', function($query) {
                $query->where('ad', 'Müşteri iptal etti');
            })
            ->where(function ($query) {
                $query->where('silindi', '!=', 1)
                    ->orWhereNull('silindi');
            });
        if ($loggedInUser && $loggedInUser->poz_id == $tsrnTeknisyenPozisyonId) {
            $bugunIptalEdilenQuery->where('personel_id', $loggedInUser->id);
        }
        $bugunIptalEdilenSayisi = $bugunIptalEdilenQuery->count();

        // Bugün atölyeye alınan servis sayısı
        $bugunAtolyeQuery = Servis::whereDate('updated_at', Carbon::today())
            ->whereHas('servisDurum', function($query) use ($workshopStatus) {
                $query->where('ad', $workshopStatus);
            })
            ->where(function ($query) {
                $query->where('silindi', '!=', 1)
                    ->orWhereNull('silindi');
            });
        if ($loggedInUser && $loggedInUser->poz_id == $tsrnTeknisyenPozisyonId) {
            $bugunAtolyeQuery->where('personel_id', $loggedInUser->id);
        }
        $bugunAtolyeSayisi = $bugunAtolyeQuery->count();

        $miniCardData = $this->getMiniCardData($loggedInUser, $tsrnTeknisyenPozisyonId);

        // ---- Bugünkü Kasa Özeti Hesaplamaları ----
        $today = Carbon::today();

        // Gelir (yon = 1)
        $todayIncomeQuery = Kasa::whereDate('kasa.created_at', $today)
            ->join('kasa_odeme_turu', 'kasa.odeme_turu_id', '=', 'kasa_odeme_turu.id')
            ->where('kasa_odeme_turu.yon', 1);
        if ($loggedInUser && $loggedInUser->poz_id == $tsrnTeknisyenPozisyonId) {
            $todayIncomeQuery->where('kasa.personel_id', $loggedInUser->id);
        }
        $todayIncome = $todayIncomeQuery->sum('kasa.tutar');

        // Gider (yon = -1)
        $todayExpenseQuery = Kasa::whereDate('kasa.created_at', $today)
            ->join('kasa_odeme_turu', 'kasa.odeme_turu_id', '=', 'kasa_odeme_turu.id')
            ->where('kasa_odeme_turu.yon', -1);
        if ($loggedInUser && $loggedInUser->poz_id == $tsrnTeknisyenPozisyonId) {
            $todayExpenseQuery->where('kasa.personel_id', $loggedInUser->id);
        }
        $todayExpense = $todayExpenseQuery->sum('kasa.tutar');

        $todayNetCash = ($todayIncome ?? 0) - ($todayExpense ?? 0);

        // Reklam Ödemesi (kasa_odeme_turu.ad = 'Reklam Ödemesi' ve yon = -1)
        $todayAdPaymentsQuery = Kasa::whereDate('kasa.created_at', $today)
            ->join('kasa_odeme_turu', 'kasa.odeme_turu_id', '=', 'kasa_odeme_turu.id')
            ->where('kasa_odeme_turu.ad', 'Reklam Ödemesi') // Ödeme türü adına göre
            ->where('kasa_odeme_turu.yon', -1); // Gider olmalı
        if ($loggedInUser && $loggedInUser->poz_id == $tsrnTeknisyenPozisyonId) {
            $todayAdPaymentsQuery->where('kasa.personel_id', $loggedInUser->id);
        }
        $todayAdPayments = $todayAdPaymentsQuery->sum('kasa.tutar');

        // Ofis Gideri (kasa_odeme_turu.ad = 'Ofis Gideri' ve yon = -1)
        $todayOfficeExpensesQuery = Kasa::whereDate('kasa.created_at', $today)
            ->join('kasa_odeme_turu', 'kasa.odeme_turu_id', '=', 'kasa_odeme_turu.id')
            ->where('kasa_odeme_turu.ad', 'Ofis Gideri') // Ödeme türü adına göre
            ->where('kasa_odeme_turu.yon', -1); // Gider olmalı
        if ($loggedInUser && $loggedInUser->poz_id == $tsrnTeknisyenPozisyonId) {
            $todayOfficeExpensesQuery->where('kasa.personel_id', $loggedInUser->id);
        }
        $todayOfficeExpenses = $todayOfficeExpensesQuery->sum('kasa.tutar');

        // Son 7 Gün Net Kasa (Grafik için)
        $netCashLast7Days = [];
        $chartDates = []; 
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $incomeQuery = Kasa::whereDate('kasa.created_at', $date)
                       ->join('kasa_odeme_turu', 'kasa.odeme_turu_id', '=', 'kasa_odeme_turu.id')
                       ->where('kasa_odeme_turu.yon', 1);
            if ($loggedInUser && $loggedInUser->poz_id == $tsrnTeknisyenPozisyonId) {
                $incomeQuery->where('kasa.personel_id', $loggedInUser->id);
            }
            $income = $incomeQuery->sum('kasa.tutar');

            $expenseQuery = Kasa::whereDate('kasa.created_at', $date)
                        ->join('kasa_odeme_turu', 'kasa.odeme_turu_id', '=', 'kasa_odeme_turu.id')
                        ->where('kasa_odeme_turu.yon', -1);
            if ($loggedInUser && $loggedInUser->poz_id == $tsrnTeknisyenPozisyonId) {
                $expenseQuery->where('kasa.personel_id', $loggedInUser->id);
            }
            $expense = $expenseQuery->sum('kasa.tutar');

            $netCashLast7Days[] = ($income ?? 0) - ($expense ?? 0);
            $chartDates[] = $date->locale('tr')->translatedFormat('d M');
        }

        $kasaSummary = [
            'todayIncome' => $todayIncome ?? 0,
            'todayExpense' => $todayExpense ?? 0,
            'todayNetCash' => $todayNetCash,
            'todayAdPayments' => $todayAdPayments ?? 0,
            'todayOfficeExpenses' => $todayOfficeExpenses ?? 0,
            'netCashLast7Days' => $netCashLast7Days,
            'chartDates' => $chartDates
        ];
        // ---- Kasa Özeti Hesaplamaları Bitiş ----
        $panelKasaDate = Carbon::today();
        $genelKasaTotals = $this->getGenelKasaTotalsForDate($panelKasaDate);
        $panelNetKalan = $this->getTechnicianDailyNetKalan($panelKasaDate->format('Y-m-d'), $loggedInUser);
        $panelNetCashLast7Days = $this->getGenelKasaNetCashLast7Days($panelKasaDate);
        $panelExpenseBreakdown = $this->getGenelKasaExpenseBreakdownForDate($panelKasaDate);

        $ilKazancTarih1 = $this->parseDateInput($request->input('il_kazanc_tarih1'));
        $ilKazancTarih2 = $this->parseDateInput($request->input('il_kazanc_tarih2'));
        if (!$ilKazancTarih1 && !$ilKazancTarih2) {
            $ilKazancTarih1 = Carbon::today();
            $ilKazancTarih2 = Carbon::today();
        } elseif ($ilKazancTarih1 && !$ilKazancTarih2) {
            $ilKazancTarih2 = $ilKazancTarih1->copy();
        } elseif (!$ilKazancTarih1 && $ilKazancTarih2) {
            $ilKazancTarih1 = $ilKazancTarih2->copy();
        }
        if ($ilKazancTarih1 && $ilKazancTarih2 && $ilKazancTarih2->lt($ilKazancTarih1)) {
            [$ilKazancTarih1, $ilKazancTarih2] = [$ilKazancTarih2, $ilKazancTarih1];
        }
        $ilBazliKazanc = $this->getIlBazliKazancOzeti($ilKazancTarih1, $ilKazancTarih2);
        $kasaSummary['todayIncome'] = $genelKasaTotals['totalGelir'];
        $kasaSummary['todayExpense'] = $genelKasaTotals['totalGider'];
        $kasaSummary['todayNetCash'] = $panelNetKalan;
        $kasaSummary['netCashLast7Days'] = $panelNetCashLast7Days['series'];
        $kasaSummary['chartDates'] = $panelNetCashLast7Days['dates'];
        $kasaSummary['todayAdPayments'] = $panelExpenseBreakdown['adPayments'];
        $kasaSummary['todayOfficeExpenses'] = $panelExpenseBreakdown['officeExpenses'];
        $kasaSummary['todaySalaryPayments'] = $panelExpenseBreakdown['salaryPayments'];
        $kasaSummary['todayUgurHarcamasi'] = $panelExpenseBreakdown['ugurHarcamasi'];
        $kasaSummary['todayTechnicianPayments'] = $panelExpenseBreakdown['technicianPayments'];
        $kasaSummary['todayServiceExpenses'] = $panelExpenseBreakdown['serviceExpenses'];

        $ithalMarkaAdlari = [
            'Amana', 'Electrolux', 'Fagor', 'Franke', 'Frigidaire', 'Gaggenau',
            'General Electric', 'Gorenje', 'Liebherr', 'Maytag', 'Miele', 'Sharp',
            'Silverline', 'Smeg', 'Subzero', 'Teka', 'Westinghouse', 'Zanussi'
        ];
        $ithalMarkaIds = TnmMarka::whereIn('ad', $ithalMarkaAdlari)->pluck('id');

        $bugunIthalMarkaServisleri = Servis::with([
            'musteri.ilce',
            'musteri.il',
            'servisDurum',
            'marka',
            'cihazTuru'
        ])
            ->whereDate('servisler.created_at', Carbon::today())
            ->when($ithalMarkaIds->isNotEmpty(), fn ($q) => $q->whereIn('marka_id', $ithalMarkaIds))
            ->when($ithalMarkaIds->isEmpty(), fn ($q) => $q->whereRaw('1 = 0'))
            ->where(function ($query) {
                $query->where('servisler.silindi', '!=', 1)
                    ->orWhereNull('servisler.silindi');
            })
            ->orderBy('servisler.created_at', 'desc')
            ->get();

        // Servis durumları ve renkleri için manuel eşleştirme
        $servisDurumStilleri = [
            'Beklemede' => 'bg-soft-secondary text-secondary',
            'Atölyeye alındı' => 'bg-soft-primary text-primary',
            'Parça Gidecek' => 'bg-soft-info text-info', // Örnek bir durum ve stili
            'Parça bekleniyor' => 'bg-soft-warning text-warning',
            'Yerinde bakım yapıldı' => 'bg-soft-info text-info',
            'Cihaz teslim edildi' => 'bg-soft-success text-success',
            'Servisi sonlandırıldı' => 'bg-soft-dark text-dark',
            'Müşteri iptal etti' => 'bg-soft-danger text-danger',
            'Ücret iadesi sürecinde' => 'bg-soft-danger text-danger',
            'Fiyatta anlaşılamadı' => 'bg-soft-danger text-danger',
            'Haber Verecek' => 'bg-soft-purple text-purple',
            'Müşteriye Ulaşılamadı' => 'bg-soft-orange text-orange',
            // Projenizdeki TÜM servis durumlarını ve karşılık gelen Bootstrap class'larını buraya ekleyin
            // Örneğin: 'Yeni Durum' => 'bg-soft-yeni-renk text-yeni-renk',
        ];
        // ---- Bugünkü İthal Marka Servisleri Bitiş ----

        // Modal için gerekli ek veriler
        $servisDurumlar = ServisDurum::gorunur()->orderBy('sira')->get();
        $servisDurumSorular = ServisDurumSoru::all();
        $personeller = Personel::where('aktif', 1)->orderBy('ad')->get();
        $markalar = TnmMarka::orderBy('ad')->get();
        $cihazTurleri = TnmCihazTuru::orderBy('ad')->get();

        // İl bazlı servis dağılımı
        $ilBazliServisDagilimi = $this->getIlBazliServisDagilimi();
        // Bugün oluşturulan servislerin marka dağılımı (il grafiği ile aynı gün / silinmemiş filtresi)
        $markaBazliServisDagilimi = $this->getMarkaBazliServisDagilimi();

        $isTsrnTeknisyen = ($loggedInUser && $loggedInUser->poz_id == $tsrnTeknisyenPozisyonId); // Yeni değişken

        // View'a gönderilecek veriler
        return view('crm.main', compact(
            'bugunServisSayisi',
            'bugunMusteriIptalSayisi',
            'bugunMusteriIptalOrani',
            'dunServisSayisi',
            'dunMusteriIptalSayisi',
            'dunMusteriIptalOrani',
            'oncekiGunServisSayisi',
            'oncekiGunMusteriIptalSayisi',
            'oncekiGunMusteriIptalOrani',
            'bugunAtolyeSayisiKart',
            'bugunAtolyeOraniKart',
            'dunAtolyeSayisiKart',
            'dunAtolyeOraniKart',
            'oncekiGunAtolyeSayisiKart',
            'oncekiGunAtolyeOraniKart',
            'dunIptalServisSayisi',
            'oncekiGunIptalServisSayisi',
            'iptalServisSayisi',
            'iptalOrani',
            'bugunTamamlananServisSayisi',
            'tamamlananOrani',
            'chartData', 
            'bugunIptalEdilenSayisi',
            'bugunAtolyeSayisi',
            'miniCardData',
            'kasaSummary',
            'bugunIthalMarkaServisleri',
            'servisDurumStilleri',
            'servisDurumlar',
            'servisDurumSorular',
            'personeller',
            'markalar',
            'cihazTurleri',
            'ilBazliServisDagilimi',
            'markaBazliServisDagilimi',
            'ilBazliKazanc',
            'ilKazancTarih1',
            'ilKazancTarih2',
            'isTsrnTeknisyen'
        ));
    }

    private function applyServisSilinmediFiltresi($query)
    {
        return $query->where(function ($q) {
            $q->where('silindi', '!=', 1)
                ->orWhereNull('silindi');
        });
    }

    private function getGunlukServisKartOzetleri(?Personel $loggedInUser, int $tsrnTeknisyenPozisyonId, int $teknisyenYonlendirildiStatusId): array
    {
        $musteriIptalDurumId = 9104;
        $atolyeDurumId = 9100;
        $tamamlananDurumIds = [9100, 9103, 9105, 9115, 9099];

        $bugun = Carbon::today();
        $dun = Carbon::yesterday();
        $oncekiGun = Carbon::yesterday()->subDay();
        $rangeStart = $oncekiGun->copy()->startOfDay();
        $rangeEnd = $bugun->copy()->endOfDay();

        $query = Servis::query()
            ->tap(fn ($q) => $this->applyServisSilinmediFiltresi($q))
            ->whereBetween('created_at', [$rangeStart, $rangeEnd]);

        if ($loggedInUser && $loggedInUser->poz_id == $tsrnTeknisyenPozisyonId) {
            $query->where('personel_id', $loggedInUser->id);
        }

        $rows = $query
            ->selectRaw('DATE(created_at) as gun, servis_durum_id, COUNT(*) as cnt')
            ->groupBy('gun', 'servis_durum_id')
            ->get();

        $bugunGun = $bugun->format('Y-m-d');
        if ($loggedInUser && $loggedInUser->poz_id == $tsrnTeknisyenPozisyonId) {
            $bugunRows = Servis::query()
                ->tap(fn ($q) => $this->applyServisSilinmediFiltresi($q))
                ->whereDate('created_at', $bugun)
                ->where('personel_id', $loggedInUser->id)
                ->where('servis_durum_id', $teknisyenYonlendirildiStatusId)
                ->selectRaw('servis_durum_id, COUNT(*) as cnt')
                ->groupBy('servis_durum_id')
                ->get();

            $rows = $rows->reject(fn ($row) => $row->gun === $bugunGun);
            foreach ($bugunRows as $row) {
                $rows->push((object) [
                    'gun' => $bugunGun,
                    'servis_durum_id' => $row->servis_durum_id,
                    'cnt' => $row->cnt,
                ]);
            }
        }

        $buildDay = function (Carbon $date) use ($rows, $musteriIptalDurumId, $atolyeDurumId, $tamamlananDurumIds) {
            $gun = $date->format('Y-m-d');
            $dayRows = $rows->where('gun', $gun);
            $total = (int) $dayRows->sum('cnt');
            $iptalRow = $dayRows->firstWhere('servis_durum_id', $musteriIptalDurumId);
            $atolyeRow = $dayRows->firstWhere('servis_durum_id', $atolyeDurumId);
            $iptal = (int) ($iptalRow->cnt ?? 0);
            $atolye = (int) ($atolyeRow->cnt ?? 0);
            $tamamlanan = (int) $dayRows
                ->whereIn('servis_durum_id', $tamamlananDurumIds)
                ->sum('cnt');

            return [
                'total' => $total,
                'iptal' => $iptal,
                'atolye' => $atolye,
                'tamamlanan' => $tamamlanan,
                'iptalOrani' => $total > 0 ? round(($iptal / $total) * 100, 2) : 0,
                'atolyeOrani' => $total > 0 ? round(($atolye / $total) * 100, 2) : 0,
                'tamamlananOrani' => $total > 0 ? round(($tamamlanan / $total) * 100, 1) : 0,
            ];
        };

        return [
            'bugun' => $buildDay($bugun),
            'dun' => $buildDay($dun),
            'oncekiGun' => $buildDay($oncekiGun),
        ];
    }

    private function getMiniCardData(?Personel $loggedInUser, int $tsrnTeknisyenPozisyonId): array
    {
        $miniCardDurumMap = [
            'ulasilamadi' => 9102,
            'haberVerecek' => 9110,
            'musteriIptal' => 9104,
        ];

        $today = Carbon::today();
        $sameDayLastWeek = Carbon::today()->subWeek();
        $rangeStart = $sameDayLastWeek->copy()->subDays(6)->startOfDay();
        $rangeEnd = $today->copy()->endOfDay();

        $query = Servis::query()
            ->tap(fn ($q) => $this->applyServisSilinmediFiltresi($q))
            ->whereBetween('created_at', [$rangeStart, $rangeEnd])
            ->whereIn('servis_durum_id', array_values($miniCardDurumMap));

        if ($loggedInUser && $loggedInUser->poz_id == $tsrnTeknisyenPozisyonId) {
            $query->where('personel_id', $loggedInUser->id);
        }

        $rows = $query
            ->selectRaw('DATE(created_at) as gun, servis_durum_id, COUNT(*) as cnt')
            ->groupBy('gun', 'servis_durum_id')
            ->get();

        $countsByDurum = [];
        foreach ($rows as $row) {
            $countsByDurum[$row->servis_durum_id][$row->gun] = (int) $row->cnt;
        }

        $dailyDates = [];
        for ($date = $rangeStart->copy(); $date->lte($today); $date->addDay()) {
            $dailyDates[] = $date->format('Y-m-d');
        }

        $miniCardData = [];
        foreach ($miniCardDurumMap as $key => $durumId) {
            $byDay = $countsByDurum[$durumId] ?? [];
            $countToday = (int) ($byDay[$today->format('Y-m-d')] ?? 0);
            $countLastWeek = (int) ($byDay[$sameDayLastWeek->format('Y-m-d')] ?? 0);

            $dailyCounts = [];
            foreach ($dailyDates as $gun) {
                $dailyCounts[] = (int) ($byDay[$gun] ?? 0);
            }

            $diffPercentage = 0;
            $diffType = 'none';
            if ($countLastWeek == 0) {
                if ($countToday > 0) {
                    $diffPercentage = 100;
                    $diffType = 'increase';
                }
            } else {
                $percentage = (($countToday - $countLastWeek) / $countLastWeek) * 100;
                $diffPercentage = round(abs($percentage));
                if ($percentage > 0) {
                    $diffType = 'increase';
                } elseif ($percentage < 0) {
                    $diffType = 'decrease';
                }
            }

            $miniCardData[$key] = [
                'today' => $countToday,
                'diff' => $diffPercentage,
                'type' => $diffType,
                'daily' => $dailyCounts,
            ];
        }

        return $miniCardData;
    }

    // İl bazlı servis sayılarını almak için yardımcı bir metod (isteğe bağlı)
    private function getIlBazliServisDagilimi()
    {
        $rows = Servis::query()
            ->join('musteriler', 'servisler.musteri_id', '=', 'musteriler.id')
            ->leftJoin('iller', 'musteriler.il_id', '=', 'iller.id')
            ->whereDate('servisler.created_at', Carbon::today())
            ->where(function ($query) {
                $query->where('servisler.silindi', '!=', 1)
                    ->orWhereNull('servisler.silindi');
            })
            ->selectRaw("COALESCE(iller.ad, 'Bilinmeyen İl') as il_adi")
            ->selectRaw('COUNT(*) as cnt')
            ->groupBy(DB::raw("COALESCE(iller.ad, 'Bilinmeyen İl')"))
            ->orderByDesc('cnt')
            ->get();

        return [
            'labels' => $rows->pluck('il_adi')->toArray(),
            'counts' => $rows->pluck('cnt')->map(fn ($c) => (int) $c)->toArray(),
        ];
    }

    /** Bugün oluşturulan servislerin marka bazlı sayımı (İl dağılımı ile aynı tarih filtresi). */
    private function getMarkaBazliServisDagilimi(): array
    {
        $rows = Servis::query()
            ->leftJoin('tnm_markalar', 'servisler.marka_id', '=', 'tnm_markalar.id')
            ->whereDate('servisler.created_at', Carbon::today())
            ->where(function ($query) {
                $query->where('servisler.silindi', '!=', 1)
                    ->orWhereNull('servisler.silindi');
            })
            ->selectRaw("COALESCE(tnm_markalar.ad, 'Marka yok') as marka_adi")
            ->selectRaw('COUNT(*) as cnt')
            ->groupBy(DB::raw("COALESCE(tnm_markalar.ad, 'Marka yok')"))
            ->orderByDesc('cnt')
            ->get();

        return [
            'labels' => $rows->pluck('marka_adi')->toArray(),
            'counts' => $rows->pluck('cnt')->map(fn ($c) => (int) $c)->toArray(),
        ];
    }

    private function getIlBazliKazancOzeti(?Carbon $dateFrom, ?Carbon $dateTo): array
    {
        $kasaByServisQuery = Kasa::query()
            ->whereNotNull('servis_id')
            ->where(function ($q) {
                $q->where('silindi', '!=', 1)
                    ->orWhereNull('silindi');
            });

        if ($dateFrom) {
            $kasaByServisQuery->whereDate('islem_tarihi', '>=', $dateFrom->format('Y-m-d'));
        }
        if ($dateTo) {
            $kasaByServisQuery->whereDate('islem_tarihi', '<=', $dateTo->format('Y-m-d'));
        }

        $kasaByServis = $kasaByServisQuery
            ->select([
                'servis_id',
                DB::raw('SUM(CASE WHEN odeme_yonu = 1 THEN tutar ELSE 0 END) as gelir'),
                DB::raw('SUM(CASE WHEN odeme_yonu = -1 THEN tutar ELSE 0 END) as gider'),
            ])
            ->groupBy('servis_id')
            ->get();

        $servisIds = $kasaByServis->pluck('servis_id')->filter()->unique()->values()->all();
        if (empty($servisIds)) {
            return [];
        }

        $servisler = Servis::with(['musteri.il', 'personel'])
            ->whereIn('id', $servisIds)
            ->get()
            ->keyBy('id');

        $summary = [];
        foreach ($kasaByServis as $row) {
            $servis = $servisler->get($row->servis_id);
            if (!$servis) {
                continue;
            }
            $ilAdi = $servis->musteri && $servis->musteri->il ? $servis->musteri->il->ad : 'Bilinmeyen İl';
            $gelir = (float) ($row->gelir ?? 0);
            $gider = (float) ($row->gider ?? 0);
            $netTutar = $gelir - $gider;

            $teknisyenPayi = 0.0;
            $firmaPayi = 0.0;
            $calismaSekliType = $servis->personel ? ($servis->personel->calisma_sekli_type ?? null) : null;
            $calismaAdetTutar = $servis->personel ? ($servis->personel->calisma_sekli_adet_tutar ?? null) : null;
            $isAdetli = (Str::lower(trim((string) $calismaSekliType)) === 'adetli');

            if ($isAdetli) {
                $firmaPayi = ((float) $calismaAdetTutar) - $gider;
            } elseif ($calismaSekliType) {
                preg_match_all('/\d{1,3}/', (string) $calismaSekliType, $matches);
                $sayilar = $matches[0] ?? [];
                if (count($sayilar) >= 2) {
                    $teknisyenYuzde = (int) $sayilar[0];
                    $firmaYuzde = (int) $sayilar[1];
                    $toplamYuzde = $teknisyenYuzde + $firmaYuzde;
                    if ($toplamYuzde > 0 && $toplamYuzde <= 100) {
                        $teknisyenPayi = ($netTutar * $teknisyenYuzde) / 100;
                        $firmaPayi = ($netTutar * $firmaYuzde) / 100;
                    } else {
                        $firmaPayi = $netTutar;
                    }
                } else {
                    $firmaPayi = $netTutar;
                }
            } else {
                $firmaPayi = $netTutar;
            }

            if (!isset($summary[$ilAdi])) {
                $summary[$ilAdi] = [
                    'il' => $ilAdi,
                    'toplam_gelir' => 0.0,
                    'toplam_gider' => 0.0,
                    'teknisyen_payi' => 0.0,
                    'firma_kazanc' => 0.0,
                    'servis_sayisi' => 0,
                ];
            }

            $summary[$ilAdi]['toplam_gelir'] += $gelir;
            $summary[$ilAdi]['toplam_gider'] += $gider;
            $summary[$ilAdi]['teknisyen_payi'] += $teknisyenPayi;
            $summary[$ilAdi]['firma_kazanc'] += $firmaPayi;
            $summary[$ilAdi]['servis_sayisi'] += 1;
        }

        $summary = array_values($summary);
        usort($summary, function ($a, $b) {
            return $b['firma_kazanc'] <=> $a['firma_kazanc'];
        });

        return $summary;
    }

    private function parseDateInput(?string $input): ?Carbon
    {
        if (!$input) {
            return null;
        }
        $input = trim($input);
        if ($input === '') {
            return null;
        }
        try {
            if (preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $input)) {
                return Carbon::createFromFormat('d.m.Y', $input);
            }
            return Carbon::createFromFormat('Y-m-d', $input);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function ilKazancTable(Request $request)
    {
        $tarih1 = $this->parseDateInput($request->input('il_kazanc_tarih1'));
        $tarih2 = $this->parseDateInput($request->input('il_kazanc_tarih2'));
        if (!$tarih1 && !$tarih2) {
            $tarih1 = Carbon::today();
            $tarih2 = Carbon::today();
        } elseif ($tarih1 && !$tarih2) {
            $tarih2 = $tarih1->copy();
        } elseif (!$tarih1 && $tarih2) {
            $tarih1 = $tarih2->copy();
        }
        if ($tarih1 && $tarih2 && $tarih2->lt($tarih1)) {
            [$tarih1, $tarih2] = [$tarih2, $tarih1];
        }
        $ilBazliKazanc = $this->getIlBazliKazancOzeti($tarih1, $tarih2);

        return response()->json([
            'success' => true,
            'html' => view('crm.partials.il-kazanc-table', [
                'ilBazliKazanc' => $ilBazliKazanc,
            ])->render(),
        ]);
    }

    private function getGenelKasaTotalsForDate(Carbon $date): array
    {
        $totalsQueryForSums = Kasa::query()
            ->where(function ($q) {
                $q->where('silindi', '!=', 1)
                    ->orWhereNull('silindi');
            })
            ->whereDate('islem_tarihi', $date->format('Y-m-d'));

        $totalGelir = (clone $totalsQueryForSums)
            ->where('odeme_yonu', 1)
            ->sum('tutar');

        $totalGiderRaw = (clone $totalsQueryForSums)
            ->where('odeme_yonu', -1)
            ->sum('tutar');

        $dateFrom = $date->format('Y-m-d');
        $dateTo = $dateFrom;
        $teknisyenPaylariToplam = $this->calculateTeknisyenPaylariToplam($dateFrom, $dateTo);
        $bekleyenOdemeToplam = (clone $totalsQueryForSums)
            ->where('gerceklesme', 0)
            ->sum('tutar');

        $totalGider = $totalGiderRaw + $teknisyenPaylariToplam + $bekleyenOdemeToplam;
        $netToplam = $totalGelir - $totalGider;

        return [
            'totalGelir' => $totalGelir,
            'totalGider' => $totalGider,
            'netToplam' => $netToplam,
        ];
    }

    private function getGenelKasaNetCashLast7Days(Carbon $endDate): array
    {
        $startDate = $endDate->copy()->subDays(6);
        $dateFrom = $startDate->format('Y-m-d');
        $dateTo = $endDate->format('Y-m-d');

        $silindiFilter = function ($q) {
            $q->where('silindi', '!=', 1)
                ->orWhereNull('silindi');
        };

        $dailyTotals = Kasa::query()
            ->where($silindiFilter)
            ->whereBetween('islem_tarihi', [$dateFrom, $dateTo])
            ->selectRaw('DATE(islem_tarihi) as gun')
            ->selectRaw('SUM(CASE WHEN odeme_yonu = 1 THEN tutar ELSE 0 END) as gelir')
            ->selectRaw('SUM(CASE WHEN odeme_yonu = -1 THEN tutar ELSE 0 END) as gider')
            ->groupBy('gun')
            ->get()
            ->keyBy('gun');

        $dailyBekleyen = Kasa::query()
            ->where($silindiFilter)
            ->whereBetween('islem_tarihi', [$dateFrom, $dateTo])
            ->where('gerceklesme', 0)
            ->selectRaw('DATE(islem_tarihi) as gun, COALESCE(SUM(tutar), 0) as tutar')
            ->groupBy('gun')
            ->pluck('tutar', 'gun');

        $teknisyenPayByDay = $this->calculateTeknisyenPaylariByDay($dateFrom, $dateTo);

        $series = [];
        $dates = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = $endDate->copy()->subDays($i);
            $gun = $date->format('Y-m-d');
            $row = $dailyTotals->get($gun);
            $gelir = (float) ($row->gelir ?? 0);
            $gider = (float) ($row->gider ?? 0);
            $bekleyen = (float) ($dailyBekleyen[$gun] ?? 0);
            $teknisyenPay = (float) ($teknisyenPayByDay[$gun] ?? 0);
            $series[] = $gelir - ($gider + $teknisyenPay + $bekleyen);
            $dates[] = $date->locale('tr')->translatedFormat('d M');
        }

        return [
            'series' => $series,
            'dates' => $dates,
        ];
    }

    private function getGenelKasaExpenseBreakdownForDate(Carbon $date): array
    {
        $baseQuery = Kasa::query()
            ->where(function ($q) {
                $q->where('silindi', '!=', 1)
                    ->orWhereNull('silindi');
            })
            ->whereDate('islem_tarihi', $date->format('Y-m-d'))
            ->where('odeme_yonu', -1);

        $serviceExpenses = (clone $baseQuery)
            ->whereHas('odemeTuru', function ($q) {
                $q->where('ad', 'Servis İşlemleri');
            })
            ->sum('tutar');

        $officeExpenses = (clone $baseQuery)
            ->whereHas('odemeTuru', function ($q) {
                $q->where('ad', 'Ofis Gideri');
            })
            ->sum('tutar');

        $adPayments = (clone $baseQuery)
            ->whereHas('odemeTuru', function ($q) {
                $q->where('ad', 'Reklam Ödemesi');
            })
            ->sum('tutar');

        $ugurHarcamasi = (clone $baseQuery)
            ->whereHas('odemeTuru', function ($q) {
                $q->where('ad', 'Uğur Bey Harcama');
            })
            ->sum('tutar');

        $salaryPayments = (clone $baseQuery)
            ->whereHas('odemeTuru', function ($q) {
                $q->where('ad', 'Maaş Ödemesi');
            })
            ->sum('tutar');

        $dateFrom = $date->format('Y-m-d');
        $dateTo = $dateFrom;
        $technicianPayments = $this->calculateTeknisyenPaylariToplam($dateFrom, $dateTo);

        return [
            'serviceExpenses' => $serviceExpenses,
            'officeExpenses' => $officeExpenses,
            'adPayments' => $adPayments,
            'ugurHarcamasi' => $ugurHarcamasi,
            'salaryPayments' => $salaryPayments,
            'technicianPayments' => $technicianPayments,
        ];
    }

    private function calculateTeknisyenPaylariToplam(string $dateFrom, string $dateTo): float
    {
        return array_sum($this->calculateTeknisyenPaylariByDay($dateFrom, $dateTo));
    }

    /**
     * @return array<string, float> gun (Y-m-d) => teknisyen payi
     */
    private function calculateTeknisyenPaylariByDay(string $dateFrom, string $dateTo): array
    {
        $teknisyenPozisyonId = 1077;
        $adetliDisiDurumlar = [9102, 9477, 9104, 9106, 9110];

        $startDate = Carbon::parse($dateFrom)->startOfDay();
        $endDate = Carbon::parse($dateTo)->startOfDay();
        if ($endDate->lt($startDate)) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }
        $rangeFrom = $startDate->format('Y-m-d');
        $rangeTo = $endDate->format('Y-m-d');

        $teknisyenler = Personel::where('poz_id', $teknisyenPozisyonId)
            ->get(['id', 'calisma_sekli_type', 'calisma_sekli_adet_tutar']);
        $teknisyenIds = $teknisyenler->pluck('id')->all();

        $silindiFilter = function ($q) {
            $q->where('silindi', '!=', 1)
                ->orWhereNull('silindi');
        };

        $kasaRows = Kasa::query()
            ->where($silindiFilter)
            ->whereBetween('islem_tarihi', [$rangeFrom, $rangeTo])
            ->where('gerceklesme', 1)
            ->get(['ilgili_personel_id', 'islem_tarihi', 'odeme_yonu', 'tutar']);

        $kasaByDay = $kasaRows->groupBy(fn ($k) => Carbon::parse($k->islem_tarihi)->format('Y-m-d'));

        $adetCounts = collect();
        if ($teknisyenIds !== []) {
            $adetCounts = Servis::query()
                ->where($silindiFilter)
                ->whereBetween('tarih', [$rangeFrom, $rangeTo])
                ->whereNotIn('servis_durum_id', $adetliDisiDurumlar)
                ->whereIn('personel_id', $teknisyenIds)
                ->selectRaw('DATE(tarih) as gun, personel_id, COUNT(*) as cnt')
                ->groupBy('gun', 'personel_id')
                ->get()
                ->mapWithKeys(fn ($row) => [$row->gun . '|' . $row->personel_id => (int) $row->cnt]);
        }

        $payByDay = [];
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $gun = $date->format('Y-m-d');
            $payByDay[$gun] = 0.0;
            $dayKasa = $kasaByDay->get($gun, collect());

            foreach ($teknisyenler as $teknisyen) {
                $matched = $dayKasa->filter(function ($kasa) use ($teknisyen) {
                    return (int) $kasa->ilgili_personel_id === (int) $teknisyen->id;
                });

                $gelir = (float) $matched->where('odeme_yonu', 1)->sum('tutar');
                $gider = (float) $matched->where('odeme_yonu', -1)->sum('tutar');
                $netTutar = $gelir - $gider;

                $calismaSekliType = $teknisyen->calisma_sekli_type ?? null;
                $isAdetli = (Str::lower(trim((string) $calismaSekliType)) === 'adetli');
                $teknisyenPayi = 0.0;
                $adet = 0;

                if ($isAdetli) {
                    $adet = (int) ($adetCounts[$gun . '|' . $teknisyen->id] ?? 0);
                } elseif ($calismaSekliType) {
                    preg_match_all('/\d{1,3}/', (string) $calismaSekliType, $matches);
                    $sayilar = $matches[0] ?? [];
                    if (count($sayilar) >= 1) {
                        $teknisyenYuzde = (int) $sayilar[0];
                        $teknisyenPayi = ($netTutar * $teknisyenYuzde) / 100;
                    }
                }

                $hasKasaHareketi = ($gelir > 0 || $gider > 0);
                $includeRow = $hasKasaHareketi || ($isAdetli && $adet > 0);
                if ($includeRow) {
                    $payByDay[$gun] += $teknisyenPayi;
                }
            }
        }

        return $payByDay;
    }

    private function getServisChartDataLast7Days(?Personel $loggedInUser, int $tsrnTeknisyenPozisyonId): array
    {
        $musteriIptalDurumId = 9104;
        $atolyeDurumId = 9100;
        $startDate = Carbon::today()->subDays(6)->startOfDay();
        $endDate = Carbon::today()->endOfDay();

        $applyBaseFilter = function ($query) use ($loggedInUser, $tsrnTeknisyenPozisyonId) {
            $query->where(function ($q) {
                $q->where('silindi', '!=', 1)
                    ->orWhereNull('silindi');
            });
            if ($loggedInUser && $loggedInUser->poz_id == $tsrnTeknisyenPozisyonId) {
                $query->where('personel_id', $loggedInUser->id);
            }
        };

        $totalByDay = Servis::query()
            ->tap($applyBaseFilter)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as gun, COUNT(*) as cnt')
            ->groupBy('gun')
            ->pluck('cnt', 'gun');

        $cancelledByDay = Servis::query()
            ->tap($applyBaseFilter)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('servis_durum_id', $musteriIptalDurumId)
            ->selectRaw('DATE(created_at) as gun, COUNT(*) as cnt')
            ->groupBy('gun')
            ->pluck('cnt', 'gun');

        $workshopByDay = Servis::query()
            ->tap($applyBaseFilter)
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->where('servis_durum_id', $atolyeDurumId)
            ->selectRaw('DATE(updated_at) as gun, COUNT(*) as cnt')
            ->groupBy('gun')
            ->pluck('cnt', 'gun');

        $dates = [];
        $totalServicesData = [];
        $cancelledServicesData = [];
        $workshopServicesData = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $gun = $date->format('Y-m-d');
            $dates[] = $date->locale('tr')->translatedFormat('d M');
            $totalServicesData[] = (int) ($totalByDay[$gun] ?? 0);
            $cancelledServicesData[] = (int) ($cancelledByDay[$gun] ?? 0);
            $workshopServicesData[] = (int) ($workshopByDay[$gun] ?? 0);
        }

        return [
            'dates' => $dates,
            'total' => $totalServicesData,
            'cancelled' => $cancelledServicesData,
            'workshop' => $workshopServicesData,
            'totalSum' => array_sum($totalServicesData),
            'cancelledSum' => array_sum($cancelledServicesData),
            'workshopSum' => array_sum($workshopServicesData),
        ];
    }

    private function getTechnicianDailyNetKalan(string $gun, ?Personel $loggedInUser): float
    {
        $teknisyenPozisyonId = 1077; // TŞRN Teknisyen pozisyon ID'si
        $teknisyenlerQuery = Personel::where('poz_id', $teknisyenPozisyonId)->orderBy('ad');
        if ($loggedInUser && $loggedInUser->poz_id === $teknisyenPozisyonId) {
            $teknisyenlerQuery->where('id', $loggedInUser->id);
        }
        $teknisyenler = $teknisyenlerQuery->get(['id', 'ad', 'calisma_sekli_type', 'calisma_sekli_adet_tutar']);

        $adetliDisiDurumlar = [9102, 9477, 9104, 9106, 9110]; // Müşteriye Ulaşılamadı, Yarın Gidilecek, Müşteri İptal Etti, Fiyatta Anlaşılamadı, Haber Verecek
        $gunlukOzetler = [];
        foreach ($teknisyenler as $teknisyen) {
            $kasaQuery = Kasa::query()
                ->forTahsilEden($teknisyen->id)
                ->whereDate('islem_tarihi', $gun)
                ->where('gerceklesme', 1)
                ->where(function ($q) {
                    $q->where('silindi', '!=', 1)
                        ->orWhereNull('silindi');
                });

            $gelir = (clone $kasaQuery)->where('odeme_yonu', 1)->sum('tutar');
            $gider = (clone $kasaQuery)->where('odeme_yonu', -1)->sum('tutar');

            $calismaSekliType = $teknisyen->calisma_sekli_type ?? null;
            $calismaAdetTutar = $teknisyen->calisma_sekli_adet_tutar ?? null;
            $isAdetli = (Str::lower(trim((string) $calismaSekliType)) === 'adetli');
            $teknisyenPayi = 0;
            $firmaPayi = 0;

            $adet = 0;
            if ($isAdetli) {
                $adet = Servis::query()
                    ->where('personel_id', $teknisyen->id)
                    ->whereDate('tarih', $gun)
                    ->whereNotIn('servis_durum_id', $adetliDisiDurumlar)
                    ->where(function ($q) {
                        $q->where('silindi', '!=', 1)
                            ->orWhereNull('silindi');
                    })
                    ->count();
                $firmaPayi = ($adet * (float) $calismaAdetTutar) - $gider;
            } elseif ($calismaSekliType) {
                preg_match_all('/\d{1,3}/', (string) $calismaSekliType, $matches);
                $sayilar = $matches[0] ?? [];
                if (count($sayilar) >= 2) {
                    $teknisyenYuzde = (int) $sayilar[0];
                    $firmaYuzde = (int) $sayilar[1];
                    $toplamYuzde = $teknisyenYuzde + $firmaYuzde;
                    $netTutar = $gelir - $gider;
                    if ($toplamYuzde > 0 && $toplamYuzde <= 100) {
                        $teknisyenPayi = ($netTutar * $teknisyenYuzde) / 100;
                        $firmaPayi = ($netTutar * $firmaYuzde) / 100;
                    }
                }
            } else {
                $teknisyenPayi = 0;
                $firmaPayi = ($gelir - $gider);
            }

            $hasKasaHareketi = ($gelir > 0 || $gider > 0);
            $includeRow = $hasKasaHareketi || ($isAdetli && $adet > 0);
            if ($includeRow) {
                $gunlukOzetler[] = [
                    'gelir' => $gelir,
                    'gider' => $gider,
                    'adetli' => $isAdetli,
                    'teknisyen_payi' => $teknisyenPayi,
                    'firma_payi' => $firmaPayi,
                ];
            }
        }

        $toplamFirmaPayi = collect($gunlukOzetler)->sum('firma_payi');
        $netKalan = $toplamFirmaPayi;

        return $netKalan;
    }
} 
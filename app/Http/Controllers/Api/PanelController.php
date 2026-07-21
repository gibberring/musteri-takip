<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Servis;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PanelController extends Controller
{
    /**
     * Mobil panel özeti: sayılar ve rol bazlı.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $operatorPozisyonId = 1073;
        $tsrnTeknisyenPozisyonId = 1077;
        $idariIslerPozisyonId = 1076;

        if ($user && in_array($user->poz_id, [$operatorPozisyonId, $tsrnTeknisyenPozisyonId, $idariIslerPozisyonId])) {
            return response()->json([
                'redirect_to' => 'servisler',
                'message' => 'Ana sayfanız servis kayıtlarıdır.',
            ]);
        }

        $bugunBaslangic = Carbon::today();
        $dunBaslangic = Carbon::yesterday();
        $dunSon = Carbon::yesterday()->endOfDay();

        $baseFilter = function ($q) {
            $q->where('silindi', '!=', 1)->orWhereNull('silindi');
        };

        $bugunServisQuery = Servis::whereDate('created_at', Carbon::today())->where($baseFilter);
        $dunServisQuery = Servis::whereBetween('created_at', [$dunBaslangic, $dunSon])->where($baseFilter);

        if ($user && (int) $user->poz_id === $tsrnTeknisyenPozisyonId) {
            $teknisyenYonlendirildiStatusId = 9098;
            $bugunServisQuery->where('personel_id', $user->id)->where('servis_durum_id', $teknisyenYonlendirildiStatusId);
            $dunServisQuery->where('personel_id', $user->id);
        }

        $bugunServisSayisi = $bugunServisQuery->count();
        $dunServisSayisi = $dunServisQuery->count();

        $toplamServisSayisi = Servis::where($baseFilter)->count();
        $iptalServisSayisi = Servis::where('silindi', 1)->count();

        return response()->json([
            'bugun_servis_sayisi' => $bugunServisSayisi,
            'dun_servis_sayisi'  => $dunServisSayisi,
            'toplam_servis_sayisi' => $toplamServisSayisi,
            'iptal_servis_sayisi'  => $iptalServisSayisi,
        ]);
    }
}

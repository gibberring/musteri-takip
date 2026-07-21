<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\RoleAbility;
use Illuminate\Support\Facades\Schema;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        // Basit yetki kontrolü: sadece Patron role (1071) erişsin (ileride genişletilebilir)
        if (!$user || (int)$user->poz_id !== 1071) {
            abort(403, 'Bu sayfaya erişim yetkiniz yok.');
        }

        // Tüm ability kayıtlarını role bazında grupla
        $abilities = RoleAbility::query()->get()->groupBy('role_id');

        // Pozisyon adlarını DB'den çek (varsa), yoksa varsayılan isimleri kullan
        $wantedRoleIds = [1071, 1073, 1076, 1077, 1080];
        $dbRoleNames = DB::table('tnm_personel_pozisyon')
            ->whereIn('id', $wantedRoleIds)
            ->pluck('ad', 'id')
            ->toArray();
        $defaultRoleNames = [
            1071 => 'Patron',
            1073 => 'Operatör',
            1076 => 'Harici Operatör',
            1077 => 'TŞRN Teknisyen',
            1080 => 'Muhasebe',
        ];
        $roleNames = [];
        foreach ($wantedRoleIds as $rid) {
            $roleNames[$rid] = $dbRoleNames[$rid] ?? $defaultRoleNames[$rid] ?? (string)$rid;
        }

        return view('settings.index', [
            'abilities' => $abilities,
            'roleNames' => $roleNames,
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        if (!$user || (int)$user->poz_id !== 1071) {
            return response()->json(['success' => false, 'message' => 'Yetkisiz işlem.'], 403);
        }

        $data = $request->input('abilities', []);
        if (empty($data)) {
            // JSON body ile gelmiş olabilir
            $json = json_decode($request->getContent(), true);
            if (is_array($json) && isset($json['abilities'])) {
                $data = $json['abilities'];
            }
        }
        // Beklenen format: abilities[ROLE_ID][ability] = true/false
        foreach ($data as $roleId => $list) {
            foreach ($list as $ability => $allowed) {
                $allowedBool = filter_var($allowed, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if ($allowedBool === null) {
                    $allowedBool = ((string)$allowed === '1' || (int)$allowed === 1);
                }
                RoleAbility::updateOrCreate(
                    ['role_id' => (int)$roleId, 'ability' => $ability],
                    ['allowed' => (bool)$allowedBool]
                );
            }
        }
        return response()->json(['success' => true, 'message' => 'İzinler güncellendi.']);
    }

    // ========== Markalar ==========
    public function markalar()
    {
        $user = Auth::user();
        if (!$user || (int)$user->poz_id !== 1071) {
            return response()->json(['success' => false, 'message' => 'Yetkisiz işlem.'], 403);
        }
        $list = DB::table('tnm_markalar')->select('id','ad')->orderBy('ad')->get();
        return response()->json($list);
    }
    public function markaStore(Request $request)
    {
        $user = Auth::user();
        if (!$user || (int)$user->poz_id !== 1071) {
            return response()->json(['success' => false, 'message' => 'Yetkisiz işlem.'], 403);
        }
        $ad = trim($request->input('ad',''));
        if ($ad==='') return response()->json(['success'=>false,'message'=>'Marka adı zorunlu'],422);
        $id = DB::table('tnm_markalar')->insertGetId(['ad'=>$ad]);
        return response()->json(['success'=>true,'id'=>$id]);
    }
    public function markaUpdate($id, Request $request)
    {
        $user = Auth::user();
        if (!$user || (int)$user->poz_id !== 1071) {
            return response()->json(['success' => false, 'message' => 'Yetkisiz işlem.'], 403);
        }
        $ad = trim($request->input('ad',''));
        if ($ad==='') return response()->json(['success'=>false,'message'=>'Marka adı zorunlu'],422);
        DB::table('tnm_markalar')->where('id',$id)->update(['ad'=>$ad]);
        return response()->json(['success'=>true]);
    }
    public function markaDestroy($id)
    {
        $user = Auth::user();
        if (!$user || (int)$user->poz_id !== 1071) {
            return response()->json(['success' => false, 'message' => 'Yetkisiz işlem.'], 403);
        }
        DB::table('tnm_markalar')->where('id',$id)->delete();
        return response()->json(['success'=>true]);
    }

    // ========== Cihaz Türleri ==========
    public function cihazTurleri()
    {
        $user = Auth::user();
        if (!$user || (int)$user->poz_id !== 1071) {
            return response()->json(['success' => false, 'message' => 'Yetkisiz işlem.'], 403);
        }
        $list = DB::table('tnm_cihazturleri')->select('id','ad')->orderBy('ad')->get();
        return response()->json($list);
    }
    public function cihazTuruStore(Request $request)
    {
        $user = Auth::user();
        if (!$user || (int)$user->poz_id !== 1071) {
            return response()->json(['success' => false, 'message' => 'Yetkisiz işlem.'], 403);
        }
        $ad = trim($request->input('ad',''));
        if ($ad==='') return response()->json(['success'=>false,'message'=>'Cihaz türü adı zorunlu'],422);
        $id = DB::table('tnm_cihazturleri')->insertGetId(['ad'=>$ad]);
        return response()->json(['success'=>true,'id'=>$id]);
    }
    public function cihazTuruUpdate($id, Request $request)
    {
        $user = Auth::user();
        if (!$user || (int)$user->poz_id !== 1071) {
            return response()->json(['success' => false, 'message' => 'Yetkisiz işlem.'], 403);
        }
        $ad = trim($request->input('ad',''));
        if ($ad==='') return response()->json(['success'=>false,'message'=>'Cihaz türü adı zorunlu'],422);
        DB::table('tnm_cihazturleri')->where('id',$id)->update(['ad'=>$ad]);
        return response()->json(['success'=>true]);
    }
    public function cihazTuruDestroy($id)
    {
        $user = Auth::user();
        if (!$user || (int)$user->poz_id !== 1071) {
            return response()->json(['success' => false, 'message' => 'Yetkisiz işlem.'], 403);
        }
        DB::table('tnm_cihazturleri')->where('id',$id)->delete();
        return response()->json(['success'=>true]);
    }

    // ========== Bölge Ayarları (İl/İlçe aktif listeleri) ==========
    public function regionsIndex()
    {
        $user = Auth::user();
        if (!$user || (int)$user->poz_id !== 1071) {
            return response()->json(['success' => false, 'message' => 'Yetkisiz'], 403);
        }
        $iller = DB::table('iller')->select('id','ad')->orderBy('ad')->get();
        $aktifIlIds = DB::table('aktif_iller')->pluck('il_id')->toArray();
        $ilceler = DB::table('ilceler')->select('id','ad','il_id')->orderBy('ad')->get();
        $aktifIlceIds = DB::table('aktif_ilceler')->pluck('ilce_id')->toArray();

        $iller = $iller->map(function($il) use ($aktifIlIds){ $il->aktif = in_array($il->id, $aktifIlIds); return $il; });
        $ilceler = $ilceler->map(function($ilce) use ($aktifIlceIds){ $ilce->aktif = in_array($ilce->id, $aktifIlceIds); return $ilce; });

        return response()->json(['success' => true, 'iller' => $iller, 'ilceler' => $ilceler]);
    }

    public function regionsSave(Request $request)
    {
        $user = Auth::user();
        if (!$user || (int)$user->poz_id !== 1071) {
            return response()->json(['success' => false, 'message' => 'Yetkisiz'], 403);
        }

        $data = $request->validate([
            'aktifIlIds' => 'array',
            'aktifIlIds.*' => 'integer',
            'aktifIlceIds' => 'array',
            'aktifIlceIds.*' => 'integer',
        ]);

        $aktifIlceIds = $data['aktifIlceIds'] ?? [];

        // Tam yenileme stratejisi: truncate + insert (İl aktifliği, seçilen ilçelerden türetilir)
        DB::table('aktif_iller')->truncate();

        DB::table('aktif_ilceler')->truncate();
        if (!empty($aktifIlceIds)) {
            // il_id'yi de dolduralım
            $pairs = DB::table('ilceler')->whereIn('id', $aktifIlceIds)->pluck('il_id','id');
            $rows = [];
            $derivedIlIds = [];
            foreach ($aktifIlceIds as $ilceId) {
                $ilId = (int)($pairs[$ilceId] ?? 0);
                $rows[] = [ 'ilce_id' => (int)$ilceId, 'il_id' => $ilId, 'created_by' => $user->id, 'created_at'=>now(), 'updated_at'=>now() ];
                if ($ilId) { $derivedIlIds[$ilId] = true; }
            }
            DB::table('aktif_ilceler')->insert($rows);

            if (!empty($derivedIlIds)) {
                $rowsIl = [];
                foreach (array_keys($derivedIlIds) as $ilId) {
                    $rowsIl[] = ['il_id' => (int)$ilId, 'created_by' => $user->id, 'created_at'=>now(), 'updated_at'=>now()];
                }
                DB::table('aktif_iller')->insert($rowsIl);
            }
        }

        return response()->json(['success' => true, 'message' => 'Bölge ayarları kaydedildi.']);
    }
}



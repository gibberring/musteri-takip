<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\Announcement;
use App\Models\AnnouncementRead;

class AnnouncementController extends Controller
{
    public function recent(Request $request)
    {
        $user = Auth::user();
        if (!$user) { return response()->json(['items' => []]); }
        $limit = (int)($request->query('limit', 10));
        $pozId = (int)($user->poz_id ?? 0);
        $rolesWanted = [];
        if ($pozId === 1077) { $rolesWanted = ['TEKNISYEN']; }
        else if ($pozId === 1073) { $rolesWanted = ['OPERATOR']; }
        else { $rolesWanted = ['TEKNISYEN', 'OPERATOR']; }

        $now = now();
        $data = [];
        foreach ($rolesWanted as $role) {
            $list = Announcement::query()
                ->where('hedef_rol', $role)
                ->where(function ($q) use ($user) {
                    $q->whereNull('personel_id')
                      ->orWhere('personel_id', $user->id);
                })
                ->where(function ($q) use ($user) {
                    $q->whereNull('servis_id')
                      ->orWhere('personel_id', $user->id);
                })
                ->where('aktif', true)
                ->where(function($q) use ($now){
                    $q->whereNull('published_at')
                      ->orWhere('published_at', '')
                      ->orWhere('published_at', '<=', $now);
                })
                ->orderByDesc('published_at')
                ->orderByDesc('id')
                ->limit($limit)
                ->get();

            $reads = AnnouncementRead::whereIn('announcement_id', $list->pluck('id')->all())
                ->where('user_id', $user->id)
                ->pluck('read_at', 'announcement_id');

            $items = $list->map(function($a) use ($reads){
                return [
                    'id' => $a->id,
                    'baslik' => $a->baslik,
                    'icerik' => $a->icerik,
                    'published_at' => $a->published_at,
                    'okundu' => $reads->has($a->id)
                ];
            })->values();

            $data[$role] = $items;
        }

        // Toplam okunmamış sayısı
        $unread = 0;
        foreach ($data as $items) { foreach ($items as $it) { if (!$it['okundu']) $unread++; } }

        return response()->json(['groups' => $data, 'unread' => $unread]);
    }

    public function markRead($id)
    {
        $user = Auth::user();
        if (!$user) { return response()->json(['success' => false], 401); }
        $ann = Announcement::find($id);
        if (!$ann) { return response()->json(['success' => false, 'message' => 'Duyuru bulunamadı'], 404); }
        $pozId = (int)($user->poz_id ?? 0);
        $rolesWanted = [];
        if ($pozId === 1077) { $rolesWanted = ['TEKNISYEN']; }
        else if ($pozId === 1073) { $rolesWanted = ['OPERATOR']; }
        else { $rolesWanted = ['TEKNISYEN', 'OPERATOR']; }
        if (!in_array($ann->hedef_rol, $rolesWanted, true)) {
            return response()->json(['success' => false, 'message' => 'Bu duyuruya erişim yetkiniz yok.'], 403);
        }
        AnnouncementRead::updateOrCreate(
            ['announcement_id' => $ann->id, 'user_id' => $user->id],
            ['read_at' => now()]
        );
        return response()->json(['success' => true]);
    }

    // Patron yönetimi
    public function adminIndex()
    {
        $user = Auth::user();
        if (!$user || (int)$user->poz_id !== 1071) { return response()->json(['success' => false], 403); }
        $list = Announcement::orderByDesc('published_at')->orderByDesc('id')->get();
        return response()->json(['success' => true, 'items' => $list]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user || (int)$user->poz_id !== 1071) { return response()->json(['success' => false], 403); }
        $data = $request->validate([
            'baslik' => 'required|string|max:255',
            'icerik' => 'nullable|string',
            'hedef_rol' => 'required|in:TEKNISYEN,OPERATOR',
            'aktif' => 'required',
            'published_at' => 'nullable|date'
        ]);
        // Tip düzeltmeleri
        $data['aktif'] = filter_var($data['aktif'], FILTER_VALIDATE_BOOLEAN);
        if (empty($data['published_at'])) { $data['published_at'] = null; }
        $data['olusturan_personel_id'] = $user->id;
        $ann = Announcement::create($data);
        return response()->json(['success' => true, 'item' => $ann]);
    }

    public function update($id, Request $request)
    {
        $user = Auth::user();
        if (!$user || (int)$user->poz_id !== 1071) { return response()->json(['success' => false], 403); }
        $ann = Announcement::findOrFail($id);
        $data = $request->validate([
            'baslik' => 'required|string|max:255',
            'icerik' => 'nullable|string',
            'hedef_rol' => 'required|in:TEKNISYEN,OPERATOR',
            'aktif' => 'required',
            'published_at' => 'nullable|date'
        ]);
        $data['aktif'] = filter_var($data['aktif'], FILTER_VALIDATE_BOOLEAN);
        if (empty($data['published_at'])) { $data['published_at'] = null; }
        $ann->update($data);
        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        if (!$user || (int)$user->poz_id !== 1071) { return response()->json(['success' => false], 403); }
        $ann = Announcement::findOrFail($id);
        $ann->delete();
        return response()->json(['success' => true]);
    }
}



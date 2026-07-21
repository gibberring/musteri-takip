<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PanelController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\MusteriController;
use App\Http\Controllers\ServisController;
use App\Http\Controllers\PersonelController;

/*
|--------------------------------------------------------------------------
| API Routes (Mobil uygulama - web rotalarına dokunmaz)
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/login/2fa', [AuthController::class, 'login2fa'])->middleware('throttle:5,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::get('/panel', [PanelController::class, 'index']);

    Route::get('/duyurular/son', [AnnouncementController::class, 'recent']);
    Route::post('/duyurular/{id}/okundu', [AnnouncementController::class, 'markRead']);

    Route::get('/iller', [MusteriController::class, 'getIller']);
    Route::get('/ilceler/{il_id}', [MusteriController::class, 'getIlceler']);

    Route::get('/musteriler', [MusteriController::class, 'index']);
    Route::get('/musteriler/{id}/detay', [MusteriController::class, 'getDetay']);

    Route::get('/servisler', [ServisController::class, 'index']);
    Route::get('/servisler/bekleyen-kayitlar', [ServisController::class, 'pendingIndex']);
    Route::get('/servisler/{servis}/detay', [ServisController::class, 'getDetay']);
    Route::put('/servisler/{servis}/durum-guncelle-detayli', [ServisController::class, 'updateDurumDetayli']);

    Route::get('/personeller', [PersonelController::class, 'index']);
    Route::get('/personeller/{personel}/get-detay', [PersonelController::class, 'getDetay']);
});

@extends('layouts.app')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;
    $profileTitle = 'Teknisyen Profili';
@endphp

@section('title', $profileTitle)

@push('page_specific_css')
{{-- Bu sayfaya özel CSS'ler buraya eklenebilir --}}
@endpush

@section('content')
    <!-- [ page-header ] start -->
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">{{ $profileTitle }}</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item">{{ $personel->ad}}</li>
            </ul>
        </div>
    </div>
    <!-- [ page-header ] end -->

    @php
        $loggedInUser = Auth::user();
        $operatorPozisyonId = 1073;
        $hariciOperatorPozisyonId = 1076;
        $patronPozisyonId = 1071;
        $muhasebePozisyonId = 1080;
        $canSeeKasaCards = !$loggedInUser || !in_array((int) $loggedInUser->poz_id, [$operatorPozisyonId, $hariciOperatorPozisyonId], true);
        $canSeeRestrictedTabs = $loggedInUser && in_array((int) $loggedInUser->poz_id, [$patronPozisyonId, $muhasebePozisyonId, $operatorPozisyonId], true);
    @endphp
    <!-- [ Main Content ] start -->
    <div class="main-content h-100 d-flex flex-column">
        <div class="row flex-grow-1 d-flex">
            <div class="col-lg-12 flex-grow-1 d-flex flex-column">
                <div class="row g-3 flex-grow-1 d-flex">
                    <div class="col-xxl-4 col-xl-6 flex-grow-1 d-flex flex-column">
                        <div class="card stretch stretch-full flex-grow-1 d-flex flex-column">
                            <div class="card-body">
                                <div class="mb-4 text-center">
                                    <div class="wd-150 ht-150 mx-auto mb-3 position-relative">
                                        @php
                                            $__pozId = $personel->poz_id ?? null;
                                            $__avatarFile = null;
                                            if (in_array($__pozId, [1071, 1080])) { $__avatarFile = 'boss.png'; }
                                            elseif (in_array($__pozId, [1074, 1077])) { $__avatarFile = '1.png'; }
                                            elseif (in_array($__pozId, [1073, 1076])) { $__avatarFile = 'call.png'; }
                                            $__avatarUrl = $__avatarFile ? asset('crm_assets/images/avatar/' . $__avatarFile) : asset('crm_assets/images/avatar/1.png');
                                        @endphp
                                        <div class="avatar-image wd-150 ht-150 border border-5 border-gray-3">
                                            <img src="{{ $__avatarUrl }}" alt="" class="img-fluid">
                                        </div>
                                        {{-- Teknisyen Aktif Durumu İçin Check İşareti --}}
                                        @if($personel->aktif)
                                        <div class="wd-10 ht-10 text-success rounded-circle position-absolute translate-middle" style="top: 76%; right: 10px">
                                            <i class="bi bi-patch-check-fill"></i>
                                        </div>
                                        @endif
                                    </div>
                                    <div class="mb-4">
                                        <div class="fs-14 fw-bold">Ad Soyad: {{ $personel->ad }}</div>
                                        <div class="fs-12 fw-bold">Kullanıcı Adı: {{ $personel->nick }}</div>
                                        <a href="javascript:void(0);" class="fs-12 fw-normal text-muted d-block">{{ $personel->email ?? 'E-posta Yok' }}</a>
                                    </div>
                                    <div class="fs-12 fw-normal text-muted text-center d-flex flex-wrap gap-3 mb-4">
                                        <div class="flex-fill py-3 px-4 rounded-1 d-none d-sm-block border border-dashed border-gray-5">
                                            <h6 class="fs-15 fw-bolder">{{ $todayServiceCount }}</h6>
                                            <p class="fs-12 text-muted mb-0">Bugün</p>
                                        </div>
                                        <div class="flex-fill py-3 px-4 rounded-1 d-none d-sm-block border border-dashed border-gray-5">
                                            <h6 class="fs-15 fw-bolder">{{ $yesterdayServiceCount }}</h6>
                                            <p class="fs-12 text-muted mb-0">Dün</p>
                                        </div>
                                        <div class="flex-fill py-3 px-4 rounded-1 d-none d-sm-block border border-dashed border-gray-5">
                                            <h6 class="fs-15 fw-bolder">{{ $dayBeforeYesterdayServiceCount }}</h6>
                                            <p class="fs-12 text-muted mb-0">Önceki Gün</p>
                                        </div>
                                    </div>
                                </div>
                                <ul class="list-unstyled mb-4">
                                    <li class="hstack justify-content-between mb-4">
                                        <span class="text-muted fw-medium hstack gap-3"><i class="feather-map-pin"></i>Adres</span>
                                        <a href="javascript:void(0);" class="float-end">{{ ($personel->adres ?? '') . ' ' . ($personel->ilce->ad ?? '') . '/' . ($personel->il->ad ?? '') }}</a>
                                    </li>
                                    <li class="hstack justify-content-between mb-4">
                                        <span class="text-muted fw-medium hstack gap-3"><i class="feather-phone"></i>Telefon</span>
                                        <a href="javascript:void(0);" class="float-end">{{ $personel->tel1 ?? 'N/A' }}</a>
                                    </li>
                                    <li class="hstack justify-content-between mb-0">
                                        <span class="text-muted fw-medium hstack gap-3"><i class="feather-mail"></i>Email</span>
                                        <a href="javascript:void(0);" class="float-end">{{ $personel->email ?? 'N/A' }}</a>
                                    </li>
                                </ul>
                                <div class="d-flex gap-2 text-center pt-4">
                                    {{-- Silme ve Düzenleme butonları sadece patron için görünür olabilir --}}
                                    @php
                                        $loggedInUser = Auth::user();
                                        $patronPozisyonId = 1071;
                                    @endphp
                                    @if($loggedInUser && $loggedInUser->poz_id == $patronPozisyonId)
                                    <a href="javascript:void(0);" class="w-50 btn btn-light-brand" onclick="// Delete işlemi buraya gelecek">
                                        <i class="feather-trash-2 me-2"></i>
                                        <span>PERSONELİ SİL</span>
                                    </a>
                                    <a href="javascript:void(0);" class="w-50 btn btn-primary" onclick="// Edit işlemi buraya gelecek">
                                        <i class="feather-edit me-2"></i>
                                        <span>PROFİLİ DÜZENLE</span>
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                        {{-- Sosyal Medya ve Öneriler Kısımları bizim için gerekli değil, kaldırıldı --}}
                    </div>
                    <div class="col-xxl-8 col-xl-12 d-flex flex-column">
                        <div class="row g-3">
                            @if($canSeeKasaCards)
                                <div class="col-xl-6 d-flex flex-column">
                                    <div class="card border-0 h-100 flex-grow-1 d-flex flex-column">
                                        <div class="card-header d-flex align-items-center justify-content-between p-3">
                                            <h5 class="mb-0">Günlük Kasa Durumu</h5>
                                        </div>
                                        <div class="card-body d-flex flex-column gap-3">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="d-flex align-items-center gap-2 text-muted">
                                                    <i class="feather-trending-up text-success"></i>
                                                    <span>Gelir</span>
                                                </div>
                                                <div class="fw-bold text-success">
                                                    {{ number_format((float) $gunlukGelir, 2, ',', '.') }} TL
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="d-flex align-items-center gap-2 text-muted">
                                                    <i class="feather-trending-down text-danger"></i>
                                                    <span>Gider</span>
                                                </div>
                                                <div class="fw-bold text-danger">
                                                    {{ number_format((float) $gunlukGider, 2, ',', '.') }} TL
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="d-flex align-items-center gap-2 text-muted">
                                                    <i class="feather-user-check text-primary"></i>
                                                    <span>Teknisyen Payı</span>
                                                </div>
                                                <div class="fw-bold text-primary">
                                                    {{ number_format((float) $gunlukTeknisyenPayi, 2, ',', '.') }} TL
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="d-flex align-items-center gap-2 text-muted">
                                                    <i class="feather-briefcase text-warning"></i>
                                                    <span>Firma Ödemesi</span>
                                                </div>
                                                <div class="fw-bold text-warning">
                                                    {{ number_format((float) $gunlukFirmaPayi, 2, ',', '.') }} TL
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-6 d-flex flex-column">
                                    {{-- Son 7 Günlük Kasa Hareketleri Grafiği --}}
                                    <div class="card border-0 h-100 flex-grow-1 d-flex flex-column">
                                        <div class="card-header d-flex align-items-center justify-content-between p-3">
                                            <h5 class="mb-0">Son 7 Günlük Kasa Detayı</h5>
                                        </div>
                                        <div class="card-body py-0 px-3">
                                            <div style="height: auto;">
                                                <div id="kasaHareketleriChart"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <div class="col-xl-6 d-flex flex-column">
                                {{-- Son 7 Günlük İş Adetleri Grafiği --}}
                                <div class="card border-0 h-100 flex-grow-1 d-flex flex-column">
                                    <div class="card-header d-flex align-items-center justify-content-between p-3">
                                        <h5 class="mb-0">Son 7 Gün Detayı</h5>
                                    </div>
                                    <div class="card-body py-0 px-3">
                                        <div style="height: auto;">
                                            <div id="serviceCountChart"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-6 d-flex flex-column">
                                {{-- Son 7 Gün Tekrar Arıza Grafiği --}}
                                <div class="card border-0 h-100 flex-grow-1 d-flex flex-column">
                                    <div class="card-header d-flex align-items-center justify-content-between p-3">
                                        <h5 class="mb-0">Son 7 Gün Tekrar Arıza</h5>
                                    </div>
                                    <div class="card-body py-0 px-3">
                                        <div style="height: auto;">
                                            <div id="tekrarArizaChart"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sekmeli Servis Listeleri Başlangıcı --}}
        <div class="row mt-4">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <ul class="nav nav-tabs mb-3" id="pills-tab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="pills-bekleyen-tab" data-bs-toggle="pill" data-bs-target="#pills-bekleyen" type="button" role="tab" aria-controls="pills-bekleyen" aria-selected="true">
                                    Yeni Kayıtlar @if($bekleyenIsler->count() > 0)<span class="badge bg-primary ms-1">{{ $bekleyenIsler->count() }}</span>@endif
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="pills-parca-tab" data-bs-toggle="pill" data-bs-target="#pills-parca" type="button" role="tab" aria-controls="pills-parca" aria-selected="false">
                                    Parça Gidecek @if($parcaGidecekServisler->count() > 0)<span class="badge bg-dark ms-1">{{ $parcaGidecekServisler->count() }}</span>@endif
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="pills-yarin-tab" data-bs-toggle="pill" data-bs-target="#pills-yarin" type="button" role="tab" aria-controls="pills-yarin" aria-selected="false">
                                    Yarın Gidilecek @if($yarinGidilecekServisler->count() > 0)<span class="badge bg-secondary ms-1">{{ $yarinGidilecekServisler->count() }}</span>@endif
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="pills-tekrar-tab" data-bs-toggle="pill" data-bs-target="#pills-tekrar" type="button" role="tab" aria-controls="pills-tekrar" aria-selected="false">
                                    Tekrar Arızalar @if($tekrarArizalar->count() > 0)<span class="badge bg-danger ms-1">{{ $tekrarArizalar->count() }}</span>@endif
                                </button>
                            </li>
                            @if($canSeeRestrictedTabs)
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="pills-iptal-tab" data-bs-toggle="pill" data-bs-target="#pills-iptal" type="button" role="tab" aria-controls="pills-iptal" aria-selected="false">
                                        İptal Servisler @if($iptalServisler->count() > 0)<span class="badge bg-danger ms-1">{{ $iptalServisler->count() }}</span>@endif
                                    </button>
                                </li>
                            @endif
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="pills-atolye-tab" data-bs-toggle="pill" data-bs-target="#pills-atolye" type="button" role="tab" aria-controls="pills-atolye" aria-selected="false">
                                    Atölyede @if($atolyedeServisler->count() > 0)<span class="badge bg-primary ms-1">{{ $atolyedeServisler->count() }}</span>@endif
                                </button>
                            </li>
                            @if($canSeeRestrictedTabs)
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="pills-fiyat-tab" data-bs-toggle="pill" data-bs-target="#pills-fiyat" type="button" role="tab" aria-controls="pills-fiyat" aria-selected="false">
                                        Fiyat Anlaşılmazlığı @if($fiyatAnlasilmazligi->count() > 0)<span class="badge bg-info ms-1">{{ $fiyatAnlasilmazligi->count() }}</span>@endif
                                    </button>
                                </li>
                            @endif
                            @if($canSeeRestrictedTabs)
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="pills-musteri-tab" data-bs-toggle="pill" data-bs-target="#pills-musteri" type="button" role="tab" aria-controls="pills-musteri" aria-selected="false">
                                        Müşteri Haber Verecek @if($musteriHaberVerecek->count() > 0)<span class="badge bg-secondary ms-1">{{ $musteriHaberVerecek->count() }}</span>@endif
                                    </button>
                                </li>
                            @endif
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="pills-iade-tab" data-bs-toggle="pill" data-bs-target="#pills-iade" type="button" role="tab" aria-controls="pills-iade" aria-selected="false">
                                    Ücret İade Süreci @if($ucretIadeSureci->count() > 0)<span class="badge bg-primary ms-1">{{ $ucretIadeSureci->count() }}</span>@endif
                                </button>
                            </li>
                        </ul>
                        <div class="tab-content" id="pills-tabContent">
                            {{-- Bekleyen İşler Tab İçeriği --}}
                            <div class="tab-pane fade show active" id="pills-bekleyen" role="tabpanel" aria-labelledby="pills-bekleyen-tab" tabindex="0">
                                @if($bekleyenIsler->isEmpty())
                                    <p class="text-center mt-3">Bu sekmede görüntülenecek yeni kayıt bulunmamaktadır.</p>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>S.NO</th>
                                                    <th>MÜŞTERİ</th>
                                                    <th>Marka</th>
                                                    <th>CİHAZ TÜRÜ</th>
                                                    <th>Durum</th>
                                                    <th>TARİH</th>
                                                    <th>Açıklama</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($bekleyenIsler as $servis)
                                                    <tr class="servis-row-click" data-servis-id="{{ $servis->id }}" style="cursor: pointer;">
                                                        <td><a href="javascript:void(0);" class="servis-detay-ac-btn" data-servis-id="{{ $servis->id }}">#{{ $servis->id }}</a></td>
                                                        <td>{{ $servis->musteri->ad ?? 'N/A' }}</td>
                                                        <td>{{ $servis->marka->ad ?? 'N/A' }}</td>
                                                        <td>{{ $servis->cihazTuru->ad ?? 'N/A' }}</td>
                                                        <td><span class="badge bg-warning">{{ $servis->servisDurum->ad ?? 'N/A' }}</span></td>
                                                        <td>{{ Carbon::parse($servis->tarih)->format('d.m.Y') }}</td>
                                                        <td>{{ Str::limit($servis->aciklama, 50) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>

                            {{-- Parça Gidecek Tab İçeriği --}}
                            <div class="tab-pane fade" id="pills-parca" role="tabpanel" aria-labelledby="pills-parca-tab" tabindex="0">
                                @if($parcaGidecekServisler->isEmpty())
                                    <p class="text-center mt-3">Bu sekmede görüntülenecek kayıt bulunmamaktadır.</p>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>S.NO</th>
                                                    <th>MÜŞTERİ</th>
                                                    <th>Marka</th>
                                                    <th>CİHAZ TÜRÜ</th>
                                                    <th>Durum</th>
                                                    <th>TARİH</th>
                                                    <th>Açıklama</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($parcaGidecekServisler as $servis)
                                                    <tr class="servis-row-click" data-servis-id="{{ $servis->id }}" style="cursor: pointer;">
                                                        <td><a href="javascript:void(0);" class="servis-detay-ac-btn" data-servis-id="{{ $servis->id }}">#{{ $servis->id }}</a></td>
                                                        <td>{{ $servis->musteri->ad ?? 'N/A' }}</td>
                                                        <td>{{ $servis->marka->ad ?? 'N/A' }}</td>
                                                        <td>{{ $servis->cihazTuru->ad ?? 'N/A' }}</td>
                                                        <td><span class="badge bg-dark">{{ $servis->servisDurum->ad ?? 'N/A' }}</span></td>
                                                        <td>{{ Carbon::parse($servis->tarih)->format('d.m.Y') }}</td>
                                                        <td>{{ Str::limit($servis->aciklama, 50) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>

                            {{-- Yarın Gidilecek Tab İçeriği --}}
                            <div class="tab-pane fade" id="pills-yarin" role="tabpanel" aria-labelledby="pills-yarin-tab" tabindex="0">
                                @if($yarinGidilecekServisler->isEmpty())
                                    <p class="text-center mt-3">Bu sekmede görüntülenecek kayıt bulunmamaktadır.</p>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>S.NO</th>
                                                    <th>MÜŞTERİ</th>
                                                    <th>Marka</th>
                                                    <th>CİHAZ TÜRÜ</th>
                                                    <th>Durum</th>
                                                    <th>TARİH</th>
                                                    <th>Açıklama</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($yarinGidilecekServisler as $servis)
                                                    <tr class="servis-row-click" data-servis-id="{{ $servis->id }}" style="cursor: pointer;">
                                                        <td><a href="javascript:void(0);" class="servis-detay-ac-btn" data-servis-id="{{ $servis->id }}">#{{ $servis->id }}</a></td>
                                                        <td>{{ $servis->musteri->ad ?? 'N/A' }}</td>
                                                        <td>{{ $servis->marka->ad ?? 'N/A' }}</td>
                                                        <td>{{ $servis->cihazTuru->ad ?? 'N/A' }}</td>
                                                        <td><span class="badge bg-secondary">{{ $servis->servisDurum->ad ?? 'N/A' }}</span></td>
                                                        <td>{{ Carbon::parse($servis->tarih)->format('d.m.Y') }}</td>
                                                        <td>{{ Str::limit($servis->aciklama, 50) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>

                            {{-- Tekrar Arızalar Tab İçeriği --}}
                            <div class="tab-pane fade" id="pills-tekrar" role="tabpanel" aria-labelledby="pills-tekrar-tab" tabindex="0">
                                @if($tekrarArizalar->isEmpty())
                                    <p class="text-center mt-3">Bu sekmede görüntülenecek kayıt bulunmamaktadır.</p>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>S.NO</th>
                                                    <th>MÜŞTERİ</th>
                                                    <th>Marka</th>
                                                    <th>CİHAZ TÜRÜ</th>
                                                    <th>Durum</th>
                                                    <th>TARİH</th>
                                                    <th>Açıklama</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($tekrarArizalar as $servis)
                                                    <tr data-servis-id="{{ $servis->id }}">
                                                        <td><a href="javascript:void(0);" class="servis-detay-ac-btn" data-servis-id="{{ $servis->id }}">#{{ $servis->id }}</a></td>
                                                        <td>{{ $servis->musteri->ad ?? 'N/A' }}</td>
                                                        <td>{{ $servis->marka->ad ?? 'N/A' }}</td>
                                                        <td>{{ $servis->cihazTuru->ad ?? 'N/A' }}</td>
                                                        <td><span class="badge bg-danger">{{ $servis->servisDurum->ad ?? 'N/A' }}</span></td>
                                                        <td>{{ Carbon::parse($servis->tarih)->format('d.m.Y') }}</td>
                                                        <td>{{ Str::limit($servis->aciklama, 50) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>

                            @if($canSeeRestrictedTabs)
                                {{-- İptal Servisler Tab İçeriği --}}
                                <div class="tab-pane fade" id="pills-iptal" role="tabpanel" aria-labelledby="pills-iptal-tab" tabindex="0">
                                    @if($iptalServisler->isEmpty())
                                        <p class="text-center mt-3">Bu sekmede görüntülenecek kayıt bulunmamaktadır.</p>
                                    @else
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>S.NO</th>
                                                        <th>MÜŞTERİ</th>
                                                        <th>Marka</th>
                                                        <th>CİHAZ TÜRÜ</th>
                                                        <th>Durum</th>
                                                        <th>TARİH</th>
                                                        <th>Açıklama</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($iptalServisler as $servis)
                                                        <tr data-servis-id="{{ $servis->id }}">
                                                            <td><a href="javascript:void(0);" class="servis-detay-ac-btn" data-servis-id="{{ $servis->id }}">#{{ $servis->id }}</a></td>
                                                            <td>{{ $servis->musteri->ad ?? 'N/A' }}</td>
                                                            <td>{{ $servis->marka->ad ?? 'N/A' }}</td>
                                                            <td>{{ $servis->cihazTuru->ad ?? 'N/A' }}</td>
                                                            <td><span class="badge bg-danger">{{ $servis->servisDurum->ad ?? 'N/A' }}</span></td>
                                                            <td>{{ Carbon::parse($servis->tarih)->format('d.m.Y') }}</td>
                                                            <td>{{ Str::limit($servis->aciklama, 50) }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            {{-- Atölyede Tab İçeriği --}}
                            <div class="tab-pane fade" id="pills-atolye" role="tabpanel" aria-labelledby="pills-atolye-tab" tabindex="0">
                                @if($atolyedeServisler->isEmpty())
                                    <p class="text-center mt-3">Bu sekmede görüntülenecek kayıt bulunmamaktadır.</p>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>S.NO</th>
                                                    <th>MÜŞTERİ</th>
                                                    <th>Marka</th>
                                                    <th>CİHAZ TÜRÜ</th>
                                                    <th>Durum</th>
                                                    <th>TARİH</th>
                                                    <th>Açıklama</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($atolyedeServisler as $servis)
                                                    <tr data-servis-id="{{ $servis->id }}">
                                                        <td><a href="javascript:void(0);" class="servis-detay-ac-btn" data-servis-id="{{ $servis->id }}">#{{ $servis->id }}</a></td>
                                                        <td>{{ $servis->musteri->ad ?? 'N/A' }}</td>
                                                        <td>{{ $servis->marka->ad ?? 'N/A' }}</td>
                                                        <td>{{ $servis->cihazTuru->ad ?? 'N/A' }}</td>
                                                        <td><span class="badge bg-primary">{{ $servis->servisDurum->ad ?? 'N/A' }}</span></td>
                                                        <td>{{ Carbon::parse($servis->tarih)->format('d.m.Y') }}</td>
                                                        <td>{{ Str::limit($servis->aciklama, 50) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>

                            @if($canSeeRestrictedTabs)
                                {{-- Fiyat Anlaşılmazlığı Tab İçeriği --}}
                                <div class="tab-pane fade" id="pills-fiyat" role="tabpanel" aria-labelledby="pills-fiyat-tab" tabindex="0">
                                    @if($fiyatAnlasilmazligi->isEmpty())
                                        <p class="text-center mt-3">Bu sekmede görüntülenecek kayıt bulunmamaktadır.</p>
                                    @else
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>S.NO</th>
                                                        <th>MÜŞTERİ</th>
                                                        <th>Marka</th>
                                                        <th>CİHAZ TÜRÜ</th>
                                                        <th>Durum</th>
                                                        <th>TARİH</th>
                                                        <th>Açıklama</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($fiyatAnlasilmazligi as $servis)
                                                        <tr data-servis-id="{{ $servis->id }}">
                                                            <td><a href="javascript:void(0);" class="servis-detay-ac-btn" data-servis-id="{{ $servis->id }}">#{{ $servis->id }}</a></td>
                                                            <td>{{ $servis->musteri->ad ?? 'N/A' }}</td>
                                                            <td>{{ $servis->marka->ad ?? 'N/A' }}</td>
                                                            <td>{{ $servis->cihazTuru->ad ?? 'N/A' }}</td>
                                                            <td><span class="badge bg-info">{{ $servis->servisDurum->ad ?? 'N/A' }}</span></td>
                                                            <td>{{ Carbon::parse($servis->tarih)->format('d.m.Y') }}</td>
                                                            <td>{{ Str::limit($servis->aciklama, 50) }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            @if($canSeeRestrictedTabs)
                                {{-- Müşteri Haber Verecek Tab İçeriği --}}
                                <div class="tab-pane fade" id="pills-musteri" role="tabpanel" aria-labelledby="pills-musteri-tab" tabindex="0">
                                    @if($musteriHaberVerecek->isEmpty())
                                        <p class="text-center mt-3">Bu sekmede görüntülenecek kayıt bulunmamaktadır.</p>
                                    @else
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>S.NO</th>
                                                        <th>MÜŞTERİ</th>
                                                        <th>Marka</th>
                                                        <th>CİHAZ TÜRÜ</th>
                                                        <th>Durum</th>
                                                        <th>TARİH</th>
                                                        <th>Açıklama</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($musteriHaberVerecek as $servis)
                                                        <tr data-servis-id="{{ $servis->id }}">
                                                            <td><a href="javascript:void(0);" class="servis-detay-ac-btn" data-servis-id="{{ $servis->id }}">#{{ $servis->id }}</a></td>
                                                            <td>{{ $servis->musteri->ad ?? 'N/A' }}</td>
                                                            <td>{{ $servis->marka->ad ?? 'N/A' }}</td>
                                                            <td>{{ $servis->cihazTuru->ad ?? 'N/A' }}</td>
                                                            <td><span class="badge bg-secondary">{{ $servis->servisDurum->ad ?? 'N/A' }}</span></td>
                                                            <td>{{ Carbon::parse($servis->tarih)->format('d.m.Y') }}</td>
                                                            <td>{{ Str::limit($servis->aciklama, 50) }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            {{-- Ücret İade Süreci Tab İçeriği --}}
                            <div class="tab-pane fade" id="pills-iade" role="tabpanel" aria-labelledby="pills-iade-tab" tabindex="0">
                                @if($ucretIadeSureci->isEmpty())
                                    <p class="text-center mt-3">Bu sekmede görüntülenecek kayıt bulunmamaktadır.</p>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>S.NO</th>
                                                    <th>MÜŞTERİ</th>
                                                    <th>Marka</th>
                                                    <th>CİHAZ TÜRÜ</th>
                                                    <th>Durum</th>
                                                    <th>TARİH</th>
                                                    <th>Açıklama</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($ucretIadeSureci as $servis)
                                                    <tr data-servis-id="{{ $servis->id }}">
                                                        <td><a href="javascript:void(0);" class="servis-detay-ac-btn" data-servis-id="{{ $servis->id }}">#{{ $servis->id }}</a></td>
                                                        <td>{{ $servis->musteri->ad ?? 'N/A' }}</td>
                                                        <td>{{ $servis->marka->ad ?? 'N/A' }}</td>
                                                        <td>{{ $servis->cihazTuru->ad ?? 'N/A' }}</td>
                                                        <td><span class="badge bg-primary">{{ $servis->servisDurum->ad ?? 'N/A' }}</span></td>
                                                        <td>{{ Carbon::parse($servis->tarih)->format('d.m.Y') }}</td>
                                                        <td>{{ Str::limit($servis->aciklama, 50) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- Sekmeli Servis Listeleri Bitişi --}}

    </div>
    <!-- [ Main Content ] end -->
@endsection

@push('page_specific_vendor_js')
<script src="{{ asset('crm_assets/vendors/js/apexcharts.min.js') }}"></script>
@endpush

@push('page_specific_scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var labels = @json($labels);
        var serviceDetailChartData = @json($serviceDetailChartData);
        var kasaChartData = @json($kasaChartData);
        var tekrarArizaData = @json($tekrarArizaData);

        // Son 7 Gün Detayı Grafiği (ApexCharts)
        if (typeof ApexCharts !== 'undefined' && document.getElementById('serviceCountChart')) {
            var serviceCountOptions = {
                chart: { type: 'bar', height: 200, toolbar: { show: false } },
                plotOptions: { bar: { horizontal: false, columnWidth: '30%', endingShape: 'rounded' } },
                dataLabels: { enabled: false },
                stroke: { show: true, width: 2, colors: ['transparent'] },
                series: serviceDetailChartData.datasets.map(function(dataset) {
                    return { name: dataset.name, data: dataset.data };
                }),
                xaxis: {
                    categories: labels,
                    axisBorder: { show: false }, axisTicks: { show: false },
                    labels: { style: { fontSize: "10px", colors: "#A0ACBB" } }
                },
                yaxis: {
                    labels: { formatter: function(val) { return parseInt(val); }, offsetX: -5, offsetY: 0, style: { color: "#A0ACBB" } },
                    min: 0, // Minimum değeri 0 olarak ayarla
                    forceNiceScale: true, // Daha güzel sayılar kullanmaya zorlar
                    tickAmount: 5 // Belirli sayıda tick etiketi belirle
                },
                colors: ['#3454d1', '#198754', '#dc3545'], // Toplam Servis (Mavi), Atölye (Yeşil), İptal (Kırmızı)
                tooltip: { y: { formatter: function (val) { return val + " adet iş" } }, style: { fontSize: "12px", fontFamily: "Inter" } },
                grid: {
                    show: false, // Arka plan ızgarasını kapat
                    padding: { top: 0, right: 0, bottom: 0, left: 0 } // Tüm paddingleri sıfırla
                },
                legend: { show: true, position: 'top', horizontalAlign: 'right', fontFamily: "Inter", fontWeight: 500, fontSize: '12px', labels:{ colors: "#A0ACBB", fontFamily:"Inter" }, markers: { width: 10, height: 10 }, itemMargin: { horizontal: 10, vertical: 0 } }
            };
            var serviceCountApexChart = new ApexCharts(document.querySelector("#serviceCountChart"), serviceCountOptions);
            try { serviceCountApexChart.render(); } catch (error) { console.error("İş adeti grafiği oluşturulurken hata:", error); }
        }

        // Son 7 Günlük Kasa Hareketleri Grafiği (ApexCharts)
        if (typeof ApexCharts !== 'undefined' && document.getElementById('kasaHareketleriChart')) {
            var kasaHareketleriOptions = {
                chart: { type: 'line', height: 200, toolbar: { show: false } },
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 3 },
                series: [
                    {
                        name: 'Gelir',
                        data: kasaChartData.datasets[0].data
                    },
                    {
                        name: 'Gider',
                        data: kasaChartData.datasets[1].data
                    }
                ],
                xaxis: {
                    categories: kasaChartData.labels,
                    axisBorder: { show: false }, axisTicks: { show: false },
                    labels: { style: { fontSize: "10px", colors: "#A0ACBB" } }
                },
                yaxis: {
                    labels: { formatter: function(val) { return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(val); }, offsetX: -5, offsetY: 0, style: { color: "#A0ACBB" } },
                    min: 0, // Minimum değeri 0 olarak ayarla
                    forceNiceScale: true, // Daha güzel sayılar kullanmaya zorlar
                    tickAmount: 5 // Belirli sayıda tick etiketi belirle
                },
                colors: ['#198754', '#dc3545'], // Yeşil ve Kırmızı
                tooltip: { y: { formatter: function (val) { return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(val); } }, style: { fontSize: "12px", fontFamily: "Inter" } },
                grid: {
                    show: false, // Arka plan ızgarasını kapat
                    padding: { top: 0, right: 0, bottom: 0, left: 0 } // Tüm paddingleri sıfırla
                },
                legend: { show: true, position: 'top', horizontalAlign: 'right', fontFamily: "Inter", fontWeight: 500, fontSize: '12px', labels:{ colors: "#A0ACBB", fontFamily:"Inter" }, markers: { width: 10, height: 10 }, itemMargin: { horizontal: 10, vertical: 0 } }
            };
            var kasaHareketleriApexChart = new ApexCharts(document.querySelector("#kasaHareketleriChart"), kasaHareketleriOptions);
            try { kasaHareketleriApexChart.render(); } catch (error) { console.error("Kasa hareketleri grafiği oluşturulurken hata:", error); }
        }

        // Son 7 Gün Tekrar Arıza Grafiği (ApexCharts)
        if (typeof ApexCharts !== 'undefined' && document.getElementById('tekrarArizaChart')) {
            var tekrarArizaOptions = {
                chart: { type: 'bar', height: 200, toolbar: { show: false } },
                plotOptions: { bar: { horizontal: false, columnWidth: '30%', endingShape: 'rounded' } },
                dataLabels: { enabled: false },
                stroke: { show: true, width: 2, colors: ['transparent'] },
                series: [
                    { name: 'Tekrar Arıza', data: tekrarArizaData }
                ],
                xaxis: {
                    categories: labels,
                    axisBorder: { show: false }, axisTicks: { show: false },
                    labels: { style: { fontSize: "10px", colors: "#A0ACBB" } }
                },
                yaxis: {
                    labels: { formatter: function(val) { return parseInt(val); }, offsetX: -5, offsetY: 0, style: { color: "#A0ACBB" } },
                    min: 0,
                    forceNiceScale: true,
                    tickAmount: 5
                },
                colors: ['#fd7e14'],
                tooltip: { y: { formatter: function (val) { return val + " adet iş" } }, style: { fontSize: "12px", fontFamily: "Inter" } },
                grid: {
                    show: false,
                    padding: { top: 0, right: 0, bottom: 0, left: 0 }
                },
                legend: { show: false }
            };
            var tekrarArizaApexChart = new ApexCharts(document.querySelector("#tekrarArizaChart"), tekrarArizaOptions);
            try { tekrarArizaApexChart.render(); } catch (error) { console.error("Tekrar arıza grafiği oluşturulurken hata:", error); }
        }
    });

    // Bekleyen işler ve diğer sekmelerdeki servis ID linklerine tıklandığında modalı aç
    document.addEventListener('click', function(e) {
        var target = e.target;
        var row = target && target.closest ? target.closest('tr.servis-row-click') : null;
        if (row && row.getAttribute('data-servis-id')) {
            var servisId = row.getAttribute('data-servis-id');
            if (servisId && window.openServisDetay) {
                window.openServisDetay(servisId);
            }
            return;
        }
        if (target && target.classList && target.classList.contains('servis-detay-ac-btn')) {
            e.preventDefault();
            var servisIdLink = target.getAttribute('data-servis-id');
            if (servisIdLink && window.openServisDetay) {
                window.openServisDetay(servisIdLink);
            }
        }
    });

    function getStatusClassById(statusId) {
        var id = String(statusId || '');
        if (['9098'].includes(id)) return 'bg-secondary';
        if (['9103', '9477'].includes(id)) return 'bg-warning';
        if (['9116', '9104'].includes(id)) return 'bg-danger';
        if (['9100', '9524'].includes(id)) return 'bg-primary';
        if (['9106'].includes(id)) return 'bg-info';
        if (['9110'].includes(id)) return 'bg-secondary';
        return 'bg-secondary';
    }

    $(document).on('servisDurumuGuncellendi', function(_e, servisId, g) {
        if (!servisId) return;
        var statusId = g && (g.servis_durum_id || (g.servis_durum && g.servis_durum.id) || g.durum_id || (g.durum && g.durum.id));
        var statusName = g && (g.servis_durum_ad || (g.servis_durum && g.servis_durum.ad) || g.durum_ad || (g.durum && g.durum.ad));
        if (!statusName && window.crmData && Array.isArray(window.crmData.servisDurumlar) && statusId) {
            var match = window.crmData.servisDurumlar.find(function(s){ return String(s.id) === String(statusId); });
            statusName = match ? match.ad : statusName;
        }
        if (!statusName) return;
        $('tr[data-servis-id="' + servisId + '"]').each(function() {
            var $badgeCell = $(this).find('td').eq(4);
            var $badge = $badgeCell.find('.badge');
            if (!$badge.length) return;
            $badge.text(statusName);
            if (statusId) {
                $badge.removeClass('bg-warning bg-danger bg-primary bg-info bg-secondary')
                      .addClass(getStatusClassById(statusId));
            }
        });
    });
</script>
@endpush

@extends('layouts.app')

@section('title', 'Personel Listesi')

@push('page_specific_css')
<link rel="stylesheet" type="text/css" href="{{ asset('crm_assets/vendors/css/dataTables.bs5.min.css') }}">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    /* Personel sayfasına özel ek stiller buraya eklenebilir */
    #kasaListTable td, #kasaListTable th { /* BU PERSONEL TABLOSU İÇİN DE GEÇERLİ OLABİLİR VEYA AYRI YAPILABİLİR */
        font-size: 0.85rem; 
        padding: 0.4rem; 
    }
    /* === ORTAK MODAL STİLLERİ (PersonelDuzenleModal için) === */
    #personelDuzenleModal .modal-header {
        background-color: #3454d1; /* Güncellendi */
        color: white;
        padding-top: 0.5rem;
        padding-bottom: 0.5rem;
    }
    #personelDuzenleModal .modal-header .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%);
    }
    #personelDuzenleModal .modal-header .modal-title,
    #personelDuzenleModal .modal-header .modal-title span,
    #personelDuzenleModal .modal-header small span { /* Kayıt tarihi için */
        color: white !important;
    }
    #personelDuzenleModal .form-control-sm,
    #personelDuzenleModal .form-select-sm {
        padding: 0.2rem 0.4rem;
        height: auto; 
        line-height: 1.5;
        font-size: 0.8rem; 
    }
    #personelDuzenleModal .form-label-sm {
        font-size: 0.75rem; 
        margin-bottom: 0.1rem; 
        padding-top: calc(0.2rem + 1px); 
        padding-bottom: calc(0.2rem + 1px);
    }
     #personelDuzenleModal .input-group-sm > .btn {
         padding-top: 0.2rem;
         padding-bottom: 0.2rem; 
    }

    /* === ORTAK MODAL STİLLERİ (YeniPersonelModal için) === */
    #yeniPersonelModal .modal-header {
        background-color: #3454d1; /* Güncellendi */
        color: white;
        padding-top: 0.5rem;
        padding-bottom: 0.5rem;
    }
    #yeniPersonelModal .modal-header .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%);
    }
    #yeniPersonelModal .modal-header .modal-title {
        color: white !important;
    }
    #yeniPersonelModal .form-control-sm,
    #yeniPersonelModal .form-select-sm {
        padding: 0.2rem 0.4rem;
        height: auto; 
        line-height: 1.5;
        font-size: 0.8rem; 
    }
    #yeniPersonelModal .form-label-sm {
        font-size: 0.75rem; 
        margin-bottom: 0.1rem; 
        padding-top: calc(0.2rem + 1px); 
        padding-bottom: calc(0.2rem + 1px);
    }
    /* Personel Düzenleme Modalı sabit yükseklik ve kaydırma */
    #personelDuzenleModal .modal-dialog { max-width: 900px; }
    /* Bootstrap modal-dialog-scrollable kullanacağız; ekstra sticky/height gerekmez */

    /* DataTables font ayarı */
    #personelListTable_wrapper table th,
    #personelListTable_wrapper table td {
        font-family: 'Inter', sans-serif;
        font-size: 13px;
        font-weight: 600;
        line-height: 1.2; /* 15.6px / 13px ~ 1.2 */
        /* display: -webkit-box; */
        /* -webkit-line-clamp: 1; */
        /* -webkit-box-orient: vertical; */
        /* overflow: hidden; */
        /* text-overflow: ellipsis; */
        /* white-space: normal; */ 
    }
    /* Taşmayı engelle: tablo sabit yerleşim + hücrelerde satır kırma */
    #personelListTable { table-layout: fixed; width: 100%; }
    #personelListTable th, #personelListTable td { white-space: normal; word-break: break-word; }
    .table-responsive { overflow-x: hidden; }
    /* DataTables sayfalama ortalama dom ile yapılacak */
    .page-header-title,
    .page-header h5 {
        border-right: none !important;
        padding-right: 0 !important;
        margin-right: 0 !important;
    }
    #personelListTable tbody td {
        padding-top: 0.2rem;
        padding-bottom: 0.2rem;
    }
</style>
@endpush

@section('content')
    <!-- [ page-header ] start -->
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Personel Listesi</h5>
            </div>
        </div>
        @php
            $loggedInUser = Auth::user();
            $operatorPozisyonId = 1073; // Operatör pozisyon ID'si
        @endphp
        @if ($loggedInUser && $loggedInUser->poz_id != $operatorPozisyonId)
        <div class="d-flex d-sm-none align-items-center justify-content-end ms-auto">
            <button type="button" class="btn btn-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#yeniPersonelModal">
                <i class="feather-plus me-1"></i>
                <span>YENİ EKLE</span>
            </button>
        </div>
        <div class="page-header-right ms-auto d-none d-sm-flex">
            <div class="page-header-right-items">
                <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                    <input type="text" class="form-control form-control-sm" id="personelSearchInput" placeholder="Personel ara..." style="max-width: 220px;">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#yeniPersonelModal">
                        <i class="feather-plus me-2"></i>
                        <span>YENİ PERSONEL EKLE</span>
                    </button>
                </div>
            </div>
        </div>
        @endif
    </div>
    <!-- [ page-header ] end -->
    <!-- [ Main Content ] start -->
    <div class="main-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="card border-0 shadow-none stretch stretch-full">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover" id="personelListTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>AD SOYAD</th>
                                        <th>POZİSYON</th>
                                        <th>ÜYELİK</th>
                                        <th>DURUM</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($personeller as $personel)
                                        <tr class="clickable-row" data-personel-id="{{ $personel->id }}" style="cursor: pointer;">
                                            <td><a href="{{ route('personel.teknisyenProfil', ['personel' => $personel->id]) }}" class="fw-bold profile-link">#{{ $personel->id }}</a></td>
                                            <td>{{ $personel->ad }}</td>
                                            <td>{{ $personel->pozisyon ? $personel->pozisyon->ad : 'N/A' }}</td>
                                            <td>
                                                @if($personel->aktif == 1)
                                                    <span class="badge bg-soft-success text-success">Aktif</span>
                                                @else
                                                    <span class="badge bg-soft-danger text-danger">Pasif</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($personel->mesai_basladimi == 1)
                                                    <span class="badge bg-soft-success text-success">Mesaide</span>
                                                @else
                                                    <span class="badge bg-soft-danger text-danger">Çalışmıyor</span>
                                                @endif
                                            </td>
                                            
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @php $__toplamPersonel = $personeller->count(); @endphp
                            <div class="px-4 pb-2 text-end small text-muted">Toplam personel sayısı: <span id="personelTotalCount">{{ $__toplamPersonel }}</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- [ Main Content ] end -->
@endsection

@section('modals')
    <!--! ================================================================ !-->
    <!--! [Start] Personel Düzenleme Modal !-->
    <!--! ================================================================ !-->
    <div class="modal fade" id="personelDuzenleModal" tabindex="-1" aria-labelledby="personelDuzenleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="personelDuzenleModalLabel">Personel Düzenle (#<span id="modalPersonelId"></span>)</h5>
                    <small class="ms-3 text-white">Oluşturulma Tarihi: <span id="modalPersonelKayitTarihi">-</span></small>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="personelDuzenleForm">
                    @csrf
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" id="editPersonelId" name="personel_id">
                    <div class="modal-body">
                        {{-- Sekme Navigasyonu --}}
                        <ul class="nav nav-tabs nav-fill mb-3" id="personelTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="temel-bilgiler-tab" data-bs-toggle="tab" data-bs-target="#temel-bilgiler-pane" type="button" role="tab" aria-controls="temel-bilgiler-pane" aria-selected="true">Temel Bilgiler</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="adres-bilgileri-tab" data-bs-toggle="tab" data-bs-target="#adres-bilgileri-pane" type="button" role="tab" aria-controls="adres-bilgileri-pane" aria-selected="false">Adres Bilgileri</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="yetki-parametreler-tab" data-bs-toggle="tab" data-bs-target="#yetki-parametreler-pane" type="button" role="tab" aria-controls="yetki-parametreler-pane" aria-selected="false">Yetki & Parametreler</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="sifre-degisikligi-tab" data-bs-toggle="tab" data-bs-target="#sifre-degisikligi-pane" type="button" role="tab" aria-controls="sifre-degisikligi-pane" aria-selected="false">Şifre Değişikliği</button>
                            </li>
                        </ul>

                        {{-- Sekme İçerikleri --}}
                        <div class="tab-content" id="personelTabsContent">
                            {{-- Temel Bilgiler Sekmesi --}}
                            <div class="tab-pane fade show active" id="temel-bilgiler-pane" role="tabpanel" aria-labelledby="temel-bilgiler-tab" tabindex="0">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="row mb-2 align-items-center">
                                            <label for="modalPersonelAd" class="col-sm-4 col-form-label col-form-label-sm">Ad Soyad <span class="text-danger">*</span></label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control form-control-sm" id="modalPersonelAd" name="ad" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row mb-2 align-items-center">
                                            <label for="modalPersonelNick" class="col-sm-4 col-form-label col-form-label-sm">Kullanıcı Adı (Nick) <span class="text-danger">*</span></label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control form-control-sm" id="modalPersonelNick" name="nick" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row mb-2 align-items-center">
                                            <label for="modalPersonelPozisyon" class="col-sm-4 col-form-label col-form-label-sm">Pozisyon <span class="text-danger">*</span></label>
                                            <div class="col-sm-8">
                                                <select class="form-select form-select-sm" id="modalPersonelPozisyon" name="poz_id" required>
                                                    <option value="">Seçiniz...</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row mb-2 align-items-center">
                                            <label for="modalPersonelAktif" class="col-sm-4 col-form-label col-form-label-sm">Durum <span class="text-danger">*</span></label>
                                            <div class="col-sm-8">
                                                <select class="form-select form-select-sm" id="modalPersonelAktif" name="aktif" required>
                                                    <option value="1">Mesaide (Aktif)</option>
                                                    <option value="0">Çalışmıyor (Pasif)</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row mb-2 align-items-center">
                                            <label for="modalPersonelEmail" class="col-sm-4 col-form-label col-form-label-sm">E-posta</label>
                                            <div class="col-sm-8">
                                                <input type="email" class="form-control form-control-sm" id="modalPersonelEmail" name="email">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row mb-2 align-items-center">
                                            <label for="modalPersonelTwoFactorRequired" class="col-sm-4 col-form-label col-form-label-sm">2FA Zorunlu</label>
                                            <div class="col-sm-8">
                                                <input type="hidden" name="two_factor_required" value="0">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="modalPersonelTwoFactorRequired" name="two_factor_required" value="1">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row mb-2 align-items-center">
                                            <label for="modalPersonelTel1" class="col-sm-4 col-form-label col-form-label-sm">Telefon</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control form-control-sm" id="modalPersonelTel1" name="tel1">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row mb-2 align-items-center">
                                            <label for="modalPersonelTel2" class="col-sm-4 col-form-label col-form-label-sm">Telefon 2</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control form-control-sm" id="modalPersonelTel2" name="tel2">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row mb-2 align-items-center">
                                            <label for="modalPersonelIsBasiTarih" class="col-sm-4 col-form-label col-form-label-sm">İşe Başlama Tarihi</label>
                                            <div class="col-sm-8">
                                                <input type="date" class="form-control form-control-sm" id="modalPersonelIsBasiTarih" name="is_basi_tarih">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Adres Bilgileri Sekmesi --}}
                            <div class="tab-pane fade" id="adres-bilgileri-pane" role="tabpanel" aria-labelledby="adres-bilgileri-tab" tabindex="0">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="row mb-2 align-items-center">
                                            <label for="modalPersonelIl" class="col-sm-4 col-form-label col-form-label-sm">İl</label>
                                            <div class="col-sm-8">
                                                <select class="form-select form-select-sm" id="modalPersonelIl" name="il_id">
                                                    <option value="">İl Seçiniz...</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row mb-2 align-items-center">
                                            <label for="modalPersonelIlce" class="col-sm-4 col-form-label col-form-label-sm">İlçe</label>
                                            <div class="col-sm-8">
                                                <select class="form-select form-select-sm" id="modalPersonelIlce" name="ilce_id" disabled>
                                                    <option value="">Önce İl Seçiniz...</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="row mb-2">
                                            <label for="modalPersonelAdres" class="col-sm-2 col-form-label col-form-label-sm">Adres</label> {{-- col-sm-4'ten col-sm-2'ye --}}
                                            <div class="col-sm-10">
                                                <textarea class="form-control form-control-sm" id="modalPersonelAdres" name="adres" rows="3"></textarea> {{-- rows="2"'den rows="3"e --}}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Yetki & Parametreler Sekmesi --}}
                            <div class="tab-pane fade" id="yetki-parametreler-pane" role="tabpanel" aria-labelledby="yetki-parametreler-tab" tabindex="0">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="row mb-2 align-items-center">
                                            <label for="modalPersonelServisFis" class="col-sm-5 col-form-label col-form-label-sm">Servis Fişi Kullanabilir</label>
                                            <div class="col-sm-7">
                                                <select class="form-select form-select-sm" id="modalPersonelServisFis" name="servis_fis_kullanabilir">
                                                    <option value="1">Evet</option>
                                                    <option value="0">Hayır</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row mb-2 align-items-center">
                                            <label for="modalPersonelMesaiBasladimi" class="col-sm-5 col-form-label col-form-label-sm">Mesai Başladı mı?</label>
                                            <div class="col-sm-7">
                                                <select class="form-select form-select-sm" id="modalPersonelMesaiBasladimi" name="mesai_basladimi">
                                                    <option value="1">Evet</option>
                                                    <option value="0">Hayır</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row mb-2 align-items-center">
                                            <label for="modalPersonelEFis" class="col-sm-5 col-form-label col-form-label-sm">E-Fiş Verebilir</label>
                                            <div class="col-sm-7">
                                                <select class="form-select form-select-sm" id="modalPersonelEFis" name="e_fis_verebilir">
                                                    <option value="1">Evet</option>
                                                    <option value="0">Hayır</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6" id="modalPersonelCalismaSekliRow" style="display: none;">
                                        <div class="row mb-2 align-items-center">
                                            <label for="modalPersonelCalismaSekli" class="col-sm-5 col-form-label col-form-label-sm">Çalışma Şekli</label>
                                            <div class="col-sm-7">
                                                <select class="form-select form-select-sm" id="modalPersonelCalismaSekli" name="calisma_sekli_type">
                                                    <option value="">Seçiniz...</option>
                                                    <option value="50_50">1. çalışma şekli %50 %50</option>
                                                    <option value="60_40">2. çalışma şekli %60 %40</option>
                                                    <option value="55_45">3. çalışma şekli %55 %45</option>
                                                    <option value="65_35">4. çalışma şekli %65 %35</option>
                                                    <option value="0_100">5. çalışma şekli %0 %100</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6" id="modalPersonelCalismaSekliAdetRow" style="display: none;">
                                        <div class="row mb-2 align-items-center">
                                            <label for="modalPersonelCalismaSekliAdetTutar" class="col-sm-5 col-form-label col-form-label-sm">Adet Tutarı (TL)</label>
                                            <div class="col-sm-7">
                                                <input type="number" step="0.01" class="form-control form-control-sm" id="modalPersonelCalismaSekliAdetTutar" name="calisma_sekli_adet_tutar" placeholder="0.00">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6" id="modalPersonelOperatorKazancRow" style="display: none;">
                                        <div class="row mb-2 align-items-center">
                                            <label for="modalPersonelOperatorKazancTutar" class="col-sm-5 col-form-label col-form-label-sm">Operatör İş Başı Kazanç (TL)</label>
                                            <div class="col-sm-7">
                                                <input type="number" step="0.01" class="form-control form-control-sm" id="modalPersonelOperatorKazancTutar" name="operator_kazanc_tutar" placeholder="0.00">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row mb-2 align-items-center">
                                            <label for="modalPersonelFisFirma" class="col-sm-5 col-form-label col-form-label-sm">Fiş Firma Adı</label>
                                            <div class="col-sm-7">
                                                <input type="text" class="form-control form-control-sm" id="modalPersonelFisFirma" name="fis_firma">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row mb-2 align-items-center">
                                            <label for="modalPersonelFisTel" class="col-sm-5 col-form-label col-form-label-sm">Fiş Telefon</label>
                                            <div class="col-sm-7">
                                                <input type="text" class="form-control form-control-sm" id="modalPersonelFisTel" name="fis_tel">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        {{-- Boşluk veya başka bir alan eklenebilir --}}
                                    </div>
                                     <div class="col-md-12">
                                        <div class="row mb-2">
                                            <label for="modalPersonelYaziciIcerigi" class="col-sm-3 col-form-label col-form-label-sm">Yazıcı İçeriği</label>
                                            <div class="col-sm-9">
                                                <textarea class="form-control form-control-sm" id="modalPersonelYaziciIcerigi" name="yazici_icerigi" rows="2"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="row mb-2">
                                            <label for="modalPersonelFisAdres" class="col-sm-3 col-form-label col-form-label-sm">Fiş Adres</label>
                                            <div class="col-sm-9">
                                                <textarea class="form-control form-control-sm" id="modalPersonelFisAdres" name="fis_adres" rows="2"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Şifre Değişikliği Sekmesi --}}
                            <div class="tab-pane fade" id="sifre-degisikligi-pane" role="tabpanel" aria-labelledby="sifre-degisikligi-tab" tabindex="0">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="row mb-2 align-items-center">
                                            <label for="modalPersonelSifreTab" class="col-sm-5 col-form-label col-form-label-sm">Yeni Şifre</label>
                                            <div class="col-sm-7">
                                                <div class="input-group input-group-sm">
                                                    <input type="password" class="form-control form-control-sm" id="modalPersonelSifreTab" name="sifre" placeholder="Değiştirmek istemiyorsanız boş bırakın" autocomplete="new-password">
                                                    <button class="btn btn-outline-secondary toggle-password-btn" type="button" data-target="#modalPersonelSifreTab" aria-label="Şifreyi göster/gizle">
                                                        <i class="feather feather-eye"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row mb-2 align-items-center">
                                           <label for="modalPersonelSifreTekrarTab" class="col-sm-5 col-form-label col-form-label-sm">Yeni Şifre (Tekrar)</label>
                                           <div class="col-sm-7">
                                               <div class="input-group input-group-sm">
                                                   <input type="password" class="form-control form-control-sm" id="modalPersonelSifreTekrarTab" name="sifre_confirmation" autocomplete="new-password">
                                                   <button class="btn btn-outline-secondary toggle-password-btn" type="button" data-target="#modalPersonelSifreTekrarTab" aria-label="Şifreyi göster/gizle">
                                                       <i class="feather feather-eye"></i>
                                                   </button>
                                               </div>
                                           </div>
                                       </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Kapat</button>
                        <button type="submit" class="btn btn-primary btn-sm" id="personelGuncelleBtn">DEĞİŞİKLİKLERİ KAYDET</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!--! ================================================================ !-->
    <!--! [End] Personel Düzenleme Modal !-->
    <!--! ================================================================ !-->

    <!--! ================================================================ !-->
    <!--! [Start] Yeni Personel Ekleme Modal !-->
    <!--! ================================================================ !-->
    <div class="modal fade" id="yeniPersonelModal" tabindex="-1" aria-labelledby="yeniPersonelModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="yeniPersonelModalLabel">Yeni Personel Ekle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="yeniPersonelForm">
                    @csrf 
                    <div class="modal-body">
                        {{-- Hata mesajları için bir alan --}}
                        <div id="yeniPersonelHataMesajlari" class="alert alert-danger d-none" role="alert"></div>

                        {{-- Sekme Navigasyonu --}}
                        <ul class="nav nav-tabs nav-fill mb-3" id="yeniPersonelTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="yeni-temel-bilgiler-tab" data-bs-toggle="tab" data-bs-target="#yeni-temel-bilgiler-pane" type="button" role="tab" aria-controls="yeni-temel-bilgiler-pane" aria-selected="true">Temel Bilgiler</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="yeni-adres-bilgileri-tab" data-bs-toggle="tab" data-bs-target="#yeni-adres-bilgileri-pane" type="button" role="tab" aria-controls="yeni-adres-bilgileri-pane" aria-selected="false">Adres Bilgileri</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="yeni-yetki-parametreler-tab" data-bs-toggle="tab" data-bs-target="#yeni-yetki-parametreler-pane" type="button" role="tab" aria-controls="yeni-yetki-parametreler-pane" aria-selected="false">Yetki & Parametreler</button>
                            </li>
                        </ul>

                        {{-- Sekme İçerikleri --}}
                        <div class="tab-content" id="yeniPersonelTabsContent">
                            {{-- Temel Bilgiler Sekmesi --}}
                            <div class="tab-pane fade show active" id="yeni-temel-bilgiler-pane" role="tabpanel" aria-labelledby="yeni-temel-bilgiler-tab" tabindex="0">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="row mb-2 align-items-center">
                                            <label for="yeniModalPersonelAd" class="col-sm-4 col-form-label col-form-label-sm">Ad Soyad <span class="text-danger">*</span></label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control form-control-sm" id="yeniModalPersonelAd" name="ad" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row mb-2 align-items-center">
                                            <label for="yeniModalPersonelNick" class="col-sm-4 col-form-label col-form-label-sm">Kullanıcı Adı (Nick) <span class="text-danger">*</span></label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control form-control-sm" id="yeniModalPersonelNick" name="nick" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row mb-2 align-items-center">
                                            <label for="yeniModalPersonelPozisyon" class="col-sm-4 col-form-label col-form-label-sm">Pozisyon <span class="text-danger">*</span></label>
                                            <div class="col-sm-8">
                                                <select class="form-select form-select-sm" id="yeniModalPersonelPozisyon" name="poz_id" required>
                                                    <option value="">Seçiniz...</option>
                                                    {{-- Pozisyonlar AJAX ile veya Controller'dan direkt basılabilir --}}
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row mb-2 align-items-center">
                                            <label for="yeniModalPersonelAktif" class="col-sm-4 col-form-label col-form-label-sm">Durum <span class="text-danger">*</span></label>
                                            <div class="col-sm-8">
                                                <select class="form-select form-select-sm" id="yeniModalPersonelAktif" name="aktif" required>
                                                    <option value="1" selected>Mesaide (Aktif)</option>
                                                    <option value="0">Çalışmıyor (Pasif)</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row mb-2 align-items-center">
                                            <label for="yeniModalPersonelEmail" class="col-sm-4 col-form-label col-form-label-sm">E-posta</label>
                                            <div class="col-sm-8">
                                                <input type="email" class="form-control form-control-sm" id="yeniModalPersonelEmail" name="email">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row mb-2 align-items-center">
                                            <label for="yeniModalPersonelTwoFactorRequired" class="col-sm-4 col-form-label col-form-label-sm">2FA Zorunlu</label>
                                            <div class="col-sm-8">
                                                <input type="hidden" name="two_factor_required" value="0">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="yeniModalPersonelTwoFactorRequired" name="two_factor_required" value="1">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row mb-2 align-items-center">
                                            <label for="yeniModalPersonelTel1" class="col-sm-4 col-form-label col-form-label-sm">Telefon</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control form-control-sm" id="yeniModalPersonelTel1" name="tel1">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row mb-2 align-items-center">
                                            <label for="yeniModalPersonelTel2" class="col-sm-4 col-form-label col-form-label-sm">Telefon 2</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control form-control-sm" id="yeniModalPersonelTel2" name="tel2">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row mb-2 align-items-center">
                                            <label for="yeniModalPersonelIsBasiTarih" class="col-sm-4 col-form-label col-form-label-sm">İşe Başlama Tarihi</label>
                                            <div class="col-sm-8">
                                                <input type="date" class="form-control form-control-sm" id="yeniModalPersonelIsBasiTarih" name="is_basi_tarih">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row mb-2 align-items-center">
                                            <label for="yeniModalPersonelSifre" class="col-sm-4 col-form-label col-form-label-sm">Şifre <span class="text-danger">*</span></label>
                                            <div class="col-sm-8">
                                                <div class="input-group input-group-sm">
                                                    <input type="password" class="form-control form-control-sm" id="yeniModalPersonelSifre" name="sifre" required autocomplete="new-password">
                                                    <button class="btn btn-outline-secondary toggle-password-btn" type="button" data-target="#yeniModalPersonelSifre" aria-label="Şifreyi göster/gizle">
                                                        <i class="feather feather-eye"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                     <div class="col-md-6">
                                        <div class="row mb-2 align-items-center">
                                           <label for="yeniModalPersonelSifreTekrar" class="col-sm-4 col-form-label col-form-label-sm">Şifre (Tekrar) <span class="text-danger">*</span></label>
                                           <div class="col-sm-8">
                                               <div class="input-group input-group-sm">
                                                   <input type="password" class="form-control form-control-sm" id="yeniModalPersonelSifreTekrar" name="sifre_confirmation" required autocomplete="new-password">
                                                   <button class="btn btn-outline-secondary toggle-password-btn" type="button" data-target="#yeniModalPersonelSifreTekrar" aria-label="Şifreyi göster/gizle">
                                                       <i class="feather feather-eye"></i>
                                                   </button>
                                               </div>
                                           </div>
                                       </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Adres Bilgileri Sekmesi --}}
                            <div class="tab-pane fade" id="yeni-adres-bilgileri-pane" role="tabpanel" aria-labelledby="yeni-adres-bilgileri-tab" tabindex="0">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="row mb-2 align-items-center">
                                            <label for="yeniModalPersonelIl" class="col-sm-4 col-form-label col-form-label-sm">İl</label>
                                            <div class="col-sm-8">
                                                <select class="form-select form-select-sm" id="yeniModalPersonelIl" name="il_id">
                                                    <option value="">İl Seçiniz...</option>
                                                    {{-- İller AJAX ile veya Controller'dan direkt basılabilir --}}
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row mb-2 align-items-center">
                                            <label for="yeniModalPersonelIlce" class="col-sm-4 col-form-label col-form-label-sm">İlçe</label>
                                            <div class="col-sm-8">
                                                <select class="form-select form-select-sm" id="yeniModalPersonelIlce" name="ilce_id" disabled>
                                                    <option value="">Önce İl Seçiniz...</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="row mb-2">
                                            <label for="yeniModalPersonelAdres" class="col-sm-2 col-form-label col-form-label-sm">Adres</label>
                                            <div class="col-sm-10">
                                                <textarea class="form-control form-control-sm" id="yeniModalPersonelAdres" name="adres" rows="3"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Yetki & Parametreler Sekmesi --}}
                            <div class="tab-pane fade" id="yeni-yetki-parametreler-pane" role="tabpanel" aria-labelledby="yeni-yetki-parametreler-tab" tabindex="0">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="row mb-2 align-items-center">
                                            <label for="yeniModalPersonelServisFis" class="col-sm-5 col-form-label col-form-label-sm">Servis Fişi Kullanabilir</label>
                                            <div class="col-sm-7">
                                                <select class="form-select form-select-sm" id="yeniModalPersonelServisFis" name="servis_fis_kullanabilir">
                                                    <option value="1" selected>Evet</option>
                                                    <option value="0">Hayır</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row mb-2 align-items-center">
                                            <label for="yeniModalPersonelMesaiBasladimi" class="col-sm-5 col-form-label col-form-label-sm">Mesai Başladı mı?</label>
                                            <div class="col-sm-7">
                                                <select class="form-select form-select-sm" id="yeniModalPersonelMesaiBasladimi" name="mesai_basladimi">
                                                    <option value="1" selected>Evet</option>
                                                    <option value="0">Hayır</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row mb-2 align-items-center">
                                            <label for="yeniModalPersonelEFis" class="col-sm-5 col-form-label col-form-label-sm">E-Fiş Verebilir</label>
                                            <div class="col-sm-7">
                                                <select class="form-select form-select-sm" id="yeniModalPersonelEFis" name="e_fis_verebilir">
                                                    <option value="1" selected>Evet</option>
                                                    <option value="0">Hayır</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6" id="yeniModalPersonelCalismaSekliRow" style="display: none;">
                                        <div class="row mb-2 align-items-center">
                                            <label for="yeniModalPersonelCalismaSekli" class="col-sm-5 col-form-label col-form-label-sm">Çalışma Şekli</label>
                                            <div class="col-sm-7">
                                                <select class="form-select form-select-sm" id="yeniModalPersonelCalismaSekli" name="calisma_sekli_type">
                                                    <option value="">Seçiniz...</option>
                                                    <option value="50_50">1. çalışma şekli %50 %50</option>
                                                    <option value="60_40">2. çalışma şekli %60 %40</option>
                                                    <option value="55_45">3. çalışma şekli %55 %45</option>
                                                    <option value="65_35">4. çalışma şekli %65 %35</option>
                                                    <option value="0_100">5. çalışma şekli %0 %100</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6" id="yeniModalPersonelCalismaSekliAdetRow" style="display: none;">
                                        <div class="row mb-2 align-items-center">
                                            <label for="yeniModalPersonelCalismaSekliAdetTutar" class="col-sm-5 col-form-label col-form-label-sm">Adet Tutarı (TL)</label>
                                            <div class="col-sm-7">
                                                <input type="number" step="0.01" class="form-control form-control-sm" id="yeniModalPersonelCalismaSekliAdetTutar" name="calisma_sekli_adet_tutar" placeholder="0.00">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6" id="yeniModalPersonelOperatorKazancRow" style="display: none;">
                                        <div class="row mb-2 align-items-center">
                                            <label for="yeniModalPersonelOperatorKazancTutar" class="col-sm-5 col-form-label col-form-label-sm">Operatör İş Başı Kazanç (TL)</label>
                                            <div class="col-sm-7">
                                                <input type="number" step="0.01" class="form-control form-control-sm" id="yeniModalPersonelOperatorKazancTutar" name="operator_kazanc_tutar" placeholder="0.00">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row mb-2 align-items-center">
                                            <label for="yeniModalPersonelFisFirma" class="col-sm-5 col-form-label col-form-label-sm">Fiş Firma Adı</label>
                                            <div class="col-sm-7">
                                                <input type="text" class="form-control form-control-sm" id="yeniModalPersonelFisFirma" name="fis_firma">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row mb-2 align-items-center">
                                            <label for="yeniModalPersonelFisTel" class="col-sm-5 col-form-label col-form-label-sm">Fiş Telefon</label>
                                            <div class="col-sm-7">
                                                <input type="text" class="form-control form-control-sm" id="yeniModalPersonelFisTel" name="fis_tel">
                                            </div>
                                        </div>
                                    </div>
                                     <div class="col-md-12">
                                        <div class="row mb-2">
                                            <label for="yeniModalPersonelYaziciIcerigi" class="col-sm-3 col-form-label col-form-label-sm">Yazıcı İçeriği</label>
                                            <div class="col-sm-9">
                                                <textarea class="form-control form-control-sm" id="yeniModalPersonelYaziciIcerigi" name="yazici_icerigi" rows="2"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="row mb-2">
                                            <label for="yeniModalPersonelFisAdres" class="col-sm-3 col-form-label col-form-label-sm">Fiş Adres</label>
                                            <div class="col-sm-9">
                                                <textarea class="form-control form-control-sm" id="yeniModalPersonelFisAdres" name="fis_adres" rows="2"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Kapat</button>
                        <button type="submit" class="btn btn-primary btn-sm" id="yeniPersonelKaydetBtn">PERSONELİ KAYDET</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!--! ================================================================ !-->
    <!--! [End] Yeni Personel Ekleme Modal !-->
    <!--! ================================================================ !-->
@endsection

@push('page_specific_vendor_js')
<script src="{{ asset('crm_assets/vendors/js/dataTables.min.js') }}"></script>
<script src="{{ asset('crm_assets/vendors/js/dataTables.bs5.min.js') }}"></script>
@endpush

@push('page_specific_main_scripts')
<script src="{{ asset('crm_assets/js/helpers.js') }}"></script> <!-- Ortak Helperlar -->
<script>
    $(document).ready(function() {
        var personelTable = $('#personelListTable').DataTable({
            "language": {
                "emptyTable": "Kayıt bulunamadı.",
                "paginate": { "previous": "Önceki Sayfa", "next": "Sonraki Sayfa" }
            },
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "/personeller",
                "type": "GET",
                "data": function (d) {
                    d.search = d.search || {};
                    d.search.value = $('#personelSearchInput').val() || '';
                }
            },
            "columns": [
                { "data": "id_html" },
                { "data": "ad" },
                { "data": "pozisyon" },
                { "data": "uyelik" },
                { "data": "durum" }
            ],
            "paging": true,
            "pageLength": 50,
            "lengthChange": false,
            "info": false,
            "searching": false,
            "autoWidth": false,
            "ordering": false,
            "dom": 'rt<"row"<"col-12 d-flex justify-content-center"p>>',
            "createdRow": function(row, data) {
                if (data && data.personel_id) {
                    $(row).addClass('clickable-row').attr('data-personel-id', data.personel_id).css('cursor', 'pointer');
                }
            },
            "drawCallback": function(settings){
                var api = this.api();
                var info = api.page.info();
                var $paginate = $('#personelListTable_wrapper').find('.dataTables_paginate');
                if (info.pages <= 1) { $paginate.hide(); } else { $paginate.show(); }
                if (typeof info.recordsTotal !== 'undefined') {
                    $('#personelTotalCount').text(info.recordsTotal);
                }
            }
        });

        var personelSearchTimer = null;
        $('#personelSearchInput').on('input', function() {
            clearTimeout(personelSearchTimer);
            personelSearchTimer = setTimeout(function() {
                personelTable.ajax.reload();
            }, 300);
        });

        // Alt metin artık Blade ile basılıyor; ekstra JS enjektesine gerek yok

        var personelDuzenleModal = new bootstrap.Modal(document.getElementById('personelDuzenleModal'));
        var yeniPersonelModal = new bootstrap.Modal(document.getElementById('yeniPersonelModal'));
        var globalPozisyonlar = []; 
        var globalIller = []; 
        var tsrnTeknisyenPozisyonId = 1077;
        var operatorPozisyonId = 1073;

        // window.crmData'dan verileri al (app.blade.php'de tanımlanmış olmalı)
        if (window.crmData) {
            globalPozisyonlar = window.crmData.pozisyonlar || []; 
            globalIller = window.crmData.iller || [];
            // Eğer personel listesi de global olarak yönetilecekse:
            // var globalPersoneller = window.crmData.personeller || []; 
        }


        // ID sütunundaki linke tıklanınca satır click handler'ı tetiklenmesin
        $('#personelListTable tbody').on('click', 'a.profile-link', function(e){
            e.stopPropagation();
        });

        // Şifre göster/gizle
        $(document).on('click', '.toggle-password-btn', function() {
            var targetSelector = $(this).data('target');
            var $input = $(targetSelector);
            if (!$input.length) return;
            var isPassword = $input.attr('type') === 'password';
            $input.attr('type', isPassword ? 'text' : 'password');
            var $icon = $(this).find('i');
            if ($icon.length) {
                $icon.toggleClass('feather-eye', !isPassword);
                $icon.toggleClass('feather-eye-off', isPassword);
            }
        });

        function toggleCalismaSekliRow(pozId, isEdit) {
            var isTeknisyen = String(pozId) === String(tsrnTeknisyenPozisyonId);
            if (isEdit) {
                if (isTeknisyen) {
                    $('#modalPersonelCalismaSekliRow').show();
                    toggleCalismaSekliAdetRow(true, true);
                } else {
                    $('#modalPersonelCalismaSekliRow').hide();
                    $('#modalPersonelCalismaSekli').val('');
                    $('#modalPersonelCalismaSekliAdetTutar').val('');
                    $('#modalPersonelCalismaSekliAdetRow').hide();
                }
            } else {
                if (isTeknisyen) {
                    $('#yeniModalPersonelCalismaSekliRow').show();
                    toggleCalismaSekliAdetRow(false, true);
                } else {
                    $('#yeniModalPersonelCalismaSekliRow').hide();
                    $('#yeniModalPersonelCalismaSekli').val('');
                    $('#yeniModalPersonelCalismaSekliAdetTutar').val('');
                    $('#yeniModalPersonelCalismaSekliAdetRow').hide();
                }
            }
        }

        function toggleOperatorKazancRow(pozId, isEdit) {
            var isOperator = String(pozId) === String(operatorPozisyonId);
            if (isEdit) {
                if (isOperator) {
                    $('#modalPersonelOperatorKazancRow').show();
                } else {
                    $('#modalPersonelOperatorKazancRow').hide();
                    $('#modalPersonelOperatorKazancTutar').val('');
                }
            } else {
                if (isOperator) {
                    $('#yeniModalPersonelOperatorKazancRow').show();
                } else {
                    $('#yeniModalPersonelOperatorKazancRow').hide();
                    $('#yeniModalPersonelOperatorKazancTutar').val('');
                }
            }
        }

        function toggleCalismaSekliAdetRow(isEdit, respectSelect) {
            var $select = isEdit ? $('#modalPersonelCalismaSekli') : $('#yeniModalPersonelCalismaSekli');
            var $row = isEdit ? $('#modalPersonelCalismaSekliAdetRow') : $('#yeniModalPersonelCalismaSekliAdetRow');
            var $input = isEdit ? $('#modalPersonelCalismaSekliAdetTutar') : $('#yeniModalPersonelCalismaSekliAdetTutar');
            var val = $select.val();
            if (val === 'adetli') {
                $row.show();
                $input.prop('disabled', false);
            } else {
                if (respectSelect) {
                    $row.hide();
                    $input.val('').prop('disabled', true);
                }
            }
        }

        $('#personelListTable tbody').on('click', 'tr.clickable-row', function(e) {
            // Eğer tıklama bir link üzerinde gerçekleşmişse (özellikle ID linki), modal açmayı durdur
            if ($(e.target).closest('a.profile-link').length) {
                return; // profil sayfasına normal şekilde gitsin
            }
            var personelId = $(this).data('personel-id');
            if (!personelId) return;

            $.ajax({
                url: `/personeller/${personelId}/get-detay`,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    var personel = data.personel; 
                    var ilceler = data.ilceler; 

                    $('#modalPersonelId').text(personel.id);
                    $('#modalPersonelKayitTarihi').text(personel.kayit_tarihi_formatted || 'N/A');
                    $('#editPersonelId').val(personel.id);
                    
                    $('#modalPersonelAd').val(personel.ad);
                    $('#modalPersonelNick').val(personel.nick);
                    $('#modalPersonelTel1').val(personel.tel1);
                    $('#modalPersonelTel2').val(personel.tel2);
                    $('#modalPersonelEmail').val(personel.email);
                    $('#modalPersonelIsBasiTarih').val(personel.is_basi_tarih); 
                    
                    var $modalPersonelPozisyon = $('#modalPersonelPozisyon');
                    $modalPersonelPozisyon.empty().append('<option value="">Seçiniz...</option>');
                    var pozisyonKaynak = (globalPozisyonlar && globalPozisyonlar.length > 0) ? globalPozisyonlar : (data.pozisyonlar || []);
                    $.each(pozisyonKaynak, function(index, pozisyon) {
                        $modalPersonelPozisyon.append($('<option>', { value: String(pozisyon.id), text: pozisyon.ad }));
                    });
                    $modalPersonelPozisyon.val(String(personel.poz_id));
                    toggleCalismaSekliRow(personel.poz_id, true);
                    toggleOperatorKazancRow(personel.poz_id, true);

                    $('#modalPersonelAktif').val(personel.aktif);
                    $('#modalPersonelTwoFactorRequired').prop('checked', String(personel.two_factor_required) === '1');

                    var $modalPersonelIl = $('#modalPersonelIl');
                    $modalPersonelIl.empty().append('<option value="">İl Seçiniz...</option>');
                    if (globalIller && globalIller.length > 0) {
                        $.each(globalIller, function(index, il) {
                            $modalPersonelIl.append($('<option>', { value: il.id, text: il.ad }));
                        });
                    }
                    $modalPersonelIl.val(personel.il_id);

                    var $modalPersonelIlce = $('#modalPersonelIlce');
                    $modalPersonelIlce.empty().append('<option value="">Önce İl Seçiniz...</option>').prop('disabled', !personel.il_id);
                    if (personel.il_id && ilceler && ilceler.length > 0) { 
                        $.each(ilceler, function(index, ilce) {
                            $modalPersonelIlce.append($('<option>', { value: ilce.id, text: ilce.ad }));
                        });
                        $modalPersonelIlce.val(personel.ilce_id);
                        $modalPersonelIlce.prop('disabled', false); 
                    } else if (personel.il_id) {
                        $.ajax({
                            url: '/ilceler/' + personel.il_id,
                            type: 'GET',
                            success: function(ilceData) {
                                $modalPersonelIlce.empty().append('<option value="">İlçe Seçiniz...</option>');
                                if(ilceData && ilceData.length > 0) {
                                    $.each(ilceData, function(i, ilce) {
                                        $modalPersonelIlce.append($('<option>', { value: ilce.id, text: ilce.ad }));
                                    });
                                    $modalPersonelIlce.val(personel.ilce_id).prop('disabled', false);
                                } else {
                                    $modalPersonelIlce.prop('disabled', true);
                                }
                            }, error: function() { $modalPersonelIlce.prop('disabled', true); }
                        });
                    } else {
                        $modalPersonelIlce.prop('disabled', true);
                    }

                    $('#modalPersonelAdres').val(personel.adres);
                    $('#modalPersonelServisFis').val(personel.servis_fis_kullanabilir);
                    $('#modalPersonelMesaiBasladimi').val(personel.mesai_basladimi);
                    $('#modalPersonelEFis').val(personel.e_fis_verebilir);
                    var calismaSekliType = personel.calisma_sekli_type || '';
                    if (String(calismaSekliType).toLowerCase() === 'adetli') {
                        calismaSekliType = '0_100';
                    }
                    $('#modalPersonelCalismaSekli').val(calismaSekliType);
                    $('#modalPersonelCalismaSekliAdetTutar').val(personel.calisma_sekli_adet_tutar || '');
                    toggleCalismaSekliAdetRow(true, true);
                    $('#modalPersonelOperatorKazancTutar').val(personel.operator_kazanc_tutar || '');
                    $('#modalPersonelYaziciIcerigi').val(personel.yazici_icerigi);
                    $('#modalPersonelFisFirma').val(personel.fis_firma);
                    $('#modalPersonelFisTel').val(personel.fis_tel);
                    $('#modalPersonelFisAdres').val(personel.fis_adres);
                    
                    $('#modalPersonelSifreTab').val(''); // ID düzeltildi
                    $('#modalPersonelSifreTekrarTab').val(''); // ID düzeltildi

                    personelDuzenleModal.show();
                },
                error: function(xhr, status, error) {
                    console.error("Personel detayları alınırken hata: ", error);
                    Swal.fire('Hata!', 'Personel bilgileri yüklenirken bir sorun oluştu.', 'error');
                }
            });
        });

        $('#modalPersonelIl').on('change', function() {
            var ilId = $(this).val();
            var $modalPersonelIlce = $('#modalPersonelIlce');
            $modalPersonelIlce.empty().append('<option value="">Yükleniyor...</option>').prop('disabled', true);

            if (ilId) {
                $.ajax({
                    url: '/ilceler/' + ilId, 
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        $modalPersonelIlce.empty().append('<option value="">İlçe Seçiniz...</option>');
                        if (data && data.length > 0) {
                            $.each(data, function(index, ilce) {
                                $modalPersonelIlce.append($('<option>', { value: ilce.id, text: ilce.ad }));
                            });
                            $modalPersonelIlce.prop('disabled', false);
                        } else {
                            $modalPersonelIlce.append('<option value="">İlçe bulunamadı</option>').prop('disabled', true);
                        }
                    },
                    error: function() {
                        $modalPersonelIlce.empty().append('<option value="">Hata!</option>').prop('disabled', true);
                        Swal.fire('Hata!', 'İlçeler yüklenirken bir sorun oluştu.', 'error');
                    }
                });
            } else {
                $modalPersonelIlce.empty().append('<option value="">Önce İl Seçiniz...</option>').prop('disabled', true);
            }
        });

        $('#personelDuzenleForm').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var personelId = $('#editPersonelId').val();
            var url = `/personeller/${personelId}`; 
            var formData = form.serialize(); 
            var submitButton = form.find('button[type="submit"]');
            var originalButtonText = submitButton.html();

            submitButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Kaydediliyor...');
            form.find('.is-invalid').removeClass('is-invalid');
            form.find('.invalid-feedback').remove();

            $.ajax({
                type: 'POST', 
                url: url,
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        personelDuzenleModal.hide();
                        Swal.fire(
                            'Başarılı!',
                            response.message,
                            'success'
                        );

                        var updatedPersonel = response.personel_data.personel;
                        var row = personelTable.row($(`tr[data-personel-id="${personelId}"]`));
                        if (row.length) {
                            var rowData = row.data();
                            rowData[1] = updatedPersonel.ad; 
                            
                            var pozisyonAdi = 'N/A';
                            var poz = null;
                            if (updatedPersonel.poz_id) {
                                if (response.personel_data && response.personel_data.pozisyonlar) { // response'dan gelen pozisyon listesini öncelikli kontrol et
                                     poz = response.personel_data.pozisyonlar.find(p => p.id == updatedPersonel.poz_id);
                                } else if (globalPozisyonlar) { // Sonra global listeyi kontrol et
                                     poz = globalPozisyonlar.find(p => p.id == updatedPersonel.poz_id);
                                }
                                if (poz) pozisyonAdi = poz.ad;
                            }
                            rowData[2] = pozisyonAdi;
                            rowData[3] = updatedPersonel.aktif == 1 ? '<span class="badge bg-soft-success text-success">Aktif</span>' : '<span class="badge bg-soft-danger text-danger">Pasif</span>'; 
                            rowData[4] = updatedPersonel.mesai_basladimi == 1 ? '<span class="badge bg-soft-success text-success">Mesaide</span>' : '<span class="badge bg-soft-danger text-danger">Çalışmıyor</span>'; 
                            row.data(rowData).draw(false); 
                        }
                    } else {
                        if (response.errors) {
                            $.each(response.errors, function(key, value) {
                                var input = form.find(`[name="${key}"]`);
                                input.addClass('is-invalid');
                                input.after(`<div class="invalid-feedback">${value[0]}</div>`);
                            });
                            Swal.fire('Doğrulama Hatası', 'Lütfen formdaki hataları düzeltin.', 'error');
                        } else {
                            Swal.fire('Hata!', response.message || 'Bir sorun oluştu.', 'error');
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Personel güncelleme hatası: ", xhr.responseText);
                    var errorMsg = 'Personel güncellenirken bir sunucu hatası oluştu.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    Swal.fire('Hata!', errorMsg, 'error');
                },
                complete: function() {
                    submitButton.prop('disabled', false).html(originalButtonText);
                }
            });
        });

        function getTeknisyenYaziciSablon() {
            return `[FIRMAADI]
TEL : [TEL]
--------------------------------
BEYAZ ESYA - KLIMA - KOMBI - TV
================================
 - MUSTERI BILGISI - 
--------------------------------
[MUSTERIBILGILERI]
================================
 - CIHAZ BILGISI - 
--------------------------------
[CIHAZBILGILERI]
================================
 - YAPILAN ISLEMLER - 
--------------------------------
[YAPILANISLEMLER]
================================
 - KASA HAREKETLERI - 
--------------------------------
[KASAHAREKETLERI]
================================
 - TEKNISYEN ADI VE IMZASI - 
--------------------------------
[TEKNISYENADI]
TARIH : [TARIHSAAT]


================================
BU FIS FATURA YERINE GECMEZ`;
        }

        function applyYaziciSablonIfTeknisyen(pozId) {
            var isTeknisyen = String(pozId) === String(tsrnTeknisyenPozisyonId);
            var $textarea = $('#yeniModalPersonelYaziciIcerigi');
            if (!$textarea.length) {
                return;
            }
            if (isTeknisyen) {
                if (!$textarea.val().trim()) {
                    $textarea.val(getTeknisyenYaziciSablon());
                }
            } else {
                var mevcut = $textarea.val().trim();
                if (mevcut === getTeknisyenYaziciSablon().trim()) {
                    $textarea.val('');
                }
            }
        }

        $(document.getElementById('yeniPersonelModal')).on('show.bs.modal', function () {
            $('#yeniPersonelForm')[0].reset();
            $('#yeniPersonelHataMesajlari').addClass('d-none').html('');
            $('#yeniPersonelForm').find('.is-invalid').removeClass('is-invalid');
            $('#yeniPersonelForm').find('.invalid-feedback').remove();

            var $yeniModalPersonelPozisyon = $('#yeniModalPersonelPozisyon');
            $yeniModalPersonelPozisyon.empty().append('<option value="">Seçiniz...</option>');
            if (globalPozisyonlar && globalPozisyonlar.length > 0) {
                $.each(globalPozisyonlar, function(index, pozisyon) {
                    $yeniModalPersonelPozisyon.append($('<option>', { value: pozisyon.id, text: pozisyon.ad }));
                });
            } else {
                // window.crmData dolu değilse veya pozisyonlar yoksa, bir uyarı verilebilir
                // veya acil durum AJAX çağrısı burada tutulabilir.
                // İdeal senaryo, window.crmData'nın her zaman dolu olmasıdır.
                console.warn("globalPozisyonlar boş. Lütfen app.blade.php'de window.crmData.pozisyonlar'ın dolu olduğundan emin olun.");
                 $.ajax({ // Fallback
                    url: '/personel-form-data', 
                    type: 'GET',
                    async: false, 
                    dataType: 'json',
                    success: function(formData) {
                        globalPozisyonlar = formData.pozisyonlar || [];
                        globalIller = formData.iller || []; 
                        $.each(globalPozisyonlar, function(index, pozisyon) {
                            $yeniModalPersonelPozisyon.append($('<option>', { value: pozisyon.id, text: pozisyon.ad }));
                        });
                        // Fallback'ten gelen illeri de yükle
                        var $yeniModalPersonelIlFallback = $('#yeniModalPersonelIl');
                        $yeniModalPersonelIlFallback.empty().append('<option value="">İl Seçiniz...</option>');
                         $.each(globalIller, function(index, il) {
                            $yeniModalPersonelIlFallback.append($('<option>', { value: il.id, text: il.ad }));
                        });
                    },
                    error: function() {
                        console.error('Yeni personel modalı için fallback form verileri (pozisyon/il) yüklenemedi.');
                    }
                });
            }

            var $yeniModalPersonelIl = $('#yeniModalPersonelIl');
            $yeniModalPersonelIl.empty().append('<option value="">İl Seçiniz...</option>');
            if (globalIller && globalIller.length > 0) {
                $.each(globalIller, function(index, il) {
                    $yeniModalPersonelIl.append($('<option>', { value: il.id, text: il.ad }));
                });
            } else {
                 console.warn("globalIller boş. Lütfen app.blade.php'de window.crmData.iller'in dolu olduğundan emin olun.");
                 // Yukarıdaki fallback AJAX'ında iller zaten set edilmiş olabilir.
            }
            
            $('#yeniModalPersonelIlce').empty().append('<option value="">Önce İl Seçiniz...</option>').prop('disabled', true);
            $('#yeniModalPersonelAktif').val('1');
            
            var today = new Date();
            var dd = String(today.getDate()).padStart(2, '0');
            var mm = String(today.getMonth() + 1).padStart(2, '0'); 
            var yyyy = today.getFullYear();
            var todayFormatted = yyyy + '-' + mm + '-' + dd;
            $('#yeniModalPersonelIsBasiTarih').val(todayFormatted);

            $('#yeniModalPersonelServisFis').val('1');
            $('#yeniModalPersonelMesaiBasladimi').val('1');
            $('#yeniModalPersonelEFis').val('1');
            toggleCalismaSekliRow($('#yeniModalPersonelPozisyon').val(), false);
            toggleOperatorKazancRow($('#yeniModalPersonelPozisyon').val(), false);
            $('#yeniModalPersonelCalismaSekliAdetTutar').val('');
            $('#yeniModalPersonelOperatorKazancTutar').val('');
            applyYaziciSablonIfTeknisyen($('#yeniModalPersonelPozisyon').val());
        });

        $('#yeniModalPersonelPozisyon').on('change', function() {
            toggleCalismaSekliRow($(this).val(), false);
            toggleOperatorKazancRow($(this).val(), false);
            applyYaziciSablonIfTeknisyen($(this).val());
        });

        $('#modalPersonelPozisyon').on('change', function() {
            toggleCalismaSekliRow($(this).val(), true);
            toggleOperatorKazancRow($(this).val(), true);
        });

        $('#yeniModalPersonelCalismaSekli').on('change', function() {
            toggleCalismaSekliAdetRow(false, true);
        });

        $('#modalPersonelCalismaSekli').on('change', function() {
            toggleCalismaSekliAdetRow(true, true);
        });

        $('#yeniModalPersonelIl').on('change', function() {
            var ilId = $(this).val();
            var $yeniModalPersonelIlce = $('#yeniModalPersonelIlce');
            $yeniModalPersonelIlce.empty().append('<option value="">Yükleniyor...</option>').prop('disabled', true);
            if (ilId) {
                $.ajax({
                    url: '/ilceler/' + ilId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        $yeniModalPersonelIlce.empty().append('<option value="">İlçe Seçiniz...</option>');
                        if (data && data.length > 0) {
                            $.each(data, function(index, ilce) {
                                $yeniModalPersonelIlce.append($('<option>', { value: ilce.id, text: ilce.ad }));
                            });
                            $yeniModalPersonelIlce.prop('disabled', false);
                        } else {
                            $yeniModalPersonelIlce.append('<option value="">İlçe bulunamadı</option>').prop('disabled', true);
                        }
                    },
                    error: function() {
                        $yeniModalPersonelIlce.empty().append('<option value="">Hata!</option>').prop('disabled', true);
                    }
                });
            } else {
                $yeniModalPersonelIlce.empty().append('<option value="">Önce İl Seçiniz...</option>').prop('disabled', true);
            }
        });

        $('#yeniPersonelForm').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var url = "{{ route('personeller.store') }}"; 
            var formData = form.serialize();
            var submitButton = form.find('button[type="submit"]');
            var originalButtonText = submitButton.html();
            var hataMesajlariDiv = $('#yeniPersonelHataMesajlari');

            submitButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Kaydediliyor...');
            hataMesajlariDiv.addClass('d-none').html('');
            form.find('.is-invalid').removeClass('is-invalid');
            form.find('.invalid-feedback').remove();

            $.ajax({
                type: 'POST',
                url: url,
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        yeniPersonelModal.hide();
                        Swal.fire('Başarılı!', response.message, 'success');
                        
                        personelTable.ajax.reload(null, false);

                        // İsteğe bağlı: window.crmData.personeller listesini de güncelle
                        // if (window.crmData && window.crmData.personeller) {
                        //    window.crmData.personeller.push(yeniPersonel); // Veya daha uygun bir güncelleme
                        // }

                    } else {
                        if (response.errors) {
                            var errorHtml = '<ul>';
                            $.each(response.errors, function(key, value) {
                                errorHtml += '<li>' + value[0] + '</li>';
                                var input = form.find(`[name="${key}"]`);
                                input.addClass('is-invalid');
                            });
                            errorHtml += '</ul>';
                            hataMesajlariDiv.html(errorHtml).removeClass('d-none');
                            Swal.fire('Doğrulama Hatası', 'Lütfen formdaki hataları düzeltin.', 'error');
                        } else {
                            hataMesajlariDiv.html('<ul><li>' + (response.message || 'Bilinmeyen bir hata oluştu.') + '</li></ul>').removeClass('d-none');
                            Swal.fire('Hata!', response.message || 'Bir sorun oluştu.', 'error');
                        }
                    }
                },
                error: function(xhr) {
                    var errorMsg = 'Yeni personel kaydedilirken bir sunucu hatası oluştu.';
                    var responseJson = xhr.responseJSON;
                    if (responseJson && responseJson.message) {
                        errorMsg = responseJson.message;
                    }
                    var errorHtml = '';
                    if (responseJson && responseJson.errors) {
                         errorHtml = '<ul>';
                        $.each(responseJson.errors, function(key, value) {
                            errorHtml += '<li>' + value[0] + '</li>';
                            var input = form.find(`[name="${key}"]`);
                            input.addClass('is-invalid');
                        });
                        errorHtml += '</ul>';
                        hataMesajlariDiv.html(errorHtml).removeClass('d-none');
                    } else if (errorMsg && hataMesajlariDiv.length) { // hataMesajlariDiv var mı diye kontrol et
                         hataMesajlariDiv.html('<ul><li>' + errorMsg + '</li></ul>').removeClass('d-none');
                    }
                    Swal.fire('Hata!', errorMsg, 'error');
                },
                complete: function() {
                    submitButton.prop('disabled', false).html(originalButtonText);
                }
            });
        });

        $('#modalPersonelTel1, #modalPersonelTel2').on('input', function() {
            if (typeof formatPhoneNumber === 'function') {
                formatPhoneNumber(this); 
            }
        });

        $('#yeniModalPersonelTel1, #yeniModalPersonelTel2').on('input', function() {
             if (typeof formatPhoneNumber === 'function') {
                formatPhoneNumber(this); 
            }
        });

    });
</script>
@endpush 
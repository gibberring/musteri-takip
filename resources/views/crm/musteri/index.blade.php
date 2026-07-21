@extends('layouts.app')

@section('title', 'Müşteri Listesi')

@push('page_specific_css')
    <link rel="stylesheet" type="text/css" href="{{ asset('crm_assets/vendors/css/dataTables.bs5.min.css') }}">
    <style>
        /* Yeni Müşteri Modalı için stiller */
        .form-check-label { margin-left: 0.25rem; }
        #yeniMusteriModal #vergiAlanlari { display: none; } /* Başlangıçta gizli */

        /* === ORTAK MODAL STİLLERİ (MusteriDetayModal için) === */
        #musteriDetayModal .modal-header {
            background-color: #3454d1;
            color: white;
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }
        #musteriDetayModal .modal-header .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%);
        }
        #musteriDetayModal .modal-header .modal-title,
        #musteriDetayModal .modal-header .modal-title span {
            color: white !important;
        }
        #musteriDetayModal .form-control-sm,
        #musteriDetayModal .form-select-sm,
        #musteriDetayModal .modal-input-compact { /* .modal-input-compact da dahil edildi */
            padding: 0.2rem 0.4rem;
            height: auto; 
            line-height: 1.5;
            font-size: 0.8rem; 
        }
        #musteriDetayModal .form-label-sm,
        #musteriDetayModal .form-label.small {
            font-size: 0.75rem; 
            margin-bottom: 0.1rem; 
            padding-top: calc(0.2rem + 1px); 
            padding-bottom: calc(0.2rem + 1px);
        }
         #musteriDetayModal .input-group-sm > .btn {
             padding-top: 0.2rem;
             padding-bottom: 0.2rem; 
        }
         /* Müşteri Detay Modalı - Düzenleme Modu için ek boşluk ayarları (opsiyonel) */
         #musteriDetayModal p {
             margin-bottom: 0.5rem; 
         }
         #musteriDetayModal .row.g-2 {
             --bs-gutter-y: 0.25rem; 
         }

        /* === ORTAK MODAL STİLLERİ (YeniMusteriModal için) === */
        #yeniMusteriModal .modal-header {
            background-color: #3454d1;
            color: white;
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }
        #yeniMusteriModal .modal-header .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%);
        }
        #yeniMusteriModal .modal-header .modal-title {
            color: white !important;
        }
        #yeniMusteriModal .form-control, /* .form-control-sm yerine .form-control kullanılmış olabilir */
        #yeniMusteriModal .form-select {  /* .form-select-sm yerine .form-select kullanılmış olabilir */
            padding: 0.2rem 0.4rem;
            height: auto; 
            line-height: 1.5;
            font-size: 0.8rem; 
        }
        #yeniMusteriModal .form-label {
            font-size: 0.75rem; 
            margin-bottom: 0.1rem; 
            padding-top: calc(0.2rem + 1px); 
            padding-bottom: calc(0.2rem + 1px);
        }

        /* DataTables font ayarı */
        #musteriListTable_wrapper table th {
            font-family: 'Inter', sans-serif;
            font-size: 13px;
            font-weight: 600; /* Başlıklar kalın */
            line-height: 1.2;
        }
        #musteriListTable_wrapper table td {
            font-family: 'Inter', sans-serif;
            font-size: 13px;
            font-weight: 400; /* Veri hücreleri normal kalınlıkta */
            line-height: 1.2;
        }
        #musteriListTable tbody td {
            padding-top: 0.2rem;
            padding-bottom: 0.2rem;
        }
        .page-header-title::after,
        .page-header-title h5::after {
            content: none !important;
        }
        .page-header-title::before,
        .page-header-title h5::before {
            content: none !important;
        }
        .page-header-title {
            border-right: none !important;
            padding-right: 0 !important;
            margin-right: 0 !important;
        }
        .page-header h5 {
            border-right: none !important;
            padding-right: 0 !important;
            margin-right: 0 !important;
        }
        @media (max-width: 575.98px) {
            .page-header {
                flex-wrap: wrap;
            }
            .page-header-left,
            .page-header-right {
                width: 100%;
            }
            .page-header-right {
                width: 100%;
                margin-top: 0.5rem;
                display: block !important;
                visibility: visible !important;
            }
            .page-header-right-items,
            .page-header-right-items-wrapper {
                display: block !important;
            }
            .page-header-right .btn {
                width: auto;
                margin: 0 auto;
                display: inline-flex;
            }
            .page-header-left {
                justify-content: center;
                text-align: center;
            }
            .page-header-right-items {
                text-align: center;
            }
        }
    </style>
    <style>
        /* FOUC sorununu çözmek için başlangıçta ana içeriği gizle */
        #mainContentWrapper { display: none; }
    </style>
@endpush

@section('content')
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Müşteri Listesi</h5>
            </div>
        </div>
        <div class="d-flex d-sm-none align-items-center justify-content-end ms-auto">
            <button type="button" class="btn btn-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#yeniMusteriModal">
                <i class="feather-plus me-1"></i>
                <span>YENİ EKLE</span>
            </button>
        </div>
        <div class="page-header-right ms-auto d-none d-sm-flex">
            <div class="page-header-right-items">
                <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#yeniMusteriModal">
                        <i class="feather-plus me-2"></i>
                        <span>YENİ MÜŞTERİ EKLE</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- End Page Header -->

    <!-- Main Content -->
    <div id="mainContentWrapper" class="main-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="card stretch stretch-full">
                    <div class="card-body p-0">
                        <div class="px-3 pt-3 d-flex justify-content-end">
                            <input type="text" id="musteriSearchInput" class="form-control form-control-sm w-auto" style="max-width: 220px;" placeholder="Ara...">
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover" id="musteriListTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>AD / FİRMA ADI</th>
                                        <th>TELEFON</th>
                                        <th>ADRES</th>
                                        <th>TİP</th>
                                        <th>KAYIT TARİHİ</th>
                                        <th class="text-end">İŞLEMLER</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Main Content -->
@endsection 

@section('modals')
    <!--! [Start] Yeni Müşteri Ekleme Modalı !-->
    <div class="modal fade" id="yeniMusteriModal" tabindex="-1" aria-labelledby="yeniMusteriModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="yeniMusteriModalLabel">Yeni Müşteri Ekle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="yeniMusteriForm" action="{{ route('musteriler.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                         <div class="alert alert-danger d-none" id="musteriHataMesajlari"></div>
                         <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Müşteri Tipi <span class="text-danger">*</span></label>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="musteri_tip" id="modalTipBireysel" value="0" checked>
                                        <label class="form-check-label" for="modalTipBireysel">Bireysel</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="musteri_tip" id="modalTipKurumsal" value="1">
                                        <label class="form-check-label" for="modalTipKurumsal">Kurumsal</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="modal_kayit_tarihi" class="form-label">Kayıt Tarihi <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="modal_kayit_tarihi" name="kayit_tarihi" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="modal_kayit_saati" class="form-label">Kayıt Saati <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" id="modal_kayit_saati" name="kayit_saati" value="{{ date('H:i') }}" required>
                            </div>
                            <div class="col-12">
                                <label for="modal_ad" class="form-label">Müşteri Adı / Firma Adı <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="modal_ad" name="ad" placeholder="Ad Soyad veya Firma Adı" required>
                            </div>
                            <div class="col-md-6">
                                <label for="modal_tel1" class="form-label">Telefon 1 <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="modal_tel1" name="tel1" required>
                            </div>
                            <div class="col-md-6">
                                <label for="modal_tel2" class="form-label">Telefon 2</label>
                                <input type="text" class="form-control" id="modal_tel2" name="tel2">
                            </div>
                            <div class="col-md-6">
                                <label for="modal_il_id" class="form-label">İl <span class="text-danger">*</span></label>
                                <select class="form-select" id="modal_il_id" name="il_id" required>
                                    <option value="" selected disabled>İl Seçiniz...</option>
                                    @isset($iller)
                                        @foreach($iller as $il)
                                            <option value="{{ $il->id }}">{{ $il->ad }}</option>
                                        @endforeach
                                    @else
                                        <option value="" disabled>İller yüklenemedi</option>
                                    @endisset
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="modal_ilce_id" class="form-label">İlçe <span class="text-danger">*</span></label>
                                <select class="form-select" id="modal_ilce_id" name="ilce_id" required disabled>
                                    <option value="" selected disabled>Önce İl Seçiniz...</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="modal_adres" class="form-label">Adres <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="modal_adres" name="adres" rows="3" required></textarea>
                            </div>
                            <div id="vergiAlanlari" class="row g-3" style="display: none; margin-left: 0; margin-right: 0;">
                                <div class="col-md-6">
                                    <label for="modal_vdaire" class="form-label">Vergi Dairesi</label>
                                    <input type="text" class="form-control" id="modal_vdaire" name="vdaire">
                                </div>
                                <div class="col-md-6">
                                    <label for="modal_vno" class="form-label">Vergi Numarası</label>
                                    <input type="text" class="form-control" id="modal_vno" name="vno">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                        <button type="submit" class="btn btn-primary">MÜŞTERİYİ KAYDET</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!--! [End] Yeni Müşteri Ekleme Modalı !-->

    <!--! [Start] Müşteri Detay Modalı !-->
    <div class="modal fade" id="musteriDetayModal" tabindex="-1" aria-labelledby="musteriDetayModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="musteriDetayModalLabel">Müşteri Detayları (#<span id="detayMusteriId"></span>)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="musteriDetayContent">
                        <p class="text-center">Yükleniyor...</p>
                    </div>
                </div>
                <div class="modal-footer" id="modalFooterNormalMusteri">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                    <button type="button" class="btn btn-primary" id="musteriDuzenleBtn">Düzenle</button>
                </div>
                <div class="modal-footer" id="modalFooterEditMusteri" style="display: none;">
                    <button type="button" class="btn btn-secondary" id="musteriDuzenleVazgecBtn">Vazgeç</button>
                    <button type="button" class="btn btn-success" id="modalMusteriKaydetBtn">Değişiklikleri Kaydet</button>
                </div>
            </div>
        </div>
    </div>
    <!--! [End] Müşteri Detay Modalı !-->
@endsection 

@push('page_specific_vendor_js')
    <script src="{{ asset('crm_assets/vendors/js/dataTables.min.js') }}"></script>
    <script src="{{ asset('crm_assets/vendors/js/dataTables.bs5.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@push('page_specific_main_scripts')
    <script>
        // Sayfa yüklendiğinde ve stiller uygulandığında içeriği göster
        $(document).ready(function() {
            $('#mainContentWrapper').css('display', 'block');
        });
    </script>
    <script src="{{ asset('crm_assets/js/helpers.js') }}"></script>
    <script src="{{ asset('crm_assets/js/musteri-index.js') }}?v={{ filemtime(public_path('crm_assets/js/musteri-index.js')) }}"></script>
@endpush 
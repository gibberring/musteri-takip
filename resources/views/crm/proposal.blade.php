@extends('layouts.app')

@section('title', ($pendingOnly ?? false) ? 'Bekleyen Kayıtlar' : 'Servis Listesi')

@push('page_specific_css')
    <link rel="stylesheet" type="text/css" href="{{ asset('crm_assets/vendors/css/dataTables.bs5.min.css') }}">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css">
    {{-- Modal İçin Ekstra Stil --}}
    <style>
        #servisDetayModal .servisDetayTbl2 td {
            line-height: 1.3; /* Varsayılan değeri azaltır (örn: 1.5) */
            padding-top: 0.1rem; /* Üst padding'i azalt */
            padding-bottom: 0.1rem; /* Alt padding'i azalt */
        }
        #servisListTable .cihaz-ariza-text {
            white-space: normal;
            overflow-wrap: anywhere;
            word-break: break-word;
        }
        .islem-log-modal .modal-body {
            padding: 0.75rem;
        }
        .islem-log-modal .form-label {
            margin-bottom: 0.15rem;
        }
        .islem-log-modal .form-control-sm,
        .islem-log-modal .form-select-sm {
            padding-top: 0.2rem;
            padding-bottom: 0.2rem;
            min-height: 30px;
        }
        .islem-log-modal .form-select-sm {
            background-color: #f8f9fa;
        }
        .page-header-title,
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
            .page-header-left {
                justify-content: center;
                text-align: center;
            }
            .page-header-right {
                margin-top: 0.5rem;
                display: block !important;
            }
            .page-header-right-items {
                display: block !important;
            }
            .page-header-right-items-wrapper {
                display: flex !important;
                flex-wrap: wrap;
                justify-content: center;
                gap: 0.35rem;
            }
            .page-header-right .btn {
                font-size: 0.75rem;
                padding: 0.25rem 0.5rem;
            }
        .page-header-right .dropdown-menu .form-select,
        .page-header-right .dropdown-menu .form-select option {
            font-size: 0.7rem !important;
            line-height: 1.1 !important;
        }
            .page-header-right .dropdown-menu .mobile-filter-select,
            .page-header-right .dropdown-menu .mobile-filter-select option {
                font-size: 10px !important;
                line-height: 1.2 !important;
                padding-top: 0.2rem;
                padding-bottom: 0.2rem;
            }
            #servisDurumSelectMobile,
            #servisDurumSelectMobile option {
                font-size: 10px !important;
                line-height: 1.2 !important;
            }
            #servisDurumTeknisyenMobile,
            #servisDurumTeknisyenMobile option {
                font-size: 10px !important;
                line-height: 1.2 !important;
            }
            #servisDurumBaslangicTarihMobile,
            #servisDurumBitisTarihMobile {
                font-size: 10px !important;
                line-height: 1.2 !important;
                padding-top: 0.2rem;
                padding-bottom: 0.2rem;
            }
            #bolgeSehirMobile,
            #bolgeSehirMobile option,
            #bolgeBaslangicTarihMobile,
            #bolgeBitisTarihMobile,
            #operatorPersonelMobile,
            #operatorPersonelMobile option,
            #operatorBaslangicTarihMobile,
            #operatorBitisTarihMobile,
            #teknisyenPersonelMobile,
            #teknisyenPersonelMobile option,
            #teknisyenMarkaMobile,
            #teknisyenMarkaMobile option,
            #teknisyenCihazMobile,
            #teknisyenCihazMobile option,
            #teknisyenSehirMobile,
            #teknisyenSehirMobile option,
            #teknisyenBaslangicTarihMobile,
            #teknisyenBitisTarihMobile {
                font-size: 10px !important;
                line-height: 1.2 !important;
                padding-top: 0.2rem;
                padding-bottom: 0.2rem;
            }
            .bulk-servis-actions .small,
            .bulk-servis-actions .bulk-servis-durum-select,
            .bulk-servis-actions .bulk-servis-durum-select option {
                font-size: 10px !important;
                line-height: 1.2 !important;
            }
            #servisListTable {
                table-layout: auto !important;
            }
            #servisListTable th,
            #servisListTable td {
                white-space: normal !important;
                padding-top: 0.25rem;
                padding-bottom: 0.25rem;
            line-height: 1.1;
            }
            #servisListTable th:nth-child(1),
            #servisListTable td:nth-child(1),
            #servisListTable th:nth-child(2),
            #servisListTable td:nth-child(2),
            #servisListTable th:nth-child(3),
            #servisListTable td:nth-child(3),
            #servisListTable th:nth-child(4),
            #servisListTable td:nth-child(4),
            #servisListTable th:nth-child(5),
            #servisListTable td:nth-child(5) {
                width: auto !important;
                min-width: 0 !important;
                max-width: none !important;
            }
            #servisListTable th:nth-child(3),
            #servisListTable td:nth-child(3) {
                width: 74% !important;
            }
            #servisListTable th:nth-child(1),
            #servisListTable td:nth-child(1) {
                display: table-cell !important;
                width: 2% !important;
                max-width: 2% !important;
                white-space: nowrap !important;
                padding-left: 2px !important;
                padding-right: 2px !important;
            }
            #servisListTable th:nth-child(2),
            #servisListTable td:nth-child(2) {
                width: 8% !important;
                max-width: 8% !important;
                white-space: nowrap !important;
            }
            #servisListTable th:nth-child(1),
            #servisListTable td:nth-child(1),
            #servisListTable th:nth-child(2),
            #servisListTable td:nth-child(2) {
                font-size: 10px;
            }
            #servisListTable td:nth-child(2) div {
                font-size: 10px !important;
                line-height: 1.1;
            }
            #servisListTable thead th {
                font-size: 10px !important;
            }
            #servisListTable td:nth-child(3) {
                font-size: 10px !important;
            }
            #servisListTable td:nth-child(3) small {
                font-size: 10px !important;
            }
            #servisListTable td:nth-child(4),
            #servisListTable td:nth-child(5) {
                font-size: 10px !important;
            }
            #servisListTable td:nth-child(4) small,
            #servisListTable td:nth-child(5) small {
                font-size: 10px !important;
            }
            #servisListTable th:nth-child(4),
            #servisListTable td:nth-child(4),
            #servisListTable th:nth-child(5),
            #servisListTable td:nth-child(5) {
                width: 10% !important;
                max-width: 10% !important;
            }
            #servisListTable td:nth-child(3) .text-truncate-1-line {
                white-space: normal !important;
                overflow: visible !important;
            }
        }
        #servisDetayModal h6 {
             margin-bottom: 0.25rem; 
        }
         #servisDetayModal hr {
             margin-top: 0.5rem;
             margin-bottom: 0.75rem;
         }
         #modalDurumGuncelleSelect {
            font-size: 0.8rem; 
         }
         .page-header-right .dropdown-menu .form-select,
         .page-header-right .dropdown-menu .form-select option {
            font-size: 0.6rem !important;
            line-height: 1.1 !important;
         }
        .bulk-servis-actions .small,
        .bulk-servis-actions .bulk-servis-durum-select,
        .bulk-servis-actions .bulk-servis-durum-select option {
            font-size: 0.85rem !important;
            line-height: 1.2 !important;
        }
         .page-header-right .dropdown-menu input[type="date"] {
            font-size: 10px !important;
            line-height: 1.2 !important;
            padding-top: 0.2rem;
            padding-bottom: 0.2rem;
         }
        .cihaz-bilgi-input {
            height: 24px !important;
            padding: 0.1rem 0.3rem !important;
            font-size: 13px !important;
            line-height: 1.2 !important;
            border: 1px solid #dee2e6 !important;
        }
        .cihaz-bilgi-input:disabled {
            background-color: transparent !important;
            border: none !important;
        }
        .cihaz-bilgi-input:not(:disabled) {
            background-color: #f8f9fa !important;
        }
        #servisDetayModal .table-sm td {
            padding: 0.25rem !important;
            vertical-align: middle !important;
        }
        #servisDetayModal .table-sm textarea.cihaz-bilgi-input {
            height: auto !important;
            min-height: 24px !important;
            resize: vertical !important;
        }
        .cihaz-bilgi-input select { height: 24px !important; padding: 0.1rem 0.3rem !important; }
        #cihazBilgileriTable td, #cihazBilgileriTable th {
            height: 24px;
            padding: 0.15rem;
            vertical-align: middle;
            font-size: 12px;
            line-height: 1;
        }
        #cihazBilgileriTable th { background-color: #f8f9fa; }
        #cihazBilgileriTable .form-control-sm {
            height: 20px;
            padding: 0.05rem 0.2rem;
            font-size: 12px;
            line-height: 1;
            margin: 0;
        }
        #cihazBilgileriTable .form-control-sm:disabled { background-color: transparent; border: none; padding: 0; }
        #cihazBilgileriTable .form-control-sm:not(:disabled) { background-color: #fff; border: 1px solid #ced4da; }
        #cihazBilgileriTable textarea.form-control-sm { height: auto; min-height: 20px; resize: vertical; }
        #cihazBilgileriTable select.form-control-sm { padding-right: 1.2rem; }
        #servisListTable th:nth-child(1),
        #servisListTable td:nth-child(1) {
            width: 32px;
            white-space: nowrap;
        }
        #servisListTable th:nth-child(2),
        #servisListTable td:nth-child(2) {
            width: 62px;
            white-space: nowrap;
        }
        #servisListTable {
            table-layout: fixed;
        }
        #servisListTable th:nth-child(3),
        #servisListTable td:nth-child(3) {
            width: 42%;
        }
        #servisListTable th:nth-child(4),
        #servisListTable td:nth-child(4) {
            width: 140px;
        }
        #servisListTable th:nth-child(5),
        #servisListTable td:nth-child(5) {
            text-align: center !important;
        }
        #servisListTable th:nth-child(5),
        #servisListTable td:nth-child(5) {
            text-align: left !important;
            padding-left: 90px;
            min-width: 180px;
        }
        #servisListTable th:nth-child(4),
        #servisListTable td:nth-child(4) {
            text-align: left !important;
            width: 220px;
        }
        #servisListTable td,
        #servisListTable th {
            line-height: 1.3 !important;
        }
        #servisListTable tbody td {
            padding-top: 0.35rem;
            padding-bottom: 0.35rem;
        }
        #servisListTable tbody td {
            border-bottom: 2px solid #dee2e6;
        }
        #servisListTable tbody td {
            padding-top: 0.35rem;
            padding-bottom: 0.35rem;
        }
        #servisListTable td:nth-child(5) .durum-teknisyen {
            font-size: 0.7rem;
        }
        #servisListTable td:nth-child(5) .durum-teknisyen a {
            font-weight: 400;
        }
        #servisListTable_filter {
            display: none !important;
        }
        .servis-genel-arama {
            min-width: 220px;
        }
        #yeniServisAcBtn,
        #yeniServisAcBtnMobile {
            min-width: 120px;
            white-space: nowrap;
        }
        #servisListTable .servis-select-checkbox,
        #servisListTable #selectAllServisler {
            width: 16px;
            height: 26px;
            border-radius: 2px;
            accent-color: #3f5bd9;
        }
        #servisDetayModal .modal-input-compact, 
        #yeniServisModal .form-control-sm, 
        #yeniServisModal .form-select-sm {
            height: calc(1.2em + 0.5rem + 2px); padding: 0.15rem 0.3rem; font-size: 0.8rem; line-height: 1.2;
        }
        #servisDetayModal .form-label.small, 
        #yeniServisModal .form-label.small, 
        #yeniServisModal .form-label {
            margin-bottom: 0.1rem; font-size: 0.75rem;
        }
        #servisDetayModal p, #yeniServisModal p { margin-bottom: 0.5rem; }
        #servisDetayModal .row.g-2, #yeniServisModal .row.g-2 { --bs-gutter-y: 0.25rem; }
         #yeniServisModal .form-check-inline { margin-bottom: 0.1rem; padding-left: 0; }
         #yeniServisModal .form-check-label.small { font-size: 0.75rem; vertical-align: middle; }
         #yeniServisModal .form-check-input { margin-right: 0.25rem; float: none; }
         #yeniServisModal .card-body { padding: 0.75rem; }
         #yeniServisModal .card-header { padding: 0.5rem 0.75rem; }
         #yeniServisModal h6 { margin-bottom: 0; font-size: 0.9rem; }
         #yeniServisModal .mb-2 { margin-bottom: 0.5rem !important; }
         #yeniServisModal .mb-3 { margin-bottom: 0.75rem !important; }
        #yeniServisModal .col-auto > label.form-label { margin-right: 0.75rem; }
        #yeniServisModal .musteri-autocomplete-dropdown {
            z-index: 1060;
            max-height: 220px;
            overflow-y: auto;
            top: 100%;
            left: 0;
            margin-top: 2px;
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            background: #fff;
        }
        #yeniServisModal .musteri-autocomplete-dropdown .list-group-item {
            cursor: pointer;
            font-size: 0.8rem;
            padding: 0.4rem 0.6rem;
            border-left: 0;
            border-right: 0;
        }
        #yeniServisModal .musteri-autocomplete-dropdown .list-group-item:hover,
        #yeniServisModal .musteri-autocomplete-dropdown .list-group-item.active {
            background-color: #eef2ff;
        }
        #servisDetayModal .modal-header, #yeniServisModal .modal-header {
            background-color: #3454d1; color: white; padding-top: 0.5rem; padding-bottom: 0.5rem;
        }
        #servisDetayModal .modal-header .btn-close, #yeniServisModal .modal-header .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%);
        }
        #servisDetayModal .modal-header .modal-title, #servisDetayModal .modal-header .modal-title span, 
        #servisDetayModal .modal-header small span, #yeniServisModal .modal-header .modal-title {
            color: white !important;
        }
        #servisDetayModal .form-control-sm, #servisDetayModal .form-select-sm, #servisDetayModal .cihaz-bilgi-input,
        #yeniServisModal .form-control-sm, #yeniServisModal .form-select-sm {
            padding: 0.2rem 0.4rem; height: auto; line-height: 1.5; font-size: 0.8rem; 
        }
        #servisDetayModal .form-label-sm, #servisDetayModal .form-label.small,
        #yeniServisModal .form-label, #yeniServisModal .form-label.small {
            font-size: 0.75rem; margin-bottom: 0.1rem; padding-top: calc(0.2rem + 1px); padding-bottom: calc(0.2rem + 1px);
        }
         #servisDetayModal .input-group-sm > .btn { padding-top: 0.2rem; padding-bottom: 0.2rem; }
         #yeniServisModal .form-check-label.small { font-size: 0.75rem; padding-top: calc(0.2rem + 1px); padding-bottom: calc(0.2rem + 1px);}

        /* DataTables font ayarı */
        #servisListTable_wrapper table th {
            font-family: 'Inter', sans-serif;
            font-size: 13px; /* Kasa sayfasındaki ile eşleşecek şekilde güncellendi */
            font-weight: 600; /* Başlıklar kalın */
            line-height: 1.2;
        }
        #servisListTable_wrapper table td {
            font-family: 'Inter', sans-serif;
            font-size: 12px; /* Kasa sayfasındaki ile eşleşecek şekilde güncellendi */
            font-weight: 400; /* Veri hücreleri normal kalınlıkta */
            line-height: 1.2;
        }
    </style>
    <style>
        /* Cihaz Arızası öneri paneli - görünürlük/okunabilirlik */
        .ariza-suggest-panel {
            position: absolute !important;
            z-index: 4000 !important;
            background: #ffffff !important;
            background-image: none !important;
            color: #212529 !important;
            border: 0 !important;             /* çerçeve yok */
            border-radius: .25rem !important;
            box-shadow: none !important;      /* gölge yok */
            max-height: 240px !important;
            overflow-y: auto !important;
            width: 240px !important;          /* biraz daha kompakt */
        }
        .ariza-suggest-item {
            display: block !important;
            width: 100% !important;
            text-align: left !important;
            padding: .35rem .5rem !important; /* daha küçük padding */
            background: #ffffff !important;
            background-image: none !important;
            color: #212529 !important;
            border: 0 !important;
            cursor: pointer !important;
            font-size: .85rem !important;     /* font küçült */
            line-height: 1.2 !important;
        }
        .ariza-suggest-item + .ariza-suggest-item { border-top: 0 !important; }
        .ariza-suggest-item:hover { background: #f8f9fa !important; }
    </style>
    <style>
        #servisDetayModal .modal-footer .btn,
        #servisDetayModal #modalDurumKaydetBtn,
        #servisDetayModal #odemeEkleBtn {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        /* FOUC sorununu çözmek için başlangıçta ana içeriği gizle */
        #mainContentWrapper { display: none; }
        
        /* Durum badge'leri için stil */
        #servisListTable .badge {
            display: inline-block; /* Genişlik kontrolü için */
            min-width: 90px; /* Tüm badge'ler için sabit genişlik */
            text-align: center; /* Metni ortala */
            padding: 0.35em 0.6em; /* Varsayılan Bootstrap padding'i */
            font-size: 0.85em; /* Font boyutunu küçült */
        }
        #servisListTable .durum-log {
            white-space: normal;
            word-break: break-word;
        }
        #servisListTable td:nth-child(6) {
            text-align: center; /* Durum sütunundaki hücre içeriğini ortala */
        }
    </style>
@endpush

@section('content')
    @php
        $loggedInUser = Auth::user();
        $tsrnTeknisyenPozisyonId = 1077; // TŞRN Teknisyen pozisyon ID'si
        $operatorPozisyonId = 1073; // Operatör pozisyon ID'si
        $patronPozisyonId = 1071; // Patron pozisyon ID'si
    @endphp
    <!-- [ page-header ] start -->
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">{{ ($pendingOnly ?? false) ? 'Bekleyen Kayıtlar' : 'Servis Listesi' }}</h5>
            </div>
        </div>
        <div class="page-header-right ms-auto d-none d-sm-block">
            <div class="page-header-right-items">
                <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                    <button type="button" id="yeniServisAcBtn" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#yeniServisModal" style="display:none;">
                        <i class="feather-plus me-2"></i>
                        <span>YENİ SERVİS</span>
                    </button>
                    {{-- YENİ BÖLGE SERVİSLERİ DROPDOWN BAŞLANGIÇ (teknisyen görmez) --}}
                    @php $hideBolge = isset($hideBolgeServisleri) ? $hideBolgeServisleri : (isset($loggedInUser) && $loggedInUser && (int) $loggedInUser->poz_id === $tsrnTeknisyenPozisyonId); @endphp
                    <div class="dropdown @if($hideBolge) d-none @endif" @if($hideBolge) style="display:none !important;" @endif>
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="bolgeServisleriDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="feather-map-pin me-2"></i> BÖLGE SERVİSLERİ
                        </button>
                        <div class="dropdown-menu p-3 shadow" aria-labelledby="bolgeServisleriDropdown" style="min-width: 350px; max-width: 400px;">
                            <div class="mb-3">
                                <label for="bolgeSehir" class="form-label fw-semibold">Şehir</label>
                                <select class="form-select form-select-sm" id="bolgeSehir">
                                    <option selected value="">Tüm Şehirler</option>
                                    @if(isset($iller) && $iller->count() > 0)
                                        @foreach($iller as $il)
                                            <option value="{{ $il->id }}">{{ $il->ad }}</option>
                                        @endforeach
                                    @else
                                        <option disabled>Şehir bulunamadı</option>
                                    @endif
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Servis Kayıt Tarih Aralığı</label>
                                <div class="input-group input-group-sm">
                                    <input type="date" class="form-control" id="bolgeBaslangicTarih" placeholder="Başlangıç" value="{{ date('Y-m-d') }}">
                                    <span class="input-group-text">ile</span>
                                    <input type="date" class="form-control" id="bolgeBitisTarih" placeholder="Bitiş" value="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                            <button type="button" class="btn btn-primary btn-sm w-100" id="bolgeServisAraBtn">
                                <i class="feather-search me-1"></i> Ara
                            </button>
                        </div>
                    </div>
                    {{-- YENİ BÖLGE SERVİSLERİ DROPDOWN BİTİŞ --}}

                    {{-- YENİ OPERATÖR SERVİSLERİ DROPDOWN BAŞLANGIÇ --}}
                    <div class="dropdown" @if(isset($loggedInUser) && $loggedInUser && $loggedInUser->poz_id == $tsrnTeknisyenPozisyonId) style="display:none;" @endif>
                        <button class="btn btn-outline-info dropdown-toggle" type="button" id="operatorServisleriDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="feather-user-check me-2"></i> OPERATÖR SERVİSLERİ
                        </button>
                        <div class="dropdown-menu p-3 shadow" aria-labelledby="operatorServisleriDropdown" style="min-width: 350px; max-width: 400px;">
                            <div class="mb-3">
                                <label for="operatorPersonel" class="form-label fw-semibold">Personel</label>
                                <select class="form-select form-select-sm" id="operatorPersonel">
                                    <option selected value="">Tüm Personeller</option>
                                    @if(isset($operatorPersonelleri) && $operatorPersonelleri->count() > 0)
                                        @foreach($operatorPersonelleri as $personel)
                                            <option value="{{ $personel->id }}">{{ $personel->ad }}</option>
                                        @endforeach
                                    @else
                                        <option disabled>Operatör bulunamadı</option>
                                    @endif
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Servis Kayıt Tarih Aralığı</label>
                                <div class="input-group input-group-sm">
                                    <input type="date" class="form-control" id="operatorBaslangicTarih" placeholder="Başlangıç" value="{{ date('Y-m-d') }}">
                                    <span class="input-group-text">ile</span>
                                    <input type="date" class="form-control" id="operatorBitisTarih" placeholder="Bitiş" value="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                            <button type="button" class="btn btn-info btn-sm w-100" id="operatorServisAraBtn">
                                <i class="feather-search me-1"></i> Ara
                            </button>
                            @if(isset($loggedInUser) && $loggedInUser && (int) $loggedInUser->poz_id === $patronPozisyonId)
                                <a href="{{ route('servisler.operatorComparison') }}" data-base-href="{{ route('servisler.operatorComparison') }}" id="operatorComparisonLink" class="btn btn-outline-secondary btn-sm w-100 mt-2">
                                    <i class="feather-bar-chart-2 me-1"></i> Operatör Karşılaştırma
                                </a>
                            @endif
                        </div>
                    </div>
                    {{-- YENİ OPERATÖR SERVİSLERİ DROPDOWN BİTİŞ --}}
                    
                    {{-- YENİ TEKNİSYEN SERVİSLERİ DROPDOWN BAŞLANGIÇ --}}
                    <div class="dropdown" style="display:none;">
                        <button class="btn btn-outline-warning dropdown-toggle" type="button" id="teknisyenServisleriDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="feather-hard-drive me-2"></i> TEKNİSYEN SERVİSLERİ
                        </button>
                        <div class="dropdown-menu p-3 shadow" aria-labelledby="teknisyenServisleriDropdown" style="min-width: 350px; max-width: 400px;">
                            <div class="mb-3">
                                <label for="teknisyenPersonel" class="form-label fw-semibold">Personel</label>
                                <select class="form-select form-select-sm" id="teknisyenPersonel">
                                    <option selected value="">Tüm Teknisyenler</option>
                                    @if(isset($teknisyenPersonelleri) && $teknisyenPersonelleri->count() > 0)
                                        @foreach($teknisyenPersonelleri as $personel)
                                            <option value="{{ $personel->id }}">{{ $personel->ad }}</option>
                                        @endforeach
                                    @else
                                        <option disabled>Teknisyen bulunamadı</option>
                                    @endif
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="teknisyenMarka" class="form-label fw-semibold">Marka</label>
                                <select class="form-select form-select-sm" id="teknisyenMarka">
                                    <option selected value="">Tüm Markalar</option>
                                    @if(isset($markalar) && $markalar->count() > 0)
                                        @foreach($markalar as $marka)
                                            <option value="{{ $marka->id }}">{{ $marka->ad }}</option>
                                        @endforeach
                                    @else
                                        <option disabled>Marka bulunamadı</option>
                                    @endif
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="teknisyenCihaz" class="form-label fw-semibold">Cihaz</label>
                                <select class="form-select form-select-sm" id="teknisyenCihaz">
                                    <option selected value="">Tüm Cihazlar</option>
                                    @if(isset($cihazTurleri) && $cihazTurleri->count() > 0)
                                        @foreach($cihazTurleri as $tur)
                                            <option value="{{ $tur->id }}">{{ $tur->ad }}</option>
                                        @endforeach
                                    @else
                                        <option disabled>Cihaz bulunamadı</option>
                                    @endif
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="teknisyenSehir" class="form-label fw-semibold">Şehir</label>
                                <select class="form-select form-select-sm" id="teknisyenSehir">
                                    <option selected value="">Tüm Şehirler</option>
                                    @if(isset($iller) && $iller->count() > 0)
                                        @foreach($iller as $il)
                                            <option value="{{ $il->id }}">{{ $il->ad }}</option>
                                        @endforeach
                                    @else
                                        <option disabled>Şehir bulunamadı</option>
                                    @endif
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Gidiş Tarih Aralığı</label>
                                <div class="input-group input-group-sm">
                                    <input type="date" class="form-control" id="teknisyenBaslangicTarih" placeholder="Başlangıç" value="{{ date('Y-m-d') }}">
                                    <span class="input-group-text">ile</span>
                                    <input type="date" class="form-control" id="teknisyenBitisTarih" placeholder="Bitiş" value="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                            <button type="button" class="btn btn-warning btn-sm w-100" id="teknisyenServisAraBtn">
                                <i class="feather-search me-1"></i> Ara
                            </button>
                        </div>
                    </div>
                    {{-- YENİ TEKNİSYEN SERVİSLERİ DROPDOWN BİTİŞ --}}

                    {{-- YENİ SERVİS DURUM DROPDOWN BAŞLANGIÇ --}}
                    @if(!($pendingOnly ?? false))
                    <div class="dropdown" @if(isset($loggedInUser) && $loggedInUser && $loggedInUser->poz_id == $tsrnTeknisyenPozisyonId) style="display:none;" @endif>
                        <button class="btn btn-outline-success dropdown-toggle" type="button" id="servisDurumDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="feather-activity me-2"></i> SERVİS DURUM
                        </button>
                        <div class="dropdown-menu p-3 shadow" aria-labelledby="servisDurumDropdown" style="min-width: 350px; max-width: 400px;">
                            <div class="mb-3">
                                <label for="servisDurumTeknisyen" class="form-label fw-semibold">Teknisyen</label>
                                <select class="form-select form-select-sm" id="servisDurumTeknisyen">
                                    <option selected value="">Tüm Teknisyenler</option>
                                    @if(isset($teknisyenPersonelleri) && $teknisyenPersonelleri->count() > 0)
                                        @foreach($teknisyenPersonelleri as $personel)
                                            <option value="{{ $personel->id }}">{{ $personel->ad }}</option>
                                        @endforeach
                                    @else
                                        <option disabled>Teknisyen bulunamadı</option>
                                    @endif
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="servisDurumSelect" class="form-label fw-semibold">Servis Durumu</label>
                                <select class="form-select form-select-sm" id="servisDurumSelect">
                                    <option selected value="">Tüm Durumlar</option>
                                    @if(isset($servisDurumlar) && $servisDurumlar->count() > 0)
                                        @foreach($servisDurumlar as $durum)
                                            <option value="{{ $durum->id }}">{{ $durum->ad }}</option>
                                        @endforeach
                                    @else
                                        <option disabled>Durum bulunamadı</option>
                                    @endif
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Servis Kayıt Tarih Aralığı</label>
                                <div class="input-group input-group-sm">
                                    <input type="date" class="form-control" id="servisDurumBaslangicTarih" placeholder="Başlangıç" value="{{ date('Y-m-d') }}">
                                    <span class="input-group-text">ile</span>
                                    <input type="date" class="form-control" id="servisDurumBitisTarih" placeholder="Bitiş" value="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                            <button type="button" class="btn btn-success btn-sm w-100" id="servisDurumAraBtn">
                                <i class="feather-search me-1"></i> Ara
                            </button>
                        </div>
                    </div>
            @endif
            {{-- YENİ SERVİS DURUM DROPDOWN BİTİŞ --}}

                    @if(!isset($loggedInUser) || !$loggedInUser || (int) $loggedInUser->poz_id !== $tsrnTeknisyenPozisyonId)
                    <input type="text" id="servisGenelArama" class="form-control form-control-sm servis-genel-arama" placeholder="Genel Arama">
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="d-block d-sm-none mt-2">
        <div class="d-flex flex-wrap gap-2 justify-content-center">
            @if(!isset($loggedInUser) || !$loggedInUser || $loggedInUser->poz_id != $tsrnTeknisyenPozisyonId)
            <button type="button" id="yeniServisAcBtnMobile" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#yeniServisModal">
                <span>YENİ SERVİS</span>
            </button>
            @endif
            {{-- YENİ BÖLGE SERVİSLERİ DROPDOWN BAŞLANGIÇ (teknisyen görmez) --}}
            <div class="dropdown @if($hideBolge) d-none @endif" @if($hideBolge) style="display:none !important;" @endif>
                <button class="btn btn-outline-secondary dropdown-toggle btn-sm" type="button" id="bolgeServisleriDropdownMobile" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="feather-map-pin me-1"></i> BÖLGE SERVİSLERİ
                </button>
                <div class="dropdown-menu p-3 shadow" aria-labelledby="bolgeServisleriDropdownMobile" style="min-width: 280px; max-width: 320px;">
                    <div class="mb-3">
                        <label for="bolgeSehirMobile" class="form-label fw-semibold">Şehir</label>
                        <select class="form-select form-select-sm mobile-filter-select" id="bolgeSehirMobile">
                            <option selected value="">Tüm Şehirler</option>
                            @if(isset($iller) && $iller->count() > 0)
                                @foreach($iller as $il)
                                    <option value="{{ $il->id }}">{{ $il->ad }}</option>
                                @endforeach
                            @else
                                <option disabled>Şehir bulunamadı</option>
                            @endif
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Servis Kayıt Tarih Aralığı</label>
                        <div class="input-group input-group-sm">
                            <input type="date" class="form-control" id="bolgeBaslangicTarihMobile" placeholder="Başlangıç" value="{{ date('Y-m-d') }}">
                            <span class="input-group-text">ile</span>
                            <input type="date" class="form-control" id="bolgeBitisTarihMobile" placeholder="Bitiş" value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm w-100" id="bolgeServisAraBtnMobile">
                        <i class="feather-search me-1"></i> Ara
                    </button>
                </div>
            </div>
            {{-- YENİ BÖLGE SERVİSLERİ DROPDOWN BİTİŞ --}}

            {{-- YENİ OPERATÖR SERVİSLERİ DROPDOWN BAŞLANGIÇ --}}
            <div class="dropdown" @if(isset($loggedInUser) && $loggedInUser && $loggedInUser->poz_id == $tsrnTeknisyenPozisyonId) style="display:none;" @endif>
                <button class="btn btn-outline-info dropdown-toggle btn-sm" type="button" id="operatorServisleriDropdownMobile" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="feather-user-check me-1"></i> OPERATÖR SERVİSLERİ
                </button>
                <div class="dropdown-menu p-3 shadow" aria-labelledby="operatorServisleriDropdownMobile" style="min-width: 280px; max-width: 320px;">
                    <div class="mb-3">
                        <label for="operatorPersonelMobile" class="form-label fw-semibold">Personel</label>
                        <select class="form-select form-select-sm mobile-filter-select" id="operatorPersonelMobile">
                            <option selected value="">Tüm Personeller</option>
                            @if(isset($operatorPersonelleri) && $operatorPersonelleri->count() > 0)
                                @foreach($operatorPersonelleri as $personel)
                                    <option value="{{ $personel->id }}">{{ $personel->ad }}</option>
                                @endforeach
                            @else
                                <option disabled>Operatör bulunamadı</option>
                            @endif
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Servis Kayıt Tarih Aralığı</label>
                        <div class="input-group input-group-sm">
                            <input type="date" class="form-control" id="operatorBaslangicTarihMobile" placeholder="Başlangıç" value="{{ date('Y-m-d') }}">
                            <span class="input-group-text">ile</span>
                            <input type="date" class="form-control" id="operatorBitisTarihMobile" placeholder="Bitiş" value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                        <button type="button" class="btn btn-info btn-sm w-100" id="operatorServisAraBtnMobile">
                        <i class="feather-search me-1"></i> Ara
                    </button>
                        @if(isset($loggedInUser) && $loggedInUser && (int) $loggedInUser->poz_id === $patronPozisyonId)
                            <a href="{{ route('servisler.operatorComparison') }}" data-base-href="{{ route('servisler.operatorComparison') }}" id="operatorComparisonLinkMobile" class="btn btn-outline-secondary btn-sm w-100 mt-2">
                                <i class="feather-bar-chart-2 me-1"></i> Operatör Karşılaştırma
                            </a>
                        @endif
                </div>
            </div>
            {{-- YENİ OPERATÖR SERVİSLERİ DROPDOWN BİTİŞ --}}

            {{-- YENİ TEKNİSYEN SERVİSLERİ DROPDOWN BAŞLANGIÇ --}}
            @if(!isset($loggedInUser) || !$loggedInUser || $loggedInUser->poz_id != $tsrnTeknisyenPozisyonId)
            <div class="dropdown">
                <button class="btn btn-outline-warning dropdown-toggle btn-sm" type="button" id="teknisyenServisleriDropdownMobile" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="feather-hard-drive me-1"></i> TEKNİSYEN SERVİSLERİ
                </button>
                <div class="dropdown-menu p-3 shadow" aria-labelledby="teknisyenServisleriDropdownMobile" style="min-width: 280px; max-width: 320px;">
                    <div class="mb-3">
                        <label for="teknisyenPersonelMobile" class="form-label fw-semibold">Personel</label>
                        <select class="form-select form-select-sm mobile-filter-select" id="teknisyenPersonelMobile">
                            <option selected value="">Tüm Teknisyenler</option>
                            @if(isset($teknisyenPersonelleri) && $teknisyenPersonelleri->count() > 0)
                                @foreach($teknisyenPersonelleri as $personel)
                                    <option value="{{ $personel->id }}">{{ $personel->ad }}</option>
                                @endforeach
                            @else
                                <option disabled>Teknisyen bulunamadı</option>
                            @endif
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="teknisyenMarkaMobile" class="form-label fw-semibold">Marka</label>
                        <select class="form-select form-select-sm mobile-filter-select" id="teknisyenMarkaMobile">
                            <option selected value="">Tüm Markalar</option>
                            @if(isset($markalar) && $markalar->count() > 0)
                                @foreach($markalar as $marka)
                                    <option value="{{ $marka->id }}">{{ $marka->ad }}</option>
                                @endforeach
                            @else
                                <option disabled>Marka bulunamadı</option>
                            @endif
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="teknisyenCihazMobile" class="form-label fw-semibold">Cihaz</label>
                        <select class="form-select form-select-sm mobile-filter-select" id="teknisyenCihazMobile">
                            <option selected value="">Tüm Cihazlar</option>
                            @if(isset($cihazTurleri) && $cihazTurleri->count() > 0)
                                @foreach($cihazTurleri as $tur)
                                    <option value="{{ $tur->id }}">{{ $tur->ad }}</option>
                                @endforeach
                            @else
                                <option disabled>Cihaz bulunamadı</option>
                            @endif
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="teknisyenSehirMobile" class="form-label fw-semibold">Şehir</label>
                        <select class="form-select form-select-sm mobile-filter-select" id="teknisyenSehirMobile">
                            <option selected value="">Tüm Şehirler</option>
                            @if(isset($iller) && $iller->count() > 0)
                                @foreach($iller as $il)
                                    <option value="{{ $il->id }}">{{ $il->ad }}</option>
                                @endforeach
                            @else
                                <option disabled>Şehir bulunamadı</option>
                            @endif
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Gidiş Tarih Aralığı</label>
                        <div class="input-group input-group-sm">
                            <input type="date" class="form-control" id="teknisyenBaslangicTarihMobile" placeholder="Başlangıç" value="{{ date('Y-m-d') }}">
                            <span class="input-group-text">ile</span>
                            <input type="date" class="form-control" id="teknisyenBitisTarihMobile" placeholder="Bitiş" value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                    <button type="button" class="btn btn-warning btn-sm w-100" id="teknisyenServisAraBtnMobile">
                        <i class="feather-search me-1"></i> Ara
                    </button>
                </div>
            </div>
            @endif
            {{-- YENİ TEKNİSYEN SERVİSLERİ DROPDOWN BİTİŞ --}}

            {{-- YENİ SERVİS DURUM DROPDOWN BAŞLANGIÇ --}}
            @if(!($pendingOnly ?? false))
            <div class="dropdown" @if(isset($loggedInUser) && $loggedInUser && $loggedInUser->poz_id == $tsrnTeknisyenPozisyonId) style="display:none;" @endif>
                <button class="btn btn-outline-success dropdown-toggle btn-sm" type="button" id="servisDurumDropdownMobile" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="feather-activity me-1"></i> SERVİS DURUM
                </button>
                <div class="dropdown-menu p-3 shadow" aria-labelledby="servisDurumDropdownMobile" style="min-width: 280px; max-width: 320px;">
                    <div class="mb-3">
                        <label for="servisDurumTeknisyenMobile" class="form-label fw-semibold">Teknisyen</label>
                        <select class="form-select form-select-sm mobile-filter-select" id="servisDurumTeknisyenMobile">
                            <option selected value="">Tüm Teknisyenler</option>
                            @if(isset($teknisyenPersonelleri) && $teknisyenPersonelleri->count() > 0)
                                @foreach($teknisyenPersonelleri as $personel)
                                    <option value="{{ $personel->id }}">{{ $personel->ad }}</option>
                                @endforeach
                            @else
                                <option disabled>Teknisyen bulunamadı</option>
                            @endif
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="servisDurumSelectMobile" class="form-label fw-semibold">Servis Durumu</label>
                        <select class="form-select form-select-sm mobile-filter-select" id="servisDurumSelectMobile">
                            <option selected value="">Tüm Durumlar</option>
                            @if(isset($servisDurumlar) && $servisDurumlar->count() > 0)
                                @foreach($servisDurumlar as $durum)
                                    <option value="{{ $durum->id }}">{{ $durum->ad }}</option>
                                @endforeach
                            @else
                                <option disabled>Durum bulunamadı</option>
                            @endif
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Servis Kayıt Tarih Aralığı</label>
                        <div class="input-group input-group-sm">
                            <input type="date" class="form-control" id="servisDurumBaslangicTarihMobile" placeholder="Başlangıç" value="{{ date('Y-m-d') }}">
                            <span class="input-group-text">ile</span>
                            <input type="date" class="form-control" id="servisDurumBitisTarihMobile" placeholder="Bitiş" value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                    <button type="button" class="btn btn-success btn-sm w-100" id="servisDurumAraBtnMobile">
                        <i class="feather-search me-1"></i> Ara
                    </button>
                </div>
            </div>
            @endif
            {{-- YENİ SERVİS DURUM DROPDOWN BİTİŞ --}}

            @if(!isset($loggedInUser) || !$loggedInUser || (int) $loggedInUser->poz_id !== $tsrnTeknisyenPozisyonId)
            <input type="text" id="servisGenelAramaMobile" class="form-control form-control-sm servis-genel-arama" placeholder="Genel Arama">
            @endif
        </div>
    </div>
    <!-- [ page-header ] end -->
    <!-- [ Main Content ] start -->
    <div id="mainContentWrapper" class="main-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="card stretch stretch-full">
                    <div class="card-body p-0">
                        @php
                            $muhasebePozisyonId = 1080;
                            $canBulkServisDurum = $loggedInUser && in_array((int) $loggedInUser->poz_id, [$patronPozisyonId, $muhasebePozisyonId], true);
                        @endphp
                        @if($canBulkServisDurum)
                        <div class="px-3 pt-3">
                            <div class="bulk-servis-actions d-none">
                                <div class="d-flex align-items-center justify-content-end gap-2">
                                    <span class="small text-muted">Seçili kayıtlar için</span>
                                    <select class="form-select form-select-sm w-auto bulk-servis-durum-select">
                                        <option value="">Durum Seçiniz</option>
                                        <option value="__delete__">Servisi sil</option>
                                        @if(isset($servisDurumlar))
                                            @foreach($servisDurumlar as $durum)
                                                <option value="{{ $durum->id }}">{{ $durum->ad }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <button type="button" class="btn btn-sm btn-primary bulk-servis-durum-btn">Uygula</button>
                                </div>
                            </div>
                        </div>
                        @endif
                        <div class="table-responsive">
                            <table class="table table-hover" id="servisListTable">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>TARİH</th>
                                        <th>MÜŞTERİ</th>
                                        <th class="text-center">CİHAZ</th>
                                        <th class="text-center">Durum</th>
                                        @if($canBulkServisDurum)
                                            <th class="text-center no-print" style="width: 50px;">
                                                <input type="checkbox" id="selectAllServisler">
                                            </th>
                                        @endif
                                        
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($servisler as $servis)
                                        <tr class="single-item clickable-row" 
                                            data-servis-id="{{ $servis->id }}"
                                            data-musteri-ad="{{ $servis->musteri?->ad ?? '' }}"
                                            data-musteri-tel1="{{ $servis->musteri?->tel1 ?? '' }}"
                                            data-musteri-tel2="{{ $servis->musteri?->tel2 ?? '' }}"
                                            data-musteri-adres="{{ $servis->musteri?->adres ?? '' }}"
                                            data-musteri-il="{{ $servis->musteri?->il?->ad ?? '' }}"
                                            data-musteri-ilce="{{ $servis->musteri?->ilce?->ad ?? '' }}"
                                            data-marka-ad="{{ $servis->marka?->ad ?? 'N/A' }}"
                                            data-cihazturu-ad="{{ $servis->cihazTuru?->ad ?? 'N/A' }}"
                                            data-cihaz-model="{{ $servis->cihaz_model ?? '' }}"
                                            data-seri-no="{{ $servis->seri_no ?? '' }}"
                                            data-cihaz-arizasi="{{ $servis->cihaz_arizasi ?? '' }}"
                                            data-operator-not="{{ $servis->operator_not ?? '' }}"
                                            data-servis-durum-id="{{ $servis->servis_durum_id ?? '' }}"
                                            data-servis-kayit-tarihi="{{ $servis->created_at ? $servis->created_at->format('d.m.Y H:i') : '-' }}"
                                            data-servis-operatoru="{{ $servis->operator?->ad ?? ($servis->user?->name ?? ($servis->kullanici?->ad ?? 'N/A')) }}"
                                            data-musteri-vergi-dairesi="{{ $servis->musteri?->vdaire ?? '' }}"
                                            data-musteri-vergi-no="{{ $servis->musteri?->vno ?? '' }}"
                                            style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#servisDetayModal">
                                            {{-- SERVİS NO --}}
                                            <td><span class="fw-normal">{{ $servis->id }}</span></td>
                                            {{-- TARİH --}}
                                            <td>
                                                <div class="fs-11">{{ $servis->created_at ? $servis->created_at->format('d.m.Y') : '-' }}</div>
                                                <div class="fs-11 text-muted">{{ $servis->created_at ? $servis->created_at->format('H:i') : '-' }}</div>
                                            </td>
                                            {{-- MÜŞTERİ --}}
                                            <td>
                                                <a href="#" class="hstack gap-3">
                                                <div>
                                                        <span class="fw-bold">{{ $servis->musteri ? ucwords(strtolower($servis->musteri->ad)) : 'N/A' }}</span>
                                                        <small class="fs-11 fw-normal text-muted d-block">
                                                            {{ $servis->musteri?->tel1 ?? '' }}
                                                            {{ $servis->musteri?->tel2 ? ' / ' . $servis->musteri?->tel2 : '' }}
                                                        </small>
                                                        @php
                                                            $ilce = $servis->musteri?->ilce?->ad;
                                                            $il = $servis->musteri?->il?->ad;
                                                            $adres = $servis->musteri?->adres;
                                                            $adresGoster = $adres
                                                                ? (mb_strtoupper(mb_substr($adres, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr(mb_strtolower($adres, 'UTF-8'), 1, null, 'UTF-8'))
                                                                : '';
                                                            $adresParca = trim(implode(' / ', array_filter([$ilce, $il])));
                                                        @endphp
                                                        @if($adresGoster !== '' || $adresParca !== '')
                                                            <small class="fs-11 fw-normal text-muted d-block">
                                                                {{ $adresGoster }}
                                                                @if($adresParca !== '')
                                                                    {{ $adresGoster !== '' ? ' - ' : '' }}{{ $adresParca }}
                                                                @endif
                                                            </small>
                                                        @endif
                                                </div>
                                            </a>
                                        </td>
                                            {{-- MARKA / CİHAZ / ARIZA --}}
                                        <td class="text-center">
                                                <span class="fw-bold text-dark d-block text-center">{{ $servis->marka?->ad ?? 'N/A' }} / {{ $servis->cihazTuru?->ad ?? 'N/A' }}</span>
                                                <small class="fs-12 fw-normal text-muted d-block text-center fst-italic">{{ $servis->cihaz_arizasi ? mb_strtoupper(mb_substr($servis->cihaz_arizasi, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr(mb_strtolower($servis->cihaz_arizasi, 'UTF-8'), 1, null, 'UTF-8') : '' }}</small>
                                        </td>
                                            {{-- Durum --}}
                                            <td>
                                                <div class="fw-bold">{{ $servis->servisDurum?->ad ?? 'N/A' }}</div>
                                                @php
                                                    $teknisyenAd = $servis->assignedPersonnelName;
                                                    $hasAssignedTechnician = !empty($servis->assignedPersonnelId)
                                                        && !empty($teknisyenAd)
                                                        && $teknisyenAd !== 'Belirlenmedi';
                                                @endphp
                                                @if ($hasAssignedTechnician)
                                                    <small class="fs-11 fw-normal text-muted d-block durum-teknisyen">
                                                        Teknisyen :
                                                        <a href="{{ route('personel.teknisyenProfil', $servis->assignedPersonnelId) }}" class="text-decoration-none">
                                                            {{ $teknisyenAd }}
                                                        </a>
                                                    </small>
                                                @endif
                                                @php
                                                    $gidisTarihGoster = '-';
                                                    if (!empty($servis->tarih)) {
                                                        try {
                                                            $gidisTarihGoster = \Carbon\Carbon::parse($servis->tarih)->format('d.m.Y');
                                                        } catch (\Exception $e) {
                                                            $gidisTarihGoster = $servis->tarih;
                                                        }
                                                    }
                                                @endphp
                                                <small class="fs-11 fw-normal text-muted d-block">
                                                    Gidiş Tarihi : {{ $gidisTarihGoster }}
                                                </small>
                                        </td>
                                        @if($canBulkServisDurum)
                                            <td class="text-center no-print">
                                                <input type="checkbox" class="servis-select-checkbox" value="{{ $servis->id }}">
                                            </td>
                                        @endif
                                            
                                    </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ $canBulkServisDurum ? 7 : 6 }}" class="text-center">Kayıt bulunamadı.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if($canBulkServisDurum)
                        <div class="px-3 pb-3 pt-2">
                            <div class="bulk-servis-actions d-none">
                                <div class="d-flex align-items-center justify-content-end gap-2">
                                    <span class="small text-muted">Seçili kayıtlar için</span>
                                    <select class="form-select form-select-sm w-auto bulk-servis-durum-select">
                                        <option value="">Durum Seçiniz</option>
                                        <option value="__delete__">Servisi sil</option>
                                        @if(isset($servisDurumlar))
                                            @foreach($servisDurumlar as $durum)
                                                <option value="{{ $durum->id }}">{{ $durum->ad }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <button type="button" class="btn btn-sm btn-primary bulk-servis-durum-btn">Uygula</button>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- [ Main Content ] end -->
@endsection

@section('modals')
    {{-- Global modallar layout üzerinden geliyor. Bu sayfaya özel modallar aşağıya --}}
    <!--! ================================================================ !-->
    <!--! [Start] Yeni Servis Ekleme Modalı                              !-->
    <!--! ================================================================ !-->
    <div class="modal fade" id="yeniServisModal" tabindex="-1" aria-labelledby="yeniServisModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false" data-session-lifetime-minutes="{{ config('session.lifetime', 120) }}" data-keepalive-url="{{ url('/session/keepalive') }}">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="yeniServisModalLabel">Yeni Servis Kaydı</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="yeniServisForm" action="{{ route('servisler.store') }}" method="POST" autocomplete="off">
                    @csrf
                    <div class="modal-body"> {{-- Modal Body Başlangıcı --}}
                        <div class="alert alert-danger d-none" id="yeniServisHataMesajlari"></div>
                        <div class="alert alert-warning alert-dismissible fade show d-none py-2 small mb-2" id="yeniServisOturumUyarisi" role="alert">
                            <strong>Oturum uyarısı:</strong> Bu form uzun süredir açık. Oturumunuz yakında sona erebilir. Kaydetmeden önce sayfayı yenileyip tekrar giriş yapmanızı öneririz.
                            <button type="button" class="btn-close btn-close-sm" aria-label="Kapat" data-bs-dismiss="alert"></button>
                        </div>
                        
                        <div class="row"> {{-- Ana Satır --}}
                            
                            {{-- ==================== SOL SÜTUN ==================== --}}
                            <div class="col-md-6">
                                {{-- Müşteri Seçim Kartı --}}
                                <div class="card mb-2">
                                    <div class="card-header bg-light py-2"><h6 class="mb-0">Müşteri Bilgisi</h6></div>
                                    <div class="card-body p-3">
                                        <div class="row g-2">
                                            {{-- Müşteri Seçim Modu --}}
                                            <div class="col-md-12 mb-1">
                                                <div class="row align-items-center gx-2">
                                                    <div class="col-auto pe-1">
                                                        <label class="form-label small fw-bold mb-0">Müşteri Seçimi:</label>
                                                    </div>
                                                    <div class="col">
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="radio" name="musteri_secim_modu" id="secimVarolan" value="varolan" checked>
                                                            <label class="form-check-label small" for="secimVarolan">Varolan</label>
                                                        </div>
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="radio" name="musteri_secim_modu" id="secimYeni" value="yeni">
                                                            <label class="form-check-label small" for="secimYeni">Yeni</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            {{-- Varolan Müşteri Alanı (AJAX autocomplete) --}}
                                            <div id="varolanMusteriAlani" class="col-md-12 mb-0"
                                                 data-musteri-search-url="{{ route('musteriler.search') }}">
                                                <label for="modal_musteri_arama" class="form-label small fw-bold mb-1">Müşteri <span class="text-danger">*</span></label>
                                                <div class="position-relative">
                                                    <input type="text"
                                                           class="form-control form-control-sm"
                                                           id="modal_musteri_arama"
                                                           placeholder="İsim veya telefon yazın..."
                                                           autocomplete="off"
                                                           autocorrect="off"
                                                           autocapitalize="off"
                                                           spellcheck="false">
                                                    <input type="hidden" id="modal_musteri_id" name="musteri_id" value="">
                                                    <div id="modal_musteri_sonuclar"
                                                         class="list-group position-absolute w-100 shadow-sm d-none musteri-autocomplete-dropdown"></div>
                                                </div>
                                                <div id="modal_musteri_secili" class="small text-success mt-1 d-none"></div>
                                                <div class="form-text small text-muted mb-0">En az 2 karakter yazın; listeden seçin.</div>
                                            </div>
                                        </div>
                                    </div>
                                </div> 
                                {{-- /Müşteri Seçim Kartı --}}

                                {{-- Yeni Müşteri Alanları Kartı (Başlangıçta Gizli) --}}
                                <div id="yeniMusteriAlanlari" class="card mb-2" style="display: none;">
                                    <div class="card-header bg-light py-2"><h6 class="mb-0">Yeni Müşteri Bilgileri</h6></div>
                                    <div class="card-body p-3">
                                        <div class="row g-2"> 
                                            <div class="col-md-12 mb-1">
                                                <div class="row align-items-center gx-2">
                                                    <div class="col-auto pe-1">
                                                        <label class="form-label small fw-bold mb-0">Müşteri Tipi<span class="text-danger">*</span>:</label>
                                                    </div>
                                                    <div class="col">
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="radio" name="yeni_musteri_tip" id="yeniTipBireysel" value="0" checked>
                                                            <label class="form-check-label small" for="yeniTipBireysel">Bireysel</label>
                                                        </div>
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="radio" name="yeni_musteri_tip" id="yeniTipKurumsal" value="1">
                                                            <label class="form-check-label small" for="yeniTipKurumsal">Kurumsal</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <input type="hidden" id="yeni_kayit_tarihi" name="yeni_kayit_tarihi" value="{{ date('Y-m-d') }}">
                                            <input type="hidden" id="yeni_kayit_saati" name="yeni_kayit_saati" value="{{ date('H:i') }}">
                                            <div class="col-12 mb-1">
                                                <label for="yeni_ad" class="form-label small fw-bold mb-1">Ad / Firma Adı <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control form-control-sm" id="yeni_ad" name="yeni_ad" placeholder="Ad Soyad veya Firma Adı" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
                                            </div>
                                            <div class="col-md-6 mb-1">
                                                <label for="yeni_tel1" class="form-label small fw-bold mb-1">Telefon 1 <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control form-control-sm" id="yeni_tel1" name="yeni_tel1" required autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" inputmode="tel">
                                            </div>
                                            <div class="col-md-6 mb-1">
                                                <label for="yeni_tel2" class="form-label small fw-bold mb-1">Telefon 2</label>
                                                <input type="text" class="form-control form-control-sm" id="yeni_tel2" name="yeni_tel2" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" inputmode="tel">
                                            </div>
                                            <div class="col-md-6 mb-1">
                                                <label for="yeni_il_id" class="form-label small fw-bold mb-1">İl <span class="text-danger">*</span></label>
                                                <select class="form-select form-select-sm" id="yeni_il_id" name="yeni_il_id">
                                                     <option value="" selected disabled>İl Seçiniz...</option>
                                                     @isset($iller) @foreach($iller as $il) <option value="{{ $il->id }}">{{ $il->ad }}</option> @endforeach @else <option value="" disabled>İller yüklenemedi</option> @endisset
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-1">
                                                <label for="yeni_ilce_id" class="form-label small fw-bold mb-1">İlçe <span class="text-danger">*</span></label>
                                                <select class="form-select form-select-sm" id="yeni_ilce_id" name="yeni_ilce_id" disabled>
                                                    <option value="" selected disabled>Önce İl Seçiniz...</option>
                                                </select>
                                            </div>
                                            <div class="col-12 mb-1">
                                                <label for="yeni_adres" class="form-label small fw-bold mb-1">Adres <span class="text-danger">*</span></label>
                                                <textarea class="form-control form-control-sm" id="yeni_adres" name="yeni_adres" rows="3" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"></textarea>
                                            </div>
                                            <div class="col-12 mb-0 yeniVergiAlanlari" style="display: none;">
                                                <div class="row g-2">
                                                    <div class="col-md-6">
                                                        <label for="yeni_vdaire" class="form-label small fw-bold mb-1">Vergi Dairesi</label>
                                                        <input type="text" class="form-control form-control-sm" id="yeni_vdaire" name="yeni_vdaire">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="yeni_vno" class="form-label small fw-bold mb-1">Vergi Numarası</label>
                                                        <input type="text" class="form-control form-control-sm" id="yeni_vno" name="yeni_vno">
                                                    </div>
                                                </div>
                                            </div>
                                        </div> 
                                    </div>
                                </div> 
                            </div> {{-- /Sol Sütun Sonu --}}

                            {{-- ==================== SAĞ SÜTUN ==================== --}}
                            <div class="col-md-6">
                                <div class="card mb-3">
                                     <div class="card-header bg-light py-2"><h6 class="mb-0">Servis/Cihaz Bilgileri</h6></div>
                                     <div class="card-body p-3">
                                        <div class="row g-2"> {{-- İç Satır --}}
                                             <!-- Marka Seçimi -->
                                            <div class="col-md-12 mb-1">
                                                <div class="row align-items-center gx-2">
                                                    <div class="col-4">
                                                        <label for="modal_marka_id" class="form-label small fw-bold mb-0 text-end">Marka<span class="text-danger">*</span>:</label>
                                                    </div>
                                                    <div class="col-8">
                                                        <select class="form-select form-select-sm" id="modal_marka_id" name="marka_id" required>
                                                            <option value="" selected disabled>Seçiniz...</option>
                                                             @isset($markalar) @foreach($markalar as $marka) <option value="{{ $marka->id }}">{{ $marka->ad }}</option> @endforeach @else <option value="" disabled>Markalar yüklenemedi</option> @endisset
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                             <!-- Cihaz Türü Seçimi -->
                                            <div class="col-md-12 mb-1">
                                                <div class="row align-items-center gx-2">
                                                    <div class="col-4">
                                                        <label for="modal_cihaz_tur_id" class="form-label small fw-bold mb-0 text-end">Cihaz Türü<span class="text-danger">*</span>:</label>
                                                    </div>
                                                    <div class="col-8">
                                                        <select class="form-select form-select-sm" id="modal_cihaz_tur_id" name="cihaz_tur_id" required>
                                                            <option value="" selected disabled>Seçiniz...</option>
                                                             @isset($cihazTurleri) @foreach($cihazTurleri as $tur) <option value="{{ $tur->id }}">{{ $tur->ad }}</option> @endforeach @else <option value="" disabled>Cihaz Türleri yüklenemedi</option> @endisset
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Cihaz Modeli -->
                                            <div class="col-md-6 mb-1">
                                                <label for="modal_cihaz_model" class="form-label small fw-bold mb-1">Cihaz Modeli</label>
                                                <input type="text" class="form-control form-control-sm" id="modal_cihaz_model" name="cihaz_model" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
                                            </div>
                                            <div class="col-md-6 mb-1">
                                                <label for="modal_seri_no" class="form-label small fw-bold mb-1">Seri No</label>
                                                <input type="text" class="form-control form-control-sm" id="modal_seri_no" name="seri_no" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
                                            </div>
                                            <div class="col-12 mb-2">
                                                <label for="modal_cihaz_arizasi" class="form-label pb-0 mb-1 small fw-bold">Cihaz Arızası / Şikayet <span class="text-danger">*</span></label>
                                                <textarea class="form-control form-control-sm" id="modal_cihaz_arizasi" name="cihaz_arizasi" rows="2" required autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"></textarea>
                                            </div>
                                             <div class="col-12 mb-0">
                                                <label for="modal_operator_not" class="form-label small fw-bold mb-1">Operatör Notu</label> 
                                                <textarea class="form-control form-control-sm" id="modal_operator_not" name="operator_not" rows="2"></textarea>
                                            </div>
                                        </div> 
                                    </div>
                                </div> 
                            </div> {{-- /Sağ Sütun Sonu --}}

                        </div> {{-- /Ana Satır Sonu --}}
                    </div> {{-- /Modal Body Sonu --}}
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">KAPAT</button>
                        <button type="submit" class="btn btn-primary">SERVİS KAYDINI OLUŞTUR</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!--! ================================================================ !-->
    <!--! [End] Yeni Servis Ekleme Modalı                                !-->
    <!--! ================================================================ !-->

    <!--! ================================================================ !-->
    <!--! [Start] Düzenleme Modalı (İşlem Logları için)                  !-->
    <!--! ================================================================ !-->
    <div class="modal fade" id="duzenleIslemLogModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h5 class="modal-title">İşlem Kaydını Düzenle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="duzenleIslemLogId">
                    <div class="mb-2">
                        <label class="form-label small">Tarih</label>
                        <input type="date" class="form-control form-control-sm" id="duzenleIslemLogTarih">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Saat</label>
                        <input type="time" class="form-control form-control-sm" id="duzenleIslemLogSaat">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Durum</label>
                        <select class="form-control form-control-sm" id="duzenleIslemLogDurum">
                            {{-- $servisDurumlar layouts.app üzerinden global olarak sağlanmalı --}}
                            @if(isset($servisDurumlar))
                                @foreach($servisDurumlar as $durum)
                                    <option value="{{ $durum->id }}">{{ $durum->ad }}</option>
                                @endforeach
                            @else
                                <option disabled>Durumlar yüklenemedi</option>
                            @endif
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Açıklama</label>
                        <textarea class="form-control form-control-sm" id="duzenleIslemLogAciklama" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer py-1">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="button" class="btn btn-sm btn-primary" onclick="kaydetIslemLog()">Kaydet</button>
                </div>
            </div>
        </div>
    </div>
    <!--! ================================================================ !-->
    <!--! [End] Düzenleme Modalı                                         !-->
    <!--! ================================================================ !-->

    <!--! ================================================================ !-->
    <!--! [Start] Servis Fişi İşlemleri Modal                            !-->
    <!--! ================================================================ !-->
    <div class="modal fade" id="servisFisiModal" tabindex="-1" aria-labelledby="servisFisiModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="servisFisiModalLabel">Servis Fişi İşlemleri (Servis No: <span id="servisFisiModalServisId"></span>)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-grid gap-2 mb-3">
                        <button class="btn btn-success" type="button" id="yeniServisFisiOlusturBtn">
                            <i class="feather feather-plus-circle me-1"></i> Yeni Servis Fişi Oluştur
                        </button>
                    </div>
                    <h6>Daha Önce Oluşturulan Fişler:</h6>
                    <div id="eskiServisFisleriListesi" class="list-group" style="max-height: 300px; overflow-y: auto;">
                        <p class="text-muted text-center">Bu servise ait daha önce oluşturulmuş fiş bulunmamaktadır.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                </div>
            </div>
        </div>
    </div>
    <!--! ================================================================ !-->
    <!--! [End] Servis Fişi İşlemleri Modal                              !-->
    <!--! ================================================================ !-->

    <!--! ================================================================ !-->
    <!--! [Start] İmza Modalı                                            !-->
    <!--! ================================================================ !-->
    <div class="modal fade" id="imzaModal" tabindex="-1" aria-labelledby="imzaModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imzaModalLabel">Servis Fişi İçin İmzalar (Servis No: <span id="imzaModalServisId"></span>)</h5>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">Lütfen aşağıdaki alanlara müşteri ve teknisyen imzalarını alınız. İmzalar servis fişine eklenecektir.</p>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="musteriImzaAlani" class="form-label fw-bold">Müşteri İmzası:</label>
                            <div id="musteriImzaWrapper" style="border: 1px solid #ced4da; border-radius: .25rem; min-height: 200px; cursor: crosshair;">
                                <canvas id="musteriImzaAlani" class="imza-alani"></canvas>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="musteriImzaTemizleBtn">Temizle</button>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="teknisyenImzaAlani" class="form-label fw-bold">Teknisyen İmzası:</label>
                            <div id="teknisyenImzaWrapper" style="border: 1px solid #ced4da; border-radius: .25rem; min-height: 200px; cursor: crosshair;">
                                <canvas id="teknisyenImzaAlani" class="imza-alani"></canvas>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="teknisyenImzaTemizleBtn">Temizle</button>
                        </div>
                    </div>
                    <div id="imzaHataMesaji" class="alert alert-danger d-none mt-2"></div>
                </div>
                <div class="modal-footer justify-content-between">
                    <div>
                        <button type="button" class="btn btn-light" id="imzaModalVazgecBtn" data-bs-dismiss="modal">Vazgeç</button>
                    </div>
                    <div>
                        <button type="button" class="btn btn-warning me-2" id="servisFormuImzasizVerBtn">
                            <i class="feather-file-minus me-1"></i> Servis Formunu İmzasız Ver
                        </button>
                        <button type="button" class="btn btn-success" id="imzalariKaydetVeFormuVerBtn">
                            <i class="feather-check-circle me-1"></i> İmzaları Kaydet & Servis Formunu Ver
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--! ================================================================ !-->
    <!--! [End] İmza Modalı                                              !-->
    <!--! ================================================================ !-->
@endsection

@push('page_specific_vendor_js')
    <script src="{{ asset('crm_assets/vendors/js/dataTables.min.js') }}"></script>
    <script src="{{ asset('crm_assets/vendors/js/dataTables.bs5.min.js') }}"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.colVis.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
@endpush

@push('page_specific_main_scripts')
    <script>
        // Sayfa yüklendiğinde ve stiller uygulandığında içeriği göster
        $(document).ready(function() {
            $('#mainContentWrapper').css('display', 'block');
            // Genelkasa vb. sayfalardan ?open_servis_id= veya #servis-{id} ile gelindiyse önce erişim kontrolü, sonra modal aç
            var params = new URLSearchParams(window.location.search);
            var openServisId = (params.get('open_servis_id') || '').trim();
            if (!openServisId && window.location.hash) {
                var hashMatch = String(window.location.hash).match(/^#servis-(\d+)/i);
                if (hashMatch) {
                    openServisId = hashMatch[1];
                }
            }
            if (openServisId !== '') {
                history.replaceState({}, document.title, window.location.pathname);
                $.ajax({
                    url: '/servisler/' + openServisId + '/detay',
                    method: 'GET',
                    success: function() {
                        $('#servisDetayModal').data('servis-id', openServisId);
                        if (typeof window.mevcutServisId !== 'undefined') { window.mevcutServisId = openServisId; }
                        if (typeof window.openServisDetay === 'function') {
                            window.openServisDetay(openServisId);
                        } else {
                            var modalEl = document.getElementById('servisDetayModal');
                            if (modalEl && window.bootstrap && window.bootstrap.Modal) {
                                var modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
                                modalInstance.show();
                            }
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 403) {
                            var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Bu servise erişim yetkiniz yok.';
                            if (window.Swal && Swal.fire) {
                                Swal.fire({ icon: 'error', title: 'Erişim reddedildi', text: msg });
                            } else {
                                alert(msg);
                            }
                        } else {
                            if (window.Swal && Swal.fire) {
                                Swal.fire({ icon: 'error', title: 'Hata', text: 'Servis detayı yüklenemedi.' });
                            } else {
                                alert('Servis detayı yüklenemedi.');
                            }
                        }
                    }
                });
            }
        });
    </script>
    <script>
        window.crmData = window.crmData || {};
        window.crmData.pendingOnly = {{ ($pendingOnly ?? false) ? 'true' : 'false' }};
    </script>
    <script>
        // Cihaz Arızası / Şikayet için öneriler
        (function(){
            var suggestions = [
                'Çalışmıyor',
                'Soğutmuyor',
                'Alt kısım soğutmuyor',
                'Üst kısım soğutmuyor',
                'Isıtmıyor',
                'Sıcak su vermiyor',
                'Petekleri ısıtmıyor'
            ];
            var $field = $('#modal_cihaz_arizasi');
            var $panel;
            function hidePanel(){ if($panel){ $panel.remove(); $panel = null; } }
            function showPanel(){
                hidePanel();
                var pos = $field.offset();
                var fieldW = $field.outerWidth();
                var panelWidth = 260;
                var viewportW = $(window).width();
                var viewportH = $(window).height();
                var scrollTop = $(window).scrollTop();
                var scrollLeft = $(window).scrollLeft();
                var leftCandidate = pos.left + fieldW + 8; // inputun hemen sağı
                // Sağdan taşarsa sola düşür
                if (leftCandidate + panelWidth > scrollLeft + viewportW - 8) {
                    leftCandidate = pos.left - panelWidth - 8;
                }
                var topCandidate = pos.top; // inputun üst hizası
                var topPos = Math.max(scrollTop + 8, Math.min(topCandidate, scrollTop + viewportH - 16 - 240));
                $panel = $('<div/>', {
                    class: 'ariza-suggest-panel',
                    css: {
                        position: 'absolute',
                        zIndex: 4000,
                        top: topPos,
                        left: leftCandidate,
                        width: panelWidth
                    }
                });
                suggestions.forEach(function(text){
                    var $btn = $('<div/>', {
                        class: 'ariza-suggest-item',
                        text: text
                    }).css({
                        fontSize: '0.95rem'
                    }).on('mouseenter', function(){
                        $(this).css({ backgroundColor: '#f8f9fa' });
                    }).on('mouseleave', function(){
                        $(this).css({ backgroundColor: '#ffffff' });
                    }).on('click', function(){
                        var current = ($field.val() || '').trim();
                        if(current && !current.endsWith('\n')) current += '\n';
                        $field.val(current + text).trigger('input');
                        hidePanel();
                        $field.focus();
                    });
                    $panel.append($btn);
                });
                $('body').append($panel);
            }
            $field.on('focus click', function(){ showPanel(); });
            $(window).on('resize scroll', hidePanel);
            $(document).on('click', function(e){
                if($panel && !$(e.target).closest($panel).length && !$(e.target).is($field)) hidePanel();
            });
        })();
    </script>
    <script src="{{ asset('crm_assets/js/helpers.js') }}"></script>
    <script src="{{ asset('crm_assets/js/proposal.js') }}?v={{ filemtime(public_path('crm_assets/js/proposal.js')) }}"></script> 
@endpush

{{-- @push('page_specific_init_js') --}}
    {{-- Bu sayfa için özel bir dashboard-init gibi bir dosya yoksa boş bırakılabilir veya kaldırılabilir --}}
{{-- @endpush --}}

{{-- @push('page_specific_scripts') --}}
    {{-- Bu sayfaya özel anlık JS kodları varsa buraya --}}
{{-- @endpush --}}

</body>

</html>

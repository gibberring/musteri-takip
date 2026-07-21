@extends('layouts.app')

@section('title', 'Genel Kasa Hareketleri')

@push('page_specific_css')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    /* Kasa sayfasına özel ek stiller buraya eklenebilir */
    .page-header-title,
    .page-header h5 {
        border-right: none !important;
        padding-right: 0 !important;
        margin-right: 0 !important;
    }
    /* Sağdaki frame (içerik alanı sağ kenarı) kalın */
    #mainContentWrapper.main-content,
    .main-content {
        border-right: 4px solid #dee2e6;
    }
    #kasaListTable th { /* Sadece başlıklar için */
        font-family: 'Inter', sans-serif;
        font-size: 13px; /* 0.85rem yaklaşık 13.6px idi, 13px olarak güncellendi */
        font-weight: 600; /* Başlıklar kalın */
        line-height: 1.2;
        padding: 0.4rem; /* Mevcut padding korundu */
    }
    #kasaListTable tbody td { /* Sadece içerik hücreleri için - başlıklar hariç */
        font-family: 'Inter', sans-serif;
        font-size: 12px; /* 0.78rem yaklaşık 12.5px idi, 12px olarak güncellendi */
        font-weight: 400; /* Veri hücreleri normal kalınlıkta */
        line-height: 1.2;
        padding: 0.4rem; /* Mevcut padding korundu */
    }
    #kasaListTable tbody td:nth-child(3) { /* Ödeme Türü sütunu (3. çocuk) */
        font-weight: bold; /* Bu özel kural korunmalı */
    }
    /* Kasa Hareketi Modalı için daha kompakt inputlar */
    #kasaHareketiModal .form-control-sm,
    #kasaHareketiModal .form-select-sm {
        padding: 0.2rem 0.4rem; /* Üst/alt ve yan padding'leri ayarla */
        height: auto; /* Yüksekliği içeriğe göre ayarla, padding ile kontrol et */
        line-height: 1.5; /* Satır yüksekliği, dikey ortalamaya yardımcı olabilir */
        font-size: 0.8rem; /* Fontu da biraz küçültelim */
    }
    #kasaHareketiModal .form-label-sm {
        font-size: 0.75rem; /* Etiket fontunu da küçült */
        margin-bottom: 0.1rem; /* Etiket alt boşluğunu azalt */
        padding-top: calc(0.2rem + 1px); /* Input padding'i ile uyumlu hale getirmek için */
        padding-bottom: calc(0.2rem + 1px);
    }
    #kasaHareketiModal .input-group-sm > .btn {
         padding-top: 0.2rem;
         padding-bottom: 0.2rem; 
    }
    /* İlişkili Servis ID inputu dar, yanında açılır pencere butonu */
    #kasaHareketiModal #modalKasaServisID {
        max-width: 90px;
    }
    #kasaHareketiModal #modalKasaServisLink {
        flex-shrink: 0;
    }
    /* Kasa Hareketi Modal Başlığı Stili */
    #kasaHareketiModal .modal-header {
        background-color: #3454d1; /* Güncellendi */
        color: white; /* Başlık yazı rengi */
        padding-top: 0.5rem;    /* Üst padding'i azalt */
        padding-bottom: 0.5rem; /* Alt padding'i azalt */
    }
    #kasaHareketiModal .modal-header .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%); /* Kapatma butonu rengini beyaza çevir */
    }
    /* Modal başlık metni ve ID için ekstradan beyaz renk */
    #kasaHareketiModal .modal-header .modal-title,
    #kasaHareketiModal .modal-header .modal-title span {
        color: white !important; /* Gerekirse !important ile önceliği artır */
    }

    /* Kasa Tablosu Sütun Genişlikleri */
    #kasaListTable thead th:nth-child(2), /* Tarih */
    #kasaListTable tbody td:nth-child(2) {
        width: 130px; 
        max-width: 130px;
        white-space: nowrap;
    }

    #kasaListTable thead th:nth-child(4), /* Açıklama */
    #kasaListTable tbody td:nth-child(4) {
        width: 250px; /* Genişliği azaltıldı */
        white-space: normal; /* Açıklamaların alt satıra kaymasını sağlar */
    }

    /* Kasa Tablosu Arama Kutusu Konumu */
    #kasaListTable_filter {
        float: right;
        text-align: right;
        margin-bottom: 10px; /* Arama kutusu ile tablo arasına biraz boşluk */
    }
    /* DataTables_length (kayıt sayısı seçimi) sola yaslamak için (eğer float:right etkiliyorsa) */
    #kasaListTable_length {
        float: left;
    }
    /* Wrapper için clear fix */
    #kasaListTable_wrapper .row:first-child::after {
        content: "";
        clear: both;
        display: table;
    }

    /* Arama dropdown'ı içindeki input ve select'lerin yüksekliğini azalt */
    #aramaForm .form-control-sm,
    #aramaForm .form-select-sm {
        padding: 0.1rem 0.3rem; /* Daha da küçük padding */
        height: 25px; /* Sabit daha küçük yükseklik */
        min-height: 25px; /* Minimum yüksekliği de belirle */
        font-size: 0.75rem; /* Fontu küçült */
    }
    #aramaForm .form-select-sm {
        height: 30px;
        min-height: 30px;
    }
    .kasa-advanced-dropdown input[type="date"] {
        font-size: 10px !important;
        line-height: 1.2 !important;
        padding-top: 0.2rem;
        padding-bottom: 0.2rem;
    }

    #aramaForm .col-form-label-sm {
        font-size: 0.7rem; /* Label fontunu daha da küçült */
        padding-top: 0.1rem; /* Label padding'ini azalt */
        padding-bottom: 0.1rem;
        white-space: nowrap; /* Metinlerin tek satırda kalmasını sağlar */
    }

    #aramaForm .d-flex.justify-content-between.mt-1 a {
        font-size: 0.7rem; /* Tarih aralığı linklerinin fontunu küçült */
    }

    /* Datepicker için z-index ve arka plan düzeltmesi */
    .ui-datepicker {
        z-index: 999999 !important; /* Tüm öğelerden daha yüksek bir z-index değeri */
        background-color: white; /* Arka planı beyaz yap */
        border: 1px solid #ccc; /* Kenarlık ekle */
    }
    /* FOUC sorununu çözmek için başlangıçta ana içeriği gizle */
    #mainContentWrapper { display: none; }
</style>
@endpush

@section('content')
    <!-- [ page-header ] start -->
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Genel Kasa Hareketleri</h5>
            </div>
        </div>
        {{-- Mobil: Yeni Kasa Hareketi ve EXCEL (yetkiye göre) --}}
        <div class="d-flex d-sm-none align-items-center justify-content-end gap-2 ms-auto">
            @if (!empty($canExport))
                @php $exportQuery = request()->query(); @endphp
                <a href="{{ url('/genelkasa/export') }}{{ count($exportQuery) ? '?' . http_build_query($exportQuery) : '' }}" class="btn btn-outline-success btn-sm" id="genelKasaExportBtnMobile">
                    <i class="feather-download"></i>
                </a>
            @endif
            @if (!empty($canAddKasa))
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#kasaHareketiModal" id="btnYeniKasaHareketiMobile">
                    <i class="feather-plus me-1"></i>
                    <span>YENİ KASA HAREKETİ</span>
                </button>
            @endif
        </div>
        {{-- Masaüstü: EXCEL ve Yeni Kasa Hareketi (yetkiye göre) --}}
        <div class="page-header-right ms-auto d-none d-sm-flex">
            <div class="page-header-right-items">
                <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                    @if (!empty($canExport))
                        @php $exportQuery = request()->query(); @endphp
                        <a href="{{ url('/genelkasa/export') }}{{ count($exportQuery) ? '?' . http_build_query($exportQuery) : '' }}" class="btn btn-outline-success" id="genelKasaExportBtn">
                            <i class="feather-download me-2"></i>
                            <span>EXCEL</span>
                        </a>
                    @endif
                    @if (!empty($canAddKasa))
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#kasaHareketiModal">
                            <i class="feather-plus me-2"></i>
                            <span>YENİ KASA HAREKETİ</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- [ page-header ] end -->
    <!-- [ Main Content ] start -->
    <div id="mainContentWrapper" class="main-content" style="overflow: visible !important;">
        <div class="row">
            <div class="col-lg-12">
                <div class="card stretch stretch-full" style="overflow: visible !important;">
                    <div class="card-body p-0"> {{-- Ana card-body --}}
                        <div class="d-flex"> {{-- Arama kutusunu sağa yaslamak için --}}
                            {{-- Yeni Arama Bölümü Başlangıcı --}}
                            <div class="input-group mb-3 ms-auto" style="padding: 15px; background-color: #f8f9fa; border-bottom: 1px solid #e9ecef; width: auto; position: relative;">
                                <input placeholder="Tutar veya teknisyen yazın." type="text" id="aratxt" name="odenentutar" autocomplete="off" class="form-control form-control-sm" style="height: 30px; max-width: 200px;">
                                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false" style="height: 30px;">
                                    <i class="feather-arrow-down"></i>
                                </button>
                                <button class="btn btn-primary btn-sm" type="button" id="kasaAraBtn" style="height: 30px;">
                                    <i class="feather-search"></i>
                                </button>
                                <div class="dropdown-menu p-3 kasa-advanced-dropdown" aria-labelledby="dropdownMenuButton" style="width: 300px; max-height: 400px; overflow-y: auto; z-index: 9999;">
                                    <form id="aramaForm" onsubmit="arama(); return false;">
                                        <div class="row mb-2">
                                            <div class="col-4"><label for="odemeTur" class="col-form-label col-form-label-sm">Ödeme Türü:</label></div>
                                            <div class="col-8">
                                                <select name="odeme_turu" id="odemeTur" class="form-select form-select-sm">
                                                    <option value="0">Hepsi</option>
                                                    @foreach($tumOdemeTurleri as $tur)
                                                        <option value="{{ $tur->id }}" {{ request('odeme_turu') == $tur->id ? 'selected' : '' }}>{{ $tur->ad }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-4"><label for="odemeyonu" class="col-form-label col-form-label-sm">Ödeme Yönü:</label></div>
                                            <div class="col-8">
                                                <select name="odeme_yonu" id="odemeyonu" class="form-select form-select-sm">
                                                    <option value="">Hepsi</option>
                                                    <option value="1" {{ request('odeme_yonu') === '1' ? 'selected' : '' }}>Gelen Ödeme</option>
                                                    <option value="-1" {{ request('odeme_yonu') === '-1' ? 'selected' : '' }}>Giden Ödeme</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-4"><label for="odemedurum" class="col-form-label col-form-label-sm">Ödeme Durumu:</label></div>
                                            <div class="col-8">
                                                <select name="odeme_durumu" id="odemedurum" class="form-select form-select-sm">
                                                    <option value="-1">Hepsi</option>
                                                    <option value="1" {{ request('odeme_durumu') === '1' ? 'selected' : '' }}>Tamamlandı</option>
                                                    <option value="0" {{ request('odeme_durumu') === '0' ? 'selected' : '' }}>Beklemede</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-4"><label for="araodemeSekli" class="col-form-label col-form-label-sm">Ödeme Şekli:</label></div>
                                            <div class="col-8">
                                                <select name="odeme_sekli" id="araodemeSekli" class="form-select form-select-sm">
                                                    <option value="0">Hepsi</option>
                                                    @foreach($tumOdemeSekilleri as $sekil)
                                                        <option value="{{ $sekil->id }}" {{ request('odeme_sekli') == $sekil->id ? 'selected' : '' }}>{{ $sekil->ad }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row mb-2 align-items-center">
                                            <div class="col-4"><label for="arailgilipersonel" class="col-form-label col-form-label-sm">Teknisyen:</label></div>
                                            <div class="col-8">
                                                <select name="ilgili_personel" id="arailgilipersonel" class="form-select form-select-sm">
                                                    <option value="0">Hepsi</option>
                                                    @foreach($teknisyenler as $personel)
                                                        <option value="{{ $personel->id }}" {{ request('ilgili_personel') == $personel->id ? 'selected' : '' }}>{{ $personel->ad }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        @php
                                            $tarih1Value = request('tarih1');
                                            $tarih2Value = request('tarih2');
                                            $bugun = \Carbon\Carbon::today()->format('Y-m-d');
                                            if ($tarih1Value && preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $tarih1Value)) {
                                                $tarih1Value = \Carbon\Carbon::createFromFormat('d.m.Y', $tarih1Value)->format('Y-m-d');
                                            } elseif (empty($tarih1Value)) {
                                                $tarih1Value = $bugun;
                                            }
                                            if ($tarih2Value && preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $tarih2Value)) {
                                                $tarih2Value = \Carbon\Carbon::createFromFormat('d.m.Y', $tarih2Value)->format('Y-m-d');
                                            } elseif (empty($tarih2Value)) {
                                                $tarih2Value = $bugun;
                                            }
                                        @endphp
                                        <div class="row mb-2">
                                            <div class="col-4"><label class="col-form-label col-form-label-sm">Gerçekleşme:</label></div>
                                            <div class="col-8">
                                                <input name="tarih1" type="date" id="araTarih1" class="form-control form-control-sm" value="{{ $tarih1Value }}">
                                                <input name="tarih2" type="date" id="araTarih2" class="form-control form-control-sm mt-1" value="{{ $tarih2Value }}">
                                                <div class="d-flex justify-content-between mt-1">
                                                    <a href="#" class="text-danger text-decoration-none" onclick="aramaTarihDegistir('{{ Carbon\Carbon::today()->subYear()->format('Y-m-d') }}','{{ Carbon\Carbon::today()->format('Y-m-d') }}'); return false;">Son 1 Yıl</a>
                                                    <a href="#" class="text-danger text-decoration-none" onclick="aramaTarihDegistir('{{ Carbon\Carbon::today()->subMonth()->format('Y-m-d') }}','{{ Carbon\Carbon::today()->format('Y-m-d') }}'); return false;">Son 1 Ay</a>
                                                </div>
                                                <div class="d-flex justify-content-between mt-1">
                                                    <a href="#" class="text-danger text-decoration-none" onclick="aramaTarihDegistir('{{ Carbon\Carbon::yesterday()->format('Y-m-d') }}','{{ Carbon\Carbon::yesterday()->format('Y-m-d') }}'); return false;">Dün</a>
                                                    <a href="#" class="text-danger text-decoration-none" onclick="aramaTarihDegistir('{{ Carbon\Carbon::today()->format('Y-m-d') }}','{{ Carbon\Carbon::today()->format('Y-m-d') }}'); return false;">Bugün</a>
                                                    <a href="#" class="text-danger text-decoration-none" onclick="aramaTarihDegistir('{{ Carbon\Carbon::tomorrow()->format('Y-m-d') }}','{{ Carbon\Carbon::tomorrow()->format('Y-m-d') }}'); return false;">Yarın</a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-primary btn-sm mt-3">Ara</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            {{-- Yeni Arama Bölümü Sonu --}}
                        </div> {{-- d-flex justify-content-end bitişi --}}
                        <div class="table-responsive">
                            @if($isPatron)
                            <div class="bulk-actions-container p-3 bg-light border-top border-bottom mb-3" style="display: none;">
                                <div class="row align-items-center">
                                    <div class="col-md-4">
                                        <span class="fw-bold">Seçilen Kayıtlar: <span class="selectedKasaCount">0</span></span>
                                    </div>
                                    <div class="col-md-5 d-flex align-items-center gap-2">
                                        <label for="bulkGerceklesmeTarihiTop" class="form-label form-label-sm mb-0">Yeni Gerçekleşme Tarihi:</label>
                                        <input type="date" class="form-control form-control-sm w-auto bulkGerceklesmeTarihi" id="bulkGerceklesmeTarihiTop" style="flex-grow: 1;">
                                    </div>
                                    <div class="col-md-3 text-end">
                                        <button type="button" class="btn btn-primary btn-sm bulkUpdateGerceklesmeTarihiBtn">
                                            <i class="feather-save me-1"></i> Toplu Güncelle
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @endif
                            <table class="table table-hover" id="kasaListTable">
                                <thead>
                                    <tr>
                                        <th>K.NO</th>
                                        <th>TARİH</th>
                                        <th>Ödeme Türü</th>
                                        <th>Açıklama</th>
                                        <th>ÖDEME ŞEKLİ</th>
                                        <th>Durum</th>
                                        <th>GERÇEKLEŞME TARİHİ</th>
                                        <th class="text-end">Tutar</th>
                                        <th class="text-center no-print" style="width: 50px;">
                                            @if($isPatron)
                                                <input type="checkbox" id="selectAllKasaHareketleri" title="Tümünü Seç/Kaldır">
                                            @endif
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @include('crm.kasa._kasa_table_rows', ['kasaHareketleri' => $kasaHareketleri, 'isPatron' => $isPatron])
                                </tbody>
                            </table>

                            <div class="py-2 mt-3 bg-light border-top border-bottom rounded">
                                <div class="d-flex">
                                    <ul class="list-group list-group-flush rounded" style="width: 350px; margin-left: auto; overflow: hidden;">
                                        <li class="list-group-item d-flex flex-column align-items-stretch"> {{-- flex-column ve align-items-stretch ekledim --}}
                                            <div class="d-flex justify-content-between w-100"> {{-- Ana toplam satırı --}}
                                                <span class="fw-bold me-auto">TOPLAM GELİR:&nbsp;</span>
                                                <span id="totalGelir" class="text-success fw-bold">{{ number_format($totalGelir, 2, ',', '.') }} TL</span>
                        </div>
                                            <div id="gelirDetaylariSubItems" class="w-100 ps-3 mt-1"> {{-- Alt detaylar için yeni container --}}
                                                @foreach($gelirDetaylariByOdemeSekli as $detay)
                                                    <div class="d-flex justify-content-between text-muted small py-1">
                                                        <span class="me-auto">{{ $detay['ad'] }}:</span>
                                                        <span>{{ number_format($detay['tutar'], 2, ',', '.') }} TL</span>
                    </div>
                                                @endforeach
                </div>
                                        </li>
                                        {{-- Önceki <li id="gelirDetaylariContainer"> kaldırıldı --}}
                                        <li class="list-group-item d-flex flex-column align-items-stretch">
                                            <div class="d-flex justify-content-between w-100">
                                                <span class="fw-bold me-auto">TOPLAM GİDER:&nbsp;</span>
                                                <span id="totalGider" class="text-danger fw-bold">{{ number_format($totalGider, 2, ',', '.') }} TL</span>
                                            </div>
                                            <div id="giderDetaylariSubItems" class="w-100 ps-3 mt-1">
                                                @foreach(($giderDetaylariByOdemeTuru ?? []) as $detay)
                                                    @if(($detay['tutar'] ?? 0) > 0)
                                                        <div class="d-flex justify-content-between text-muted small py-1">
                                                            <span class="me-auto">{{ $detay['ad'] }}:</span>
                                                            <span>{{ number_format($detay['tutar'], 2, ',', '.') }} TL</span>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center border-top pt-2">
                                            <div class="d-flex justify-content-between w-100">
                                                <span class="fw-bold me-auto">GENEL TOPLAM:&nbsp;</span>
                                                <span id="netToplam" class="{{ $netToplam >= 0 ? 'text-success' : 'text-danger' }} fw-bold">{{ number_format($netToplam, 2, ',', '.') }} TL</span>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            @if($isPatron)
                            <div class="bulk-actions-container p-3 bg-light border-top mt-3" style="display: none;">
                                <div class="row align-items-center">
                                    <div class="col-md-4">
                                        <span class="fw-bold">Seçilen Kayıtlar: <span class="selectedKasaCount">0</span></span>
                                    </div>
                                    <div class="col-md-5 d-flex align-items-center gap-2">
                                        <label for="bulkGerceklesmeTarihiBottom" class="form-label form-label-sm mb-0">Yeni Gerçekleşme Tarihi:</label>
                                        <input type="date" class="form-control form-control-sm w-auto bulkGerceklesmeTarihi" id="bulkGerceklesmeTarihiBottom" style="flex-grow: 1;">
                                    </div>
                                    <div class="col-md-3 text-end">
                                        <button type="button" class="btn btn-primary btn-sm bulkUpdateGerceklesmeTarihiBtn">
                                            <i class="feather-save me-1"></i> Toplu Güncelle
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @endif
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
    <!--! [Start] Kasa Hareketi Modal !-->
    <!--! ================================================================ !-->
    <div class="modal fade" id="kasaHareketiModal" tabindex="-1" aria-labelledby="kasaHareketiModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="kasaHareketiModalLabel">Kasa Hareketi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="kasaHareketiForm">
                    @csrf
                    <input type="hidden" id="hareketId" name="hareket_id">
                    <div class="modal-body">
                        <div id="kasaHareketiHataMesajlari" class="alert alert-danger d-none" role="alert"></div>

                        <div class="row g-3"> {{-- Ana satır: Genel ve Ödeme Detayları için --}}
                            {{-- Sol Sütun: Genel Bilgiler --}}
                            <div class="col-md-6">
                                <h6 class="mb-3 fw-semibold border-bottom pb-2">Genel Bilgiler</h6>
                                <div class="row mb-2 align-items-center">
                                    <label for="modalKasaKayitTarihi" class="col-sm-5 col-form-label col-form-label-sm">Oluşturulma Tarihi:</label>
                                    <div class="col-sm-7">
                                        <input type="text" class="form-control form-control-sm" id="modalKasaKayitTarihi" readonly disabled>
                                    </div>
                                </div>
                                <div class="row mb-2 align-items-center">
                                    <label for="modalKasaPersonel" class="col-sm-5 col-form-label col-form-label-sm">Personel <span class="text-danger">*</span></label>
                                    <div class="col-sm-7">
                                        <select class="form-select form-select-sm" id="modalKasaPersonel" name="personel_id" required>
                                            <option value="">Seçiniz...</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row mb-2 align-items-center">
                                    <label for="modalKasaServisID" class="col-sm-5 col-form-label col-form-label-sm">İlişkili Servis ID</label>
                                    <div class="col-sm-7">
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control form-control-sm" id="modalKasaServisID" name="servis_id" readonly>
                                            <a href="#" id="modalKasaServisLink" class="btn btn-outline-secondary btn-sm" target="_blank" rel="noopener noreferrer" title="Servis detayını yeni pencerede aç" style="opacity: 0.5; pointer-events: none;"><i class="feather-external-link"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Sağ Sütun: Ödeme Detayları --}}
                            <div class="col-md-6">
                                <h6 class="mb-3 fw-semibold border-bottom pb-2">Ödeme Detayları</h6>
                                <div class="row mb-2 align-items-center">
                                    <label for="modalKasaOdemeTuru" class="col-sm-5 col-form-label col-form-label-sm">Ödeme Türü <span class="text-danger">*</span></label>
                                    <div class="col-sm-7">
                                        <select class="form-select form-select-sm" id="modalKasaOdemeTuru" name="odeme_turu_id" required>
                                            <option value="">Seçiniz...</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row mb-2 align-items-center">
                                    <label for="modalKasaOdemeSekli" class="col-sm-5 col-form-label col-form-label-sm">Ödeme Şekli <span class="text-danger">*</span></label>
                                    <div class="col-sm-7">
                                        <select class="form-select form-select-sm" id="modalKasaOdemeSekli" name="odeme_sekli_id" required>
                                            <option value="">Seçiniz...</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row mb-2 align-items-center">
                                    <label for="modalKasaOdemeYonu" class="col-sm-5 col-form-label col-form-label-sm">Ödeme Yönü <span class="text-danger">*</span></label>
                                    <div class="col-sm-7">
                                        <select class="form-select form-select-sm" id="modalKasaOdemeYonu" name="yon" required>
                                            <option value="1">Gelen Ödeme (Gelir)</option>
                                            <option value="-1">Giden Ödeme (Gider)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row mb-2 align-items-center">
                                    <label for="modalKasaTutar" class="col-sm-5 col-form-label col-form-label-sm">Tutar <span class="text-danger">*</span></label>
                                    <div class="col-sm-7">
                                        <input type="number" step="0.01" class="form-control form-control-sm" id="modalKasaTutar" name="tutar" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-3">

                        {{-- Alt Bölüm 1: İşlem Zamanı ve Durumu --}}
                        <h6 class="mb-3 fw-semibold border-bottom pb-2">İşlem Zamanı ve Durumu</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-3">
                                <label for="modalKasaTarih" class="form-label form-label-sm">İşlem Tarihi <span class="text-danger">*</span></label>
                                <input type="date" class="form-control form-control-sm" id="modalKasaTarih" name="tarih" required>
                            </div>
                            <div class="col-md-3">
                                <label for="modalKasaSaat" class="form-label form-label-sm">İşlem Saati <span class="text-danger">*</span></label>
                                <input type="time" class="form-control form-control-sm" id="modalKasaSaat" name="saat" required>
                            </div>
                            <div class="col-md-3">
                                <label for="modalKasaGerceklesmeTarihi" class="form-label form-label-sm">Gerçekleşme Tarihi</label>
                                <input type="date" class="form-control form-control-sm" id="modalKasaGerceklesmeTarihi" name="islem_tarihi">
                            </div>
                            <div class="col-md-3">
                                <label for="modalKasaDurum" class="form-label form-label-sm">Ödeme Durumu <span class="text-danger">*</span></label>
                                <select class="form-select form-select-sm" id="modalKasaDurum" name="gerceklesme" required>
                                    <option value="1">Tamamlandı</option>
                                    <option value="0">Beklemede</option>
                                </select>
                            </div>
                        </div>
                        
                        {{-- Alt Bölüm 2: Açıklama --}}
                        <h6 class="mb-2 fw-semibold border-bottom pb-1">Açıklama</h6>
                        <div class="row">
                            <div class="col-md-12">
                                <textarea class="form-control form-control-sm" id="modalKasaAciklama" name="aciklama" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger btn-sm me-auto" id="kasaSilBtn" onclick="silKasaKaydini()" style="display: none;"><i class="feather feather-trash-2 me-1"></i>KASAYI SİL</button>
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Kapat</button>
                        <button type="submit" class="btn btn-primary btn-sm" id="kasaKaydetBtn">Kaydet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!--! ================================================================ !-->
    <!--! [End] Kasa Hareketi Modal !-->
    <!--! ================================================================ !-->
@endsection

@push('page_specific_vendor_js')
{{-- <script src="{{ asset('crm_assets/vendors/js/dataTables.min.js') }}"></script>
<script src="{{ asset('crm_assets/vendors/js/dataTables.bs5.min.js') }}"></script> --}}
@endpush

@push('page_specific_main_scripts')
<script src="{{ asset('crm_assets/js/helpers.js') }}"></script>
<script>
    const loggedInUserPozId = {{ Auth::check() ? Auth::user()->poz_id : 'null' }};
    const loggedInUserId = {{ Auth::check() ? Auth::user()->id : 'null' }};
    const patronPozisyonId = 1071; // Patron pozisyon ID'si
    const muhasebePozisyonId = 1080; // Muhasebe pozisyon ID'si
    const tsrnTeknisyenPozisyonId = 1077; // TŞRN Teknisyen pozisyon ID'si
    const isPatron = (loggedInUserPozId === patronPozisyonId);
    const isPatronOrMuhasebe = (loggedInUserPozId === patronPozisyonId || loggedInUserPozId === muhasebePozisyonId);
    const isTsrnTeknisyen = (loggedInUserPozId === tsrnTeknisyenPozisyonId);

    // === YENİ: Kasa Kaydını Silme (Soft Delete) Fonksiyonu ===
    function silKasaKaydini() {
            var hareketId = $('#hareketId').val();
            if (!hareketId) {
                Swal.fire('Hata!', 'Silinecek kasa hareketi ID bilgisi bulunamadı.', 'error');
                return;
            }

            Swal.fire({
                title: 'Emin misiniz?',
                html: "<b>#" + hareketId + "</b> ID'li kasa kaydı silinecek ve listede görünmeyecektir!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Evet, Sil!',
                cancelButtonText: 'İptal'
            }).then((result) => {
                if (result.isConfirmed || result.value) {
                    const $silButonu = $('#kasaSilBtn');
                    const originalButtonText = $silButonu.html();
                    $silButonu.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Siliniyor...');

                    $.ajax({
                        type: 'POST',
                        url: '/genelkasa/' + hareketId + '/soft-delete',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Silindi!', response.message, 'success').then(() => {
                                    bootstrap.Modal.getInstance(document.getElementById('kasaHareketiModal')).hide();
                                    location.reload(); // Şimdilik sayfayı yenile
                                });
                            } else {
                                Swal.fire('Hata!', response.message || 'Kasa kaydı silinirken bir sorun oluştu.', 'error');
                            }
                        },
                        error: function(xhr) {
                            var errorMsg = 'Kasa kaydı silinirken bir sunucu hatası oluştu.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                            Swal.fire('Hata!', errorMsg, 'error');
                        },
                        complete: function() {
                            $silButonu.prop('disabled', false).html(originalButtonText);
                        }
                    });
                }
            });
        }

    $(document).ready(function() {
        console.log("Doküman hazırlandı: Kasa index sayfası."); // Yeni kontrol logu
        // Sayfa yüklendiğinde ve stiller uygulandığında içeriği göster
        $('#mainContentWrapper').show();
        console.log("mainContentWrapper gösterildi.");

        // Tümünü seç/kaldır onay kutusu için olay dinleyici
        $('#selectAllKasaHareketleri').on('change', function() {
            const isChecked = $(this).prop('checked');
            $('.kasa-checkbox').prop('checked', isChecked).trigger('change'); // Her bir checkbox'ı tetikle
        });

        // Her bir kasa hareketi onay kutusu için olay dinleyici
        $(document).on('click', '.kasa-checkbox', function(e) {
            e.stopPropagation(); // Olayın satıra yayılmasını engelle
        });

        $(document).on('change', '.kasa-checkbox', function(e) {
            // e.stopPropagation(); // Artık click olayında durduruluyor
            const id = $(this).data('id');
            if ($(this).prop('checked')) {
                if (!selectedKasaHareketleri.includes(id)) {
                    selectedKasaHareketleri.push(id);
                }
            } else {
                selectedKasaHareketleri = selectedKasaHareketleri.filter(item => item !== id);
                // Eğer bir tanesi kaldırılırsa, tümünü seç kutusunun işaretini kaldır
                $('#selectAllKasaHareketleri').prop('checked', false);
            }
            updateBulkActionsVisibility();
        });

        // İlk yüklemede gizli olduğundan emin ol
        updateBulkActionsVisibility();

        // Toplu tarih alanlarını senkron tut
        $(document).on('change', '.bulkGerceklesmeTarihi', function() {
            const val = $(this).val();
            $('.bulkGerceklesmeTarihi').val(val);
        });

        // Toplu Güncelleme butonu için olay dinleyici
        $(document).on('click', '.bulkUpdateGerceklesmeTarihiBtn', function() {
            console.log("Toplu Güncelle butonu tıklandı."); // Yeni kontrol logu
            if (selectedKasaHareketleri.length === 0) {
                Swal.fire('Uyarı!', 'Lütfen güncellenecek kasa hareketlerini seçin.', 'warning');
                return;
            }

            const newGerceklesmeTarihi = $('.bulkGerceklesmeTarihi').filter(function() {
                return $(this).val();
            }).first().val();
            if (!newGerceklesmeTarihi) {
                Swal.fire('Uyarı!', 'Lütfen yeni bir gerçekleşme tarihi seçin.', 'warning');
                return;
            }

            Swal.fire({
                title: 'Emin misiniz?',
                text: `${selectedKasaHareketleri.length} adet kasa kaydının gerçekleşme tarihi ${newGerceklesmeTarihi} olarak güncellenecektir.`, // formatToDottedDate kullanıldı
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Evet, Güncelle!',
                cancelButtonText: 'İptal'
            }).then((result) => {
                console.log("Swal.fire sonucu: ", result); // Mevcut log
                console.log("result.isConfirmed: ", result.isConfirmed); // Yeni detaylı log
                console.log("result.value: ", result.value); // Yeni detaylı log

                if (result.isConfirmed || result.value) {
                    console.log("Onay bloğuna girildi."); // Mevcut kontrol logu
                    console.log("Seçilen ID'ler:", selectedKasaHareketleri); // Mevcut log
                    console.log("Seçilen ID sayısı:", selectedKasaHareketleri.length); // Yeni log
                    console.log("Yeni Gerçekleşme Tarihi:", newGerceklesmeTarihi); // Mevcut log
                    
                    try {
                        const $buttons = $('.bulkUpdateGerceklesmeTarihiBtn');
                        $buttons.each(function() {
                            const $btn = $(this);
                            $btn.data('original-html', $btn.html());
                            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Güncelleniyor...');
                        });

                        const bulkUpdateUrl = '{{ route('kasa.bulkUpdateGerceklesmeTarihi') }}';
                        const postData = {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            hareket_ids: selectedKasaHareketleri,
                            gerceklesme_tarihi: newGerceklesmeTarihi
                        };

                        console.log("Toplu Güncelleme AJAX isteği gönderiliyor...");
                        console.log("URL:", bulkUpdateUrl);
                        console.log("Data:", postData);

                        $.ajax({
                            url: bulkUpdateUrl,
                            type: 'POST',
                            data: postData,
                            success: function(response) {
                                console.log("Toplu Güncelleme Başarılı Yanıt:", response);
                                if (response.success) {
                                    Swal.fire('Başarılı!', response.message, 'success').then(() => {
                                        arama(); // Tabloyu yeniden yükle
                                    });
                                } else {
                                    Swal.fire('Hata!', response.message || 'Bir hata oluştu.', 'error');
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error("Toplu Güncelleme AJAX Hatası:", status, error, xhr.responseText, xhr);
                                let errorMsg = 'Toplu güncelleme sırasında bir hata oluştu.';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMsg = xhr.responseJSON.message;
                                }
                                Swal.fire('Hata!', errorMsg, 'error');
                            },
                            complete: function() {
                                $buttons.each(function() {
                                    const $btn = $(this);
                                    $btn.prop('disabled', false).html($btn.data('original-html'));
                                });
                            }
                        });
                    } catch (ajaxError) {
                        console.error("AJAX çağrısı sırasında senkron bir hata oluştu:", ajaxError);
                        Swal.fire('Hata!', 'Beklenmedik bir hata oluştu. Lütfen konsolu kontrol edin.', 'error');
                        $buttons.each(function() {
                            const $btn = $(this);
                            $btn.prop('disabled', false).html($btn.data('original-html'));
                        });
                    }
                    } else {
                    console.log("Onay bloğuna girilmedi. result.isConfirmed false."); // Yeni log
                }
            });
        });

        /*
        $('#kasaListTable').DataTable({
             "language": {
                "emptyTable": "Kayıt bulunamadı."
             },
             "paging": false, 
             "info": false,
             "searching": false, // DataTables'in kendi aramasını kapat
             "order": [[ 1, "desc" ]],
             "createdRow": function( row, data, dataIndex ) {
                var yon = $(row).attr('data-yon'); 
                var tutarCell = $(row).find('td:last-child');
                var odemeTuruCell = $(row).find('td:eq(2)'); // Ödeme Türü hücresi (0-indexed)

                tutarCell.removeClass('text-success text-danger'); 
                if (yon == 1) {
                    tutarCell.addClass('text-success');
                } else if (yon == -1) {
                    tutarCell.addClass('text-danger');
                }

                odemeTuruCell.css('font-weight', 'bold'); // Ödeme Türü sütununu bold yap
            }
        });
        */

        var kasaHareketiModal = new bootstrap.Modal(document.getElementById('kasaHareketiModal'));
        // var kasaDataTable = $('#kasaListTable').DataTable();
        
        // populateKasaModalDropdowns, fetchKasaFormDataIfNeeded, updateKasaModalByOdemeTuru fonksiyonlarını global alana taşıyorum.

        // Satır tıklama ile modal açma (düzenleme için)
        $('#kasaListTable').on('click', 'tbody tr.clickable-row', function (e) {
            // Eğer tıklanan element bir checkbox ise, modalın açılmasını engelle
            if ($(e.target).hasClass('kasa-checkbox')) {
                e.stopPropagation(); // Olayın daha fazla yayılmasını engelle (gerekmeyebilir ama ekstra önlem)
                return; 
            }
            console.log("Satır tıklama olayı tetiklendi."); // Yeni log
            var hareketId = $(this).data('hareket-id');
            console.log("Satır tıklandı, Hareket ID:", hareketId); // Log 1
            if (!hareketId) {
                console.error("Hareket ID alınamadı!");
                return;
            }

            $('#kasaHareketiForm')[0].reset();
            $('#kasaHareketiHataMesajlari').addClass('d-none').html('');
            $('#hareketId').val(hareketId);
            $('#kasaHareketiModalLabel').text('Kasa Hareketini Düzenle (#' + hareketId + ')');
            $('#kasaKaydetBtn').text('DEĞİŞİKLİKLERİ KAYDET');
            $('#kasaSilBtn').show();
            console.log("AJAX isteği öncesi, URL: /kasa/" + hareketId); // Log 2

            $.ajax({
                url: '/genelkasa/' + hareketId, // KasaController@show route'una denk gelir (resource controller için)
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    console.log("AJAX Başarılı Yanıt:", response); // Log 3
                    if (response.success) {
                        var dataKasaHareketi = response.kasaHareketi; // Ana kasa hareketi verisi
                        
                        // Global dizileri her zaman taze verilerle güncelle
                        globalKasaPersoneller = response.personeller || [];
                        globalKasaTeknisyenler = response.teknisyenler || [];
                        globalKasaOdemeTurleri = response.odemeTurleri || []; // Bu artık controller'dan yon ve muhattap içermeli
                        globalKasaOdemeSekilleri = response.odemeSekilleri || [];
                        
                        populateKasaModalDropdowns(globalKasaPersoneller, globalKasaOdemeTurleri, globalKasaOdemeSekilleri);

                        // Modal alanlarını kasaHareketi verisinden doldur
                        $('#modalKasaKayitTarihi').val(dataKasaHareketi.created_at ? new Date(dataKasaHareketi.created_at).toLocaleDateString('tr-TR', { day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit'}) : 'N/A');
                        $('#modalKasaPersonel').val(dataKasaHareketi.personel_id);
                        $('#modalKasaOdemeTuru').val(dataKasaHareketi.odeme_turu_id);
                        $('#modalKasaOdemeSekli').val(dataKasaHareketi.odeme_sekli_id);
                        
                        // Ödeme Yönünü, kasa hareketinin kendi odeme_yonu sütunundan al
                        if (typeof dataKasaHareketi.odeme_yonu !== 'undefined' && dataKasaHareketi.odeme_yonu !== null) {
                            // console.log('index.blade.php (JS) - dataKasaHareketi.odeme_yonu:', dataKasaHareketi.odeme_yonu); // Kaldırıldı
                            $('#modalKasaOdemeYonu').val(dataKasaHareketi.odeme_yonu);
                            console.log('Düzenleme Modalı: Kasa Hareketinden Ödeme Yönü yüklendi:', dataKasaHareketi.odeme_yonu);
                        } else {
                            // console.log('index.blade.php (JS) - dataKasaHareketi.odeme_yonu undefined/null, using default 1.'); // Kaldırıldı
                            $('#modalKasaOdemeYonu').val('1'); // Varsayılan (veya hata durumu)
                            console.log('Düzenleme Modalı: Kasa Hareketinden Ödeme Yönü alınamadı, varsayılan (1) ayarlandı.');
                        }

                        $('#modalKasaDurum').val(dataKasaHareketi.gerceklesme);
                        $('#modalKasaTarih').val(dataKasaHareketi.tarih ? dataKasaHareketi.tarih.split(' ')[0] : '');
                        var saatParts = dataKasaHareketi.saat ? dataKasaHareketi.saat.split(':') : [];
                        $('#modalKasaSaat').val(saatParts.length >= 2 ? saatParts[0] + ':' + saatParts[1] : '');
                        $('#modalKasaGerceklesmeTarihi').val(dataKasaHareketi.islem_tarihi ? dataKasaHareketi.islem_tarihi.split(' ')[0] : '');
                        $('#modalKasaTutar').val(parseFloat(dataKasaHareketi.tutar || 0).toFixed(2));
                        $('#modalKasaServisID').val(dataKasaHareketi.servis_id || '');
                        if (dataKasaHareketi.servis_id) {
                            $('#modalKasaServisLink').attr('href', '/servisler?open_servis_id=' + dataKasaHareketi.servis_id).css({ opacity: '1', pointerEvents: 'auto' });
                        } else {
                            $('#modalKasaServisLink').attr('href', '#').css({ opacity: '0.5', pointerEvents: 'none' });
                        }
                        $('#modalKasaAciklama').val(dataKasaHareketi.aciklama);
                        
                        // Alan görünürlüklerini ve YENİ KAYITTA yönü ayarla
                        updateKasaModalByOdemeTuru(dataKasaHareketi.odeme_turu_id); 
                        var hedefPersonelId = dataKasaHareketi.ilgili_personel_id || dataKasaHareketi.personel_id;
                        if (hedefPersonelId) {
                            $('#modalKasaPersonel').val(hedefPersonelId);
                        }
                        // updateKasaModalByOdemeTuru artık düzenleme modunda YÖN'e dokunmayacak,
                        // çünkü yukarıda dataKasaHareketi.yon ile set edildi.

                        // Düzenleme modunda: Patron ve muhasebe harici tüm alanlar (tarih dahil) devre dışı
                        if (!isPatronOrMuhasebe) {
                            $('#modalKasaOdemeTuru').prop('disabled', true);
                            $('#modalKasaServisID').prop('readonly', true);
                            $('#modalKasaOdemeSekli').prop('disabled', true);
                            $('#modalKasaOdemeYonu').prop('disabled', true);
                            $('#modalKasaTutar').prop('readonly', true);
                            $('#modalKasaTarih').prop('readonly', true);
                            $('#modalKasaSaat').prop('readonly', true);
                            $('#modalKasaGerceklesmeTarihi').prop('readonly', true);
                            $('#modalKasaDurum').prop('disabled', true);
                            $('#modalKasaAciklama').prop('readonly', true);
                            $('#kasaSilBtn').hide();
                            $('#kasaKaydetBtn').hide();
                        } else {
                            // Patron veya muhasebe: tüm alanlar (tarih dahil) düzenlenebilir
                            $('#modalKasaOdemeTuru').prop('disabled', false);
                            $('#modalKasaServisID').prop('readonly', false);
                            $('#modalKasaOdemeSekli').prop('disabled', false);
                            $('#modalKasaOdemeYonu').prop('disabled', false);
                            $('#modalKasaTutar').prop('readonly', false);
                            $('#modalKasaTarih').prop('readonly', false);
                            $('#modalKasaSaat').prop('readonly', false);
                            $('#modalKasaGerceklesmeTarihi').prop('readonly', false);
                            $('#modalKasaDurum').prop('disabled', false);
                            $('#modalKasaAciklama').prop('readonly', false);
                            $('#kasaSilBtn').show(); // Kasayı Sil butonunu göster
                            $('#kasaKaydetBtn').show(); // Kaydet butonunu göster
                        }

                        kasaHareketiModal.show();
                        console.log("Modal gösterildi."); // Yeni log
                    } else {
                        Swal.fire('Hata!', response.message || 'Kasa hareket detayları yüklenemedi.', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Hatası:", status, error, xhr.responseText); // Log 4
                    Swal.fire('Hata!', 'Kasa hareket detayları yüklenirken sunucu hatası oluştu.', 'error');
                }
            });
        });

        // Yeni Kasa Hareketi butonu ile modal açma (ekleme için)
        $('button[data-bs-target="#kasaHareketiModal"]').on('click', function() {
            $('#kasaHareketiForm')[0].reset();
            $('#kasaHareketiHataMesajlari').addClass('d-none').html('');
            $('#hareketId').val('');
            $('#kasaHareketiModalLabel').text('Yeni Kasa Hareketi Ekle');
            $('#kasaKaydetBtn').text('Kaydet').show(); // Yeni kayıt için Kaydet butonunu göster
            $('#kasaSilBtn').hide();
            $('#modalKasaServisLink').attr('href', '#').css({ opacity: '0.5', pointerEvents: 'none' });

            // Tüm düzenleme modunda disabled/readonly yapılan alanları varsayılan haline getir
            $('#modalKasaOdemeTuru').prop('disabled', false);
            $('#modalKasaServisID').prop('readonly', false);
            $('#modalKasaOdemeSekli').prop('disabled', false);
            $('#modalKasaOdemeYonu').prop('disabled', false);
            $('#modalKasaTutar').prop('readonly', false);
            $('#modalKasaTarih').prop('readonly', false);
            $('#modalKasaSaat').prop('readonly', false);
            $('#modalKasaGerceklesmeTarihi').prop('readonly', false);
            $('#modalKasaDurum').prop('disabled', false);
            $('#modalKasaAciklama').prop('readonly', false);
            // Patron ve muhasebe harici: ödeme tarih/saat alanlarına müdahale edilemesin
            if (!isPatronOrMuhasebe) {
                $('#modalKasaTarih').prop('readonly', true);
                $('#modalKasaSaat').prop('readonly', true);
                $('#modalKasaGerceklesmeTarihi').prop('readonly', true);
            } 
            
            fetchKasaFormDataIfNeeded(); 
            updateKasaModalByOdemeTuru($('#modalKasaOdemeTuru').val());

            // Taşeron teknisyenlerde ödeme yönü sabit: Gelen Ödeme (gelir)
            if (isTsrnTeknisyen) {
                $('#modalKasaOdemeYonu').val('1').prop('disabled', true);
            } else {
                $('#modalKasaOdemeYonu').prop('disabled', false);
            }

            // Ödeme Şekli için varsayılan olarak Nakit (ID:1) seçili gelsin
            var nakitOdemeSekliId = 1; // 'Nakit' için ID, seeder'a göre varsayım
            var $odemeSekliSelect = $('#modalKasaOdemeSekli');
            // Dropdown'ın seçeneklerinin yüklenmesini bekle (fetchKasaFormDataIfNeeded içinde populate ediliyor)
            // Eğer globalKasaOdemeSekilleri zaten doluysa direkt set edebiliriz.
            if (globalKasaOdemeSekilleri.length > 0 && globalKasaOdemeSekilleri.find(s => s.id == nakitOdemeSekliId)) {
                $odemeSekliSelect.val(nakitOdemeSekliId);
            } else if (globalKasaOdemeSekilleri.length === 0) {
                // Eğer fetchKasaFormDataIfNeeded içinde seçenekler yükleniyorsa ve async:false ise,
                // bu noktada seçenekler dolmuş olmalı. Tekrar kontrol edip set edelim.
                var nakitOption = $odemeSekliSelect.find('option').filter(function () { 
                    return $(this).text().toLowerCase() === 'nakit'; 
                }).val();
                if (nakitOption) {
                    $odemeSekliSelect.val(nakitOption);
                } else {
                    // Alternatif olarak, eğer ID biliniyorsa ve seçenekler yüklendiyse:
                    // $odemeSekliSelect.val(nakitOdemeSekliId);
                }
            }
            // En güvenlisi, `fetchKasaFormDataIfNeeded` sonrası global dizinin dolduğundan emin olup ID ile set etmektir.

            var today = new Date();
            var day = String(today.getDate()).padStart(2, '0');
            var month = String(today.getMonth() + 1).padStart(2, '0'); // Ocak 0 olduğu için +1
            var year = today.getFullYear();
            var hours = String(today.getHours()).padStart(2, '0');
            var minutes = String(today.getMinutes()).padStart(2, '0');

            var todayFormattedDate = year + '-' + month + '-' + day;
            var currentTimeFormatted = hours + ':' + minutes;
            var currentDateTimeFormatted = todayFormattedDate + ' ' + currentTimeFormatted;

            console.log("Yeni Kayıt - Oluşturulma Tarihi:", currentDateTimeFormatted);
            $('#modalKasaKayitTarihi').val(currentDateTimeFormatted);
            
            console.log("Yeni Kayıt - İşlem Tarihi:", todayFormattedDate);
            $('#modalKasaTarih').val(todayFormattedDate);
            
            console.log("Yeni Kayıt - İşlem Saati:", currentTimeFormatted);
            $('#modalKasaSaat').val(currentTimeFormatted);
            
            console.log("Yeni Kayıt - Gerçekleşme Tarihi:", todayFormattedDate);
            $('#modalKasaGerceklesmeTarihi').val(todayFormattedDate);
            
            // Yeni kayıt açıldığında Ödeme Yönünü varsayılan olarak Gelen Ödeme (1) olarak ayarla
            $('#modalKasaOdemeYonu').val('1');
            
            updateKasaModalByOdemeTuru($('#modalKasaOdemeTuru').val());

            // Patron/muhasebe harici: tarih alanları zaten readonly (yukarıda)
            // Patron olmayanlar için Ödeme Türü ve Personel alanlarını ayarla
            if (!isPatron) {
                // Ödeme Türü: Servis İşlemleri otomatik seçili ve disable
                // 'Servis İşlemleri' ID'sini burada dinamik olarak bulmalıyız.
                // Şimdilik varsayılan bir ID kullanalım, sonra KasaController'dan alacak şekilde düzeltiriz.
                // fetchKasaFormDataIfNeeded içinde globalKasaOdemeTurleri dolduktan sonra burada set etmeliyiz.
                $('#modalKasaOdemeTuru').prop('disabled', true);

                // Personel: Giriş yapan personel otomatik seçili ve disable
                $('#modalKasaPersonel').val(loggedInUserId).prop('disabled', true);

                // İlişkili Servis ID: Sadece Servis İşlemleri seçiliyse readonly kaldırılabilir
                // (Ama sadece kendi servisleri için geçerli olacak)
                $('#modalKasaServisID').prop('readonly', false);
            } else {
                $('#modalKasaOdemeTuru').prop('disabled', false);
                $('#modalKasaPersonel').prop('disabled', false);
                $('#modalKasaServisID').prop('readonly', true); // Normalde patronlar için readonly kalabilir veya ihtiyaca göre değişir
            }
        });

        $('#modalKasaOdemeTuru').on('change', function() {
            updateKasaModalByOdemeTuru($(this).val());
            // Eğer Patron değilse, Ödeme Türü değişse bile disable kalsın
            if (!isPatron) {
                $('#modalKasaOdemeTuru').prop('disabled', true);
            }
        });

        // Kasa Hareketi Formu Gönderimi (AJAX ile store/update)
        $('#kasaHareketiForm').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var hareketId = $('#hareketId').val();
            var url = hareketId ? `/genelkasa/${hareketId}` : "{{ route('kasa.store') }}";
            var formData = form.serializeArray();
            
            if (hareketId) {
                formData.push({name: '_method', value: 'PUT'});
            }
            // Eğer Ödeme Türü selectbox'ı disabled ise, submit öncesi enable yap ve hidden input ekle
            let odemeTuruDisabled = false;
            let $odemeTuruSelect = $('#modalKasaOdemeTuru');
            if ($odemeTuruSelect.prop('disabled')) {
                odemeTuruDisabled = true;
                // Selectbox'ı geçici olarak etkinleştir ki değeri form data'sına eklensin
                // formData.push({ name: $odemeTuruSelect.attr('name'), value: $odemeTuruSelect.val() }); // Manuel ekleme
                // Daha basit: direkt disable kaldır, sonra geri ekle
                $odemeTuruSelect.prop('disabled', false); // Bu zaten vardı, kalsın.
            }

            // Eğer personel selectbox'ı disabled ise, submit öncesi enable yap
            let personelDisabled = false;
            let $personelSelect = $('#modalKasaPersonel');
            if ($personelSelect.prop('disabled')) {
                personelDisabled = true;
                $personelSelect.prop('disabled', false);
            }

            // FormData'ya manuel olarak disabled alanların değerlerini ekle
            // serializeArray() sonrası ve $.param() öncesi yapılmalı.
            var formArrayData = form.serializeArray();

            // Ödeme yönü: disabled olduğunda serializeArray'a dahil olmaz; her zaman güncel değeri açıkça ekleyelim
            var odemeYonuVal = $('#modalKasaOdemeYonu').val();
            formArrayData = formArrayData.filter(function(item) { return item.name !== 'yon' && item.name !== 'odeme_yonu'; });
            formArrayData.push({ name: 'yon', value: odemeYonuVal });
            formArrayData.push({ name: 'odeme_yonu', value: odemeYonuVal });

            if (odemeTuruDisabled) {
                formArrayData.push({ name: $odemeTuruSelect.attr('name'), value: $odemeTuruSelect.val() });
            }
            if (personelDisabled) {
                formArrayData.push({ name: $personelSelect.attr('name'), value: $personelSelect.val() });
            }

            var serializedFormData = $.param(formArrayData);
            var requestType = hareketId ? 'PUT' : 'POST'; // Düzenleme ise PUT, yeni kayıt ise POST

            console.log('Gönderilecek Ödeme Yönü değeri:', $('#modalKasaOdemeYonu').val()); // Yeni log
            console.log('Gönderilecek Form Data:', serializedFormData); // Yeni log

            var submitButton = form.find('button[type="submit"]');
            var originalButtonText = submitButton.html();
            var hataMesajlariDiv = $('#kasaHareketiHataMesajlari');

            submitButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Kaydediliyor...');
            hataMesajlariDiv.addClass('d-none').html('');
            form.find('.is-invalid').removeClass('is-invalid');
            form.find('.invalid-feedback').remove();

            $.ajax({
                type: requestType, // POST yerine dinamik requestType kullan
                url: url,
                data: serializedFormData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        kasaHareketiModal.hide();
                        Swal.fire('Başarılı!', response.message, 'success');

                        var hareket = response.kasaHareketi;
                        var odemeTuruAdi = response.odemeTuruAdi || (hareket.odeme_turu ? hareket.odeme_turu.ad : 'N/A');
                        var odemeSekliAdi = response.odemeSekliAdi || (hareket.odeme_sekli ? hareket.odeme_sekli.ad : 'N/A');
                        var aciklamaGosterilecek = response.aciklama_gosterilecek || 'N/A';

                        var tutarFormatted = parseFloat(hareket.tutar || 0).toLocaleString('tr-TR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' TL';

                        var rowData = [
                            `<a href="#" class="fw-bold">#${hareket.id}</a>`,
                            hareket.tarih ? new Date(hareket.tarih + 'T' + (hareket.saat || '00:00:00')).toLocaleString('tr-TR', { day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit' }) : 'N/A',
                            odemeTuruAdi,
                            aciklamaGosterilecek,
                            odemeSekliAdi,
                            hareket.gerceklesme == 1 ? '<span class="badge bg-soft-success text-success">Tamamlandı</span>' : (hareket.gerceklesme === 0 ? '<span class="badge bg-soft-warning text-warning">Beklemede</span>' : 'N/A'),
                            hareket.islem_tarihi ? new Date(hareket.islem_tarihi).toLocaleDateString('tr-TR', {day:'2-digit', month:'2-digit', year:'numeric'}) : 'N/A',
                            tutarFormatted
                        ];

                        // Modal kapandıktan sonra listenin yenilenmesi için arama fonksiyonunu çağırabiliriz.
                        arama(); // Tabloyu AJAX ile yeniden yükle

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
                        }
                        else {
                            hataMesajlariDiv.html('<ul><li>' + (response.message || 'Bilinmeyen bir hata oluştu.') + '</li></ul>').removeClass('d-none');
                            Swal.fire('Hata!', response.message || 'Bir sorun oluştu.', 'error');
                        }
                    }
                },
                error: function(xhr) {
                    var errorMsg = 'Bir sunucu hatası oluştu.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                        // Özel hata mesajını doğrudan Swal ile göster
                        Swal.fire('Hata!', errorMsg, 'error');
                        // Eğer spesifik hatalar varsa onları da göstermek için ek kontrol
                        if (xhr.responseJSON.errors) {
                             var errorHtml = '<ul>';
                            $.each(xhr.responseJSON.errors, function(key, value) {
                                errorHtml += '<li>' + value[0] + '</li>';
                                var input = form.find(`[name="${key}"]`);
                                input.addClass('is-invalid');
                            });
                            errorHtml += '</ul>';
                            hataMesajlariDiv.html(errorHtml).removeClass('d-none');
                        }
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                         var errorHtml = '<ul>';
                        $.each(xhr.responseJSON.errors, function(key, value) {
                            errorHtml += '<li>' + value[0] + '</li>';
                            var input = form.find(`[name="${key}"]`);
                            input.addClass('is-invalid');
                        });
                        errorHtml += '</ul>';
                        hataMesajlariDiv.html(errorHtml).removeClass('d-none');
                         errorMsg = 'Lütfen formdaki hataları düzeltin.';
                    Swal.fire('Hata!', errorMsg, 'error');
                    } else {
                        Swal.fire('Hata!', errorMsg, 'error');
                    }
                },
                complete: function() {
                    submitButton.prop('disabled', false).html(originalButtonText);
                    // Submit tamamlandıktan sonra Ödeme Türü selectbox'ı tekrar disable yap
                    if (odemeTuruDisabled) {
                        $odemeTuruSelect.prop('disabled', true);
                    }
                    // Submit tamamlandıktan sonra Personel selectbox'ı tekrar disable yap
                    if (personelDisabled) {
                        $personelSelect.prop('disabled', true);
                    }
                }
            });
        });

        

        // Datepicker entegrasyonu (jQuery UI veya benzeri bir kütüphane varsayımıyla)
        $(function() {
            $(".date-pick").datepicker({
                dateFormat: 'dd.mm.yy',
                changeMonth: true,
                changeYear: true
            });
        });

        // Sayfa yüklendiğinde mevcut arama parametrelerini inputlara yerleştir ve arama yap (AJAX ile)
        (function() {
            console.log("Sayfa yüklendiğinde arama parametreleri kontrol ediliyor.");
            var urlParams = new URLSearchParams(window.location.search);
            
            if (urlParams.has('odenentutar')) {
                $('#aratxt').val(urlParams.get('odenentutar'));
            }
            if (urlParams.has('odeme_turu')) {
                $('#odemeTur').val(urlParams.get('odeme_turu'));
            }
            if (urlParams.has('odeme_yonu')) {
                $('#odemeyonu').val(urlParams.get('odeme_yonu'));
            }
            if (urlParams.has('odeme_durumu')) {
                $('#odemedurum').val(urlParams.get('odeme_durumu'));
            }
            if (urlParams.has('odeme_sekli')) {
                $('#araodemeSekli').val(urlParams.get('odeme_sekli'));
            }
            if (urlParams.has('ilgili_personel')) {
                $('#arailgilipersonel').val(urlParams.get('ilgili_personel'));
            }
            if (urlParams.has('tarih1')) {
                $('#araTarih1').val(urlParams.get('tarih1'));
            }
            if (urlParams.has('tarih2')) {
                $('#araTarih2').val(urlParams.get('tarih2'));
            }

            // Eğer URL'de arama parametreleri varsa, AJAX aramayı tetikle ve URL'yi temizle
            if (window.location.search) {
                console.log("URL'de parametreler bulundu. URL temizleniyor ve arama tetikleniyor.");
                console.log("Mevcut URL (temizleme öncesi):", window.location.href);
                var cleanUrl = window.location.origin + window.location.pathname;
                window.history.replaceState({}, document.title, cleanUrl);
                console.log("Yeni URL (temizleme sonrası):", window.location.href);
                arama();
            } else {
                console.log("URL'de parametre bulunamadı. İlk arama tetiklenmiyor.");
                // Export linkini varsayılan tarih (bugün) ile güncelle ki Excel export sayfayla aynı filtreleri kullansın
                var formData = $('#aramaForm').serialize();
                var tutarValue = $('#aratxt').val() || '';
                if (tutarValue) formData += (formData ? '&' : '') + 'odenentutar=' + encodeURIComponent(tutarValue);
                updateExportLink(formData);
            }
        })();
    }); // $(document).ready sonu

    // Global olarak erişilebilir fonksiyonlar
    var globalKasaPersoneller = [];
    var globalKasaTeknisyenler = [];
    var globalKasaOdemeTurleri = [];
    var globalKasaOdemeSekilleri = [];
    let selectedKasaHareketleri = []; // Buraya taşındı

    function updateBulkActionsVisibility() { // Buraya taşındı
        $('.selectedKasaCount').text(selectedKasaHareketleri.length);
        if (selectedKasaHareketleri.length > 0) {
            $('.bulk-actions-container').slideDown();
        } else {
            $('.bulk-actions-container').slideUp();
        }
    }

    function populateKasaModalDropdowns(personeller, odemeTurleri, odemeSekilleri) {
        var $personelSelect = $('#modalKasaPersonel');
        $personelSelect.empty().append('<option value="">Seçiniz...</option>');
        if (personeller && personeller.length > 0) {
            $.each(personeller, function(idx, personel) {
                $personelSelect.append($('<option>', { value: personel.id, text: personel.ad }));
            });
        }

        var $odemeTuruSelect = $('#modalKasaOdemeTuru');
        $odemeTuruSelect.empty().append('<option value="">Seçiniz...</option>');
        if (odemeTurleri && odemeTurleri.length > 0) {
            $.each(odemeTurleri, function(idx, tur) {
                $odemeTuruSelect.append($('<option>', { value: tur.id, text: tur.ad }));
            });
        }

        var $odemeSekliSelect = $('#modalKasaOdemeSekli');
        $odemeSekliSelect.empty().append('<option value="">Seçiniz...</option>');
        if (odemeSekilleri && odemeSekilleri.length > 0) {
            $.each(odemeSekilleri, function(idx, sekil) {
                $odemeSekliSelect.append($('<option>', { value: sekil.id, text: sekil.ad }));
            });
        }
    }

    function fetchKasaFormDataIfNeeded() {
        if (globalKasaPersoneller.length === 0 || globalKasaOdemeTurleri.length === 0 || globalKasaOdemeSekilleri.length === 0) {
                    $.ajax({
                url: '{{ route("kasa.formData") }}',
                type: 'GET',
                        dataType: 'json',
                async: false, 
                        success: function(response) {
                    if(response.success){
                        console.log("fetchKasaFormDataIfNeeded - Gelen Ödeme Türleri:", response.odemeTurleri);
                        globalKasaPersoneller = response.personeller || [];
                        globalKasaTeknisyenler = response.teknisyenler || [];
                        globalKasaOdemeTurleri = response.odemeTurleri || []; 
                        globalKasaOdemeSekilleri = response.odemeSekilleri || [];
                        populateKasaModalDropdowns(globalKasaPersoneller, globalKasaOdemeTurleri, globalKasaOdemeSekilleri);

                        // AJAX'tan gelen verilerle Servis İşlemleri ID'sini bul ve Patron olmayanlar için ayarla
                        const servisIslemleriTur = globalKasaOdemeTurleri.find(tur => tur.id === 5); // ID 5 olan ödeme türünü bul
                        if (servisIslemleriTur && !isPatron) {
                            $('#modalKasaOdemeTuru').val(servisIslemleriTur.id);
                            $('#modalKasaOdemeTuru').prop('disabled', true); // Değiştirilemez yap
                            updateKasaModalByOdemeTuru(servisIslemleriTur.id); // Ödeme türü değişimini simüle et
                        }
                    }
                },
                error: function() { console.error('Kasa modalı için form verileri yüklenemedi.'); }
                                });
                            } else {
            populateKasaModalDropdowns(globalKasaPersoneller, globalKasaOdemeTurleri, globalKasaOdemeSekilleri);
            // Eğer globalKasaOdemeTurleri zaten doluysa ve Patron değilse, yine Servis İşlemleri'ni seç
            const servisIslemleriTur = globalKasaOdemeTurleri.find(tur => tur.id === 5); // ID 5 olan ödeme türünü bul
            if (servisIslemleriTur && !isPatron) {
                $('#modalKasaOdemeTuru').val(servisIslemleriTur.id);
                $('#modalKasaOdemeTuru').prop('disabled', true); // Değiştirilemez yap
                updateKasaModalByOdemeTuru(servisIslemleriTur.id);
            }
        }
    }

    function updateKasaModalByOdemeTuru(selectedOdemeTuruId) {
        console.log('[UMBOT] updateKasaModalByOdemeTuru çağrıldı. ID:', selectedOdemeTuruId);
        var selectedTur = globalKasaOdemeTurleri.find(tur => tur.id == selectedOdemeTuruId);
        console.log('[UMBOT] Seçilen Ödeme Türü Obj:', selectedTur);
        if (selectedTur) { 
            console.log('[UMBOT] selectedTur.muhattap DEĞERİ:', selectedTur.muhattap);
            console.log('[UMBOT] selectedTur.muhattap TÜRÜ:', typeof selectedTur.muhattap);
        }

        var $personelRow = $('#modalKasaPersonel').closest('.row.mb-2.align-items-center');
        var $modalKasaPersonel = $('#modalKasaPersonel');
        var $servisIdRow = $('#modalKasaServisID').closest('.row.mb-2.align-items-center');
        var $aciklamaRow = $('#modalKasaAciklama').closest('.row'); 
        var $modalKasaServisID = $('#modalKasaServisID');
        var $modalKasaOdemeYonu = $('#modalKasaOdemeYonu');
        var isEditMode = Boolean($('#hareketId').val());

        console.log('[UMBOT] $personelRow length:', $personelRow.length);
        console.log('[UMBOT] $servisIdRow length:', $servisIdRow.length);

        // Varsayılan durum
        $personelRow.hide();
        $modalKasaPersonel.prop('required', false);
        $servisIdRow.hide();
        $modalKasaServisID.prop('readonly', true);
        $('#modalKasaServisLink').attr('href', '#').css({ opacity: '0.5', pointerEvents: 'none' });
        $aciklamaRow.show(); 

        if (selectedTur) {
            // Ödeme yönü sabit olan türlerde yönü kilitle
            var fixedYon = Number(selectedTur.yon);
            if (fixedYon === 1 || fixedYon === -1) {
                if (!isEditMode) {
                    $modalKasaOdemeYonu.val(String(fixedYon));
                }
                $modalKasaOdemeYonu.prop('disabled', true);
            } else if (!isTsrnTeknisyen) {
                $modalKasaOdemeYonu.prop('disabled', false);
            }
            if (selectedTur.muhattap && typeof selectedTur.muhattap === 'string') {
                var muhattaplar = selectedTur.muhattap.toUpperCase().split(',').map(item => item.trim());
                console.log('[UMBOT] Muhattaplar Array:', muhattaplar);
                
                var personelSarti = muhattaplar.includes('PERSONEL');
                var servisSarti = muhattaplar.includes('SERVIS');
                console.log('[UMBOT] PERSONEL muhattaplarda mı?', personelSarti);
                console.log('[UMBOT] SERVIS muhattaplarda mı?', servisSarti);

                    if (personelSarti) {
                    console.log("[UMBOT] PERSONEL koşulu sağlandı, $personelRow gösteriliyor.");
                    $personelRow.show();
                    $modalKasaPersonel.prop('required', true);
                        // Servis işlemlerinde personel listesi sadece teknisyen olsun
                        if (selectedTur && String(selectedTur.id) === '5' && Array.isArray(globalKasaTeknisyenler)) {
                            $modalKasaPersonel.empty().append('<option value="">Seçiniz...</option>');
                            var kaynakTeknisyenler = globalKasaTeknisyenler.length ? globalKasaTeknisyenler : globalKasaPersoneller;
                            kaynakTeknisyenler.forEach(function(p){
                                $modalKasaPersonel.append($('<option>', { value: p.id, text: p.ad }));
                            });
                        } else {
                            // Diğer türlerde tüm personeller
                            $modalKasaPersonel.empty().append('<option value="">Seçiniz...</option>');
                            globalKasaPersoneller.forEach(function(p){
                                $modalKasaPersonel.append($('<option>', { value: p.id, text: p.ad }));
                            });
                        }
                    // Patron değilse personeli sabitle
                    if (!isPatron && isTsrnTeknisyen) {
                        $modalKasaPersonel.val(loggedInUserId).prop('disabled', true);
                    } else {
                        $modalKasaPersonel.prop('disabled', false);
                    }
                } else {
                    $modalKasaPersonel.prop('disabled', true);
                }
                if (servisSarti) {
                    console.log("[UMBOT] SERVIS koşulu sağlandı, $servisIdRow gösteriliyor, $modalKasaServisID düzenlenebilir yapılıyor.");
                    $servisIdRow.show();
                    $modalKasaServisID.prop('readonly', false);
                    var servisIdVal = ($modalKasaServisID.val() || '').toString().trim();
                    if (servisIdVal && servisIdVal !== '0') {
                        $('#modalKasaServisLink').attr('href', '/servisler?open_servis_id=' + servisIdVal).css({ opacity: '1', pointerEvents: 'auto' });
                    }
                } else {
                    $modalKasaServisID.prop('readonly', true);
                }
            } else {
                console.log('[UMBOT] selectedTur.muhattap bulunamadı veya string değil.', selectedTur ? selectedTur.muhattap : 'selectedTur tanımsız');
                // Muhattap yoksa personel ve servis alanları varsayılan olarak gizli kalır.
                $modalKasaPersonel.prop('disabled', true);
                $modalKasaServisID.prop('readonly', true);
            }
        }

        // Taşeron teknisyenlerde ödeme yönü sabit: Gelen Ödeme (gelir)
        if (isTsrnTeknisyen) {
            $modalKasaOdemeYonu.val('1').prop('disabled', true);
        }
    }

    // Arama fonksiyonları - global kapsamda tanımlandı
    function sayikontrol(e) {
        e.value = e.value.replace(/[^0-9.]/g, '');
    }

    var exportBaseUrl = "{{ url('/genelkasa/export') }}";
    function updateExportLink(formData) {
        var href = exportBaseUrl;
        if (formData) {
            href += '?' + formData;
        }
        $('#genelKasaExportBtn').attr('href', href);
    }

    function arama() {
        console.log("arama() fonksiyonu çağrıldı.");
        var formData = $('#aramaForm').serialize();
        var tutarValue = $('#aratxt').val() || '';
        if (tutarValue) {
            formData += (formData ? '&' : '') + 'odenentutar=' + encodeURIComponent(tutarValue);
        }
        console.log("aramaForm verileri:", formData);
        console.log("Gönderilen tarih1:", $('#araTarih1').val());
        console.log("Gönderilen tarih2:", $('#araTarih2').val());
        var currentUrl = window.location.pathname; 

        updateExportLink(formData);
        
        $('#kasaListTable tbody').html('<tr><td colspan="9" class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div> Yükleniyor...</td></tr>'); // Colspan 8 -> 9 yapıldı

        $.ajax({
            url: currentUrl,
            type: 'GET',
            data: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest' 
            },
            success: function(response) {
                console.log("AJAX arama başarılı. Gelen yanıt:", response);
                $('#kasaListTable tbody').html(response.html); // response.html olarak güncellendi
                console.log("arama() sonrası tbody kontrolü:", $('#kasaListTable tbody tr.clickable-row').length, "adet clickable-row bulundu."); // Yeni log
                console.log("İlk clickable-row data-hareket-id:", $('#kasaListTable tbody tr.clickable-row:first').data('hareket-id')); // Yeni log
                // Arama sonrası selectedKasaHareketleri dizisini ve görünürlüğü sıfırla/güncelle
                selectedKasaHareketleri = [];
                $('#selectAllKasaHareketleri').prop('checked', false);
                updateBulkActionsVisibility();

                // Toplamları güncelle
                $('#totalGelir').text(parseFloat(response.totalGelir || 0).toLocaleString('tr-TR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' TL').removeClass('text-success text-danger').addClass('text-success');
                $('#totalGider').text(parseFloat(response.totalGider || 0).toLocaleString('tr-TR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' TL').removeClass('text-success text-danger').addClass('text-danger');
                
                const netToplam = parseFloat(response.netToplam || 0);
                $('#netToplam').text(netToplam.toLocaleString('tr-TR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' TL').removeClass('text-success text-danger').addClass(netToplam >= 0 ? 'text-success' : 'text-danger');

                // Gelir detaylarını güncelle
                $('#gelirDetaylariSubItems').empty(); // Yeni container'ı boşalt
                if (response.gelirDetaylariByOdemeSekli && response.gelirDetaylariByOdemeSekli.length > 0) {
                    $.each(response.gelirDetaylariByOdemeSekli, function(index, detay) {
                        const formattedTutar = parseFloat(detay.tutar || 0).toLocaleString('tr-TR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' TL';
                        const detailHtml = `
                            <div class="d-flex justify-content-between text-muted small py-1">
                                <span>${detay.ad}:</span>
                                <span>${formattedTutar}</span>
                            </div>
                        `;
                        $('#gelirDetaylariSubItems').append(detailHtml); // Yeni container'a ekle
                    });
                }

                // Gider detaylarını güncelle
                $('#giderDetaylariSubItems').empty();
                if (response.giderDetaylariByOdemeTuru && response.giderDetaylariByOdemeTuru.length > 0) {
                    $.each(response.giderDetaylariByOdemeTuru, function(index, detay) {
                        const tutar = parseFloat(detay.tutar || 0);
                        if (tutar <= 0) {
                            return;
                        }
                        const formattedTutar = tutar.toLocaleString('tr-TR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' TL';
                        const detailHtml = `
                            <div class="d-flex justify-content-between text-muted small py-1">
                                <span>${detay.ad}:</span>
                                <span>${formattedTutar}</span>
                            </div>
                        `;
                        $('#giderDetaylariSubItems').append(detailHtml);
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Arama Hatası:", status, error, xhr.responseText);
                $('#kasaListTable tbody').html('<tr><td colspan="9" class="text-center text-danger">Arama sonuçları yüklenirken bir hata oluştu.</td></tr>'); // Colspan 8 -> 9 yapıldı
                        }
                    });
                }

    function aramaTarihDegistir(tarih1, tarih2) {
        $('#araTarih1').val(tarih1);
        $('#araTarih2').val(tarih2);
        console.log('Tarih 1 set edildi:', $('#araTarih1').val());
        console.log('Tarih 2 set edildi:', $('#araTarih2').val());
        arama(); 
    }

    $('#kasaAraBtn').on('click', function () {
        arama();
    });

    $('#aratxt').on('keydown', function (e) {
        if (e.key === 'Enter' || e.keyCode === 13) {
            e.preventDefault();
            arama();
        }
    });

    // Helper function to convert YYYY-MM-DD to DD.MM.YYYY - global kapsamda tanımlandı
    function formatToDottedDate(dateString) {
        var parts = dateString.split('-');
        if (parts.length === 3) {
            return parts[2] + '.' + parts[1] + '.' + parts[0];
        }
        return dateString;
    }

</script>
@endpush
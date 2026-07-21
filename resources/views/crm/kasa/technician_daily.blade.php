@extends('layouts.app')

@section('title', 'Net Kasa Hareketleri')

@push('page_specific_css')
<style>
    .page-header-title,
    .page-header h5 {
        border-right: none !important;
        padding-right: 0 !important;
        margin-right: 0 !important;
    }
    /* Sağdaki frame (içerik alanı sağ kenarı) kalın */
    .main-content {
        border-right: 4px solid #dee2e6;
    }
    #kasaTeknisyenTable thead th {
        font-family: 'Inter', sans-serif;
        font-size: 13px;
        font-weight: 600;
        line-height: 1.2;
        padding: 0.55rem 0.4rem;
    }
    #kasaTeknisyenTable {
        table-layout: fixed;
        width: 100%;
    }
    #kasaTeknisyenTable th,
    #kasaTeknisyenTable td {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    #kasaTeknisyenTable th:nth-child(1),
    #kasaTeknisyenTable td:nth-child(1) { width: 9%; }
    #kasaTeknisyenTable th:nth-child(2),
    #kasaTeknisyenTable td:nth-child(2) { width: 20%; }
    #kasaTeknisyenTable th:nth-child(3),
    #kasaTeknisyenTable td:nth-child(3) { width: 9%; }
    #kasaTeknisyenTable th:nth-child(4),
    #kasaTeknisyenTable td:nth-child(4) { width: 13%; }
    #kasaTeknisyenTable th:nth-child(5),
    #kasaTeknisyenTable td:nth-child(5) { width: 13%; }
    #kasaTeknisyenTable th:nth-child(6),
    #kasaTeknisyenTable td:nth-child(6) { width: 13%; }
    #kasaTeknisyenTable th:nth-child(7),
    #kasaTeknisyenTable td:nth-child(7) { width: 13%; }
    #kasaTeknisyenTable th:nth-child(8),
    #kasaTeknisyenTable td:nth-child(8) { width: 6%; }
    #kasaTeknisyenTable tbody td {
        padding: 0.35rem 0.4rem;
        line-height: 1.2;
    }
    .kasa-table-wrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .kasa-detay-table thead th {
        font-size: 11px;
        font-weight: 600;
        line-height: 1.2;
        padding: 0.3rem 0.35rem;
    }
    .kasa-detay-table tbody td {
        font-size: 11px;
        line-height: 1.2;
        padding: 0.25rem 0.35rem;
    }
    .kasa-detay-table .servis-detay-link {
        text-decoration: underline;
        cursor: pointer;
    }
    .kasa-detay-table th,
    .kasa-detay-table td {
        font-style: italic;
    }
    /* Teknisyen detay tablosunda satır hover – hangi kayıt üzerinde olduğu belli olsun */
    .kasa-detay-table tbody tr td {
        transition: background-color 0.15s ease;
    }
    .kasa-detay-table tbody tr:hover td {
        background-color: #f1f3f5 !important;
    }
    .kasa-detay-gider td {
        color: #dc3545;
    }
    .teknisyen-toggle {
        font-weight: 700;
    }
    .teknisyen-toggle i {
        vertical-align: middle;
    }
    /* Detay açıkken diğer tüm satırlar blur (scroll’da da tutarlı; sabit overlay kullanılmıyor) */
    #kasaTeknisyenTable.kasa-detail-open tbody tr.teknisyen-row:not(.kasa-detail-active),
    #kasaTeknisyenTable.kasa-detail-open tbody tr.teknisyen-detail-row:not(.kasa-detail-active) {
        filter: blur(4px);
        opacity: 0.65;
        pointer-events: none;
        transition: filter 0.2s ease, opacity 0.2s ease;
    }
    #kasaTeknisyenTable tbody tr.teknisyen-row.kasa-detail-active,
    #kasaTeknisyenTable tbody tr.teknisyen-detail-row.kasa-detail-active {
        position: relative;
        z-index: 1050;
        background: #fff;
    }
    #kasaTeknisyenTable tbody tr.teknisyen-detail-row.kasa-detail-active td {
        background: #fff;
    }
    .kasa-advanced-search .form-control {
        min-width: 180px;
        height: 30px;
    }
    .kasa-advanced-search .dropdown-toggle::after {
        margin-left: 0.35rem;
    }
    .kasa-advanced-dropdown .form-control-sm,
    .kasa-advanced-dropdown .form-select-sm {
        height: 30px;
        min-height: 30px;
        padding-top: 0.2rem;
        padding-bottom: 0.2rem;
        font-size: 0.8rem;
    }
    .kasa-advanced-dropdown .form-control-sm,
    .kasa-advanced-dropdown .form-select-sm,
    .kasa-advanced-dropdown input[type="date"] {
        width: 100%;
        box-sizing: border-box;
    }
    .kasa-advanced-dropdown .form-control-sm,
    .kasa-advanced-dropdown .form-select-sm,
    .kasa-advanced-dropdown input[type="date"],
    .kasa-advanced-dropdown .col-8 {
        min-width: 0;
    }
    .kasa-advanced-dropdown .input-group-sm > .form-control,
    .kasa-advanced-dropdown .input-group-sm > .input-group-text {
        height: 30px;
    }
    .kasa-advanced-dropdown .input-group-sm .input-group-text {
        padding: 0 0.35rem;
        font-size: 0.75rem;
        line-height: 1;
    }
    .kasa-advanced-dropdown input[type="date"] {
        font-size: 0.8rem !important;
        line-height: 1.2 !important;
    }
    .kasa-advanced-dropdown .kasa-date-range {
        font-size: 0.7rem;
    }
    @media (max-width: 575.98px) {
        #kasaTarihForm input[type="date"] {
            font-size: 0.7rem;
        }
        #kasaTeknisyenTable {
            min-width: 920px;
        }
        #kasaTeknisyenTable thead th,
        #kasaTeknisyenTable tbody td {
            font-size: 11px;
            padding: 0.3rem 0.35rem;
        }
    }
</style>
@endpush

@section('content')
    @php
        $loggedInUser = Auth::user();
        $canMesaiAksiyon = $loggedInUser && in_array((int) $loggedInUser->poz_id, [1071, 1080]);
        $canSeeMesaiColumn = !$loggedInUser || (int) $loggedInUser->poz_id !== 1077;
    @endphp
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Net Kasa Hareketleri</h5>
            </div>
        </div>
        @if (!empty($canExport))
        <div class="page-header-right ms-auto">
            <div class="page-header-right-items">
                <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                    @php
                        $excelParams = [
                            'tarih1' => $dateFrom ?? $gun,
                            'tarih2' => $dateTo ?? $gun,
                        ];
                        if (!empty($selectedTeknisyenId)) {
                            $excelParams['teknisyen_id'] = $selectedTeknisyenId;
                        }
                        if (!empty($odenentutar)) {
                            $excelParams['odenentutar'] = $odenentutar;
                        }
                        $excelHref = url('/kasa/excel') . '?' . http_build_query($excelParams);
                    @endphp
                    <a href="{{ $excelHref }}" class="btn btn-outline-success" id="kasaExcelExportBtn">
                        <i class="feather-download me-2"></i>
                        <span>EXCEL</span>
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="main-content">
        <div class="row">
            <div class="col-12">
                <div class="card stretch stretch-full">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0">Net Kasa</h5>
                        <form id="kasaTarihForm" method="POST" action="{{ url('/kasa') }}" class="d-flex gap-2 align-items-center">
                            @csrf
                            @if (!empty($canAdvancedSearch))
                                <div class="input-group input-group-sm kasa-advanced-search">
                                    <input type="text" id="kasaOdenenTutar" name="odenentutar" autocomplete="off" value="{{ $odenentutar ?? '' }}" class="form-control form-control-sm" placeholder="Tutar veya teknisyen yazın.">
                                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                        <i class="feather-arrow-down"></i>
                                    </button>
                                    <button type="button" class="btn btn-primary btn-sm" id="kasaFilterSubmitBtn">
                                        <i class="feather-search"></i>
                                    </button>
                                    <div class="dropdown-menu p-3 kasa-advanced-dropdown" style="width: 280px;">
                                        <div class="row mb-2 align-items-center">
                                            <div class="col-4">
                                                <label for="kasaFilterTeknisyen" class="col-form-label col-form-label-sm">Teknisyen:</label>
                                            </div>
                                            <div class="col-8">
                                                <select id="kasaFilterTeknisyen" name="teknisyen_id" class="form-select form-select-sm">
                                                    <option value="">Hepsi</option>
                                                    @foreach($teknisyenler as $personel)
                                                        <option value="{{ $personel->id }}" {{ (string) ($selectedTeknisyenId ?? '') === (string) $personel->id ? 'selected' : '' }}>
                                                            {{ $personel->ad }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row mb-2 align-items-center">
                                            <div class="col-4">
                                                <label class="col-form-label col-form-label-sm">Gerçekleşme:</label>
                                            </div>
                                            <div class="col-8">
                                                <input type="date" id="kasaFilterTarih1" name="tarih1" value="{{ $dateFrom ?? $gun }}" class="form-control form-control-sm">
                                                <input type="date" id="kasaFilterTarih2" name="tarih2" value="{{ $dateTo ?? $gun }}" class="form-control form-control-sm mt-1">
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between mt-1">
                                                <a href="#" class="text-danger text-decoration-none kasa-date-range" data-from="{{ \Carbon\Carbon::today()->subYear()->format('Y-m-d') }}" data-to="{{ \Carbon\Carbon::today()->format('Y-m-d') }}">Son 1 Yıl</a>
                                                <a href="#" class="text-danger text-decoration-none kasa-date-range" data-from="{{ \Carbon\Carbon::today()->subMonth()->format('Y-m-d') }}" data-to="{{ \Carbon\Carbon::today()->format('Y-m-d') }}">Son 1 Ay</a>
                                        </div>
                                        <div class="d-flex justify-content-between mt-1">
                                                <a href="#" class="text-danger text-decoration-none kasa-date-range" data-from="{{ \Carbon\Carbon::yesterday()->format('Y-m-d') }}" data-to="{{ \Carbon\Carbon::yesterday()->format('Y-m-d') }}">Dün</a>
                                                <a href="#" class="text-danger text-decoration-none kasa-date-range" data-from="{{ \Carbon\Carbon::today()->format('Y-m-d') }}" data-to="{{ \Carbon\Carbon::today()->format('Y-m-d') }}">Bugün</a>
                                                <a href="#" class="text-danger text-decoration-none kasa-date-range" data-from="{{ \Carbon\Carbon::tomorrow()->format('Y-m-d') }}" data-to="{{ \Carbon\Carbon::tomorrow()->format('Y-m-d') }}">Yarın</a>
                                        </div>
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-primary btn-sm mt-3">Ara</button>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <label for="tarih" class="mb-0 small">Tarih</label>
                                <input type="date" id="tarih" name="tarih" value="{{ $gun }}" class="form-control form-control-sm">
                                <button type="submit" class="btn btn-sm btn-primary">GET&#304;R</button>
                            @endif
                        </form>
                    </div>
                    <div class="card-body">
                        <div class="text-muted small mb-3">
                            <em>Sadece <strong>tamamlandı</strong> durumundaki ödemeler hesaplamaya dahil edilir.</em>
                        </div>
                        <div class="row row-cols-2 row-cols-md-5 g-3 mb-3">
                            <div class="col">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body py-2">
                                        <div class="text-muted small">Toplam Gelir</div>
                                        <div class="fw-bold text-success" id="kasaToplamGelir">
                                            @if (!empty($kasaKartlarAdetli))
                                                ADETLİ
                                            @else
                                                {{ number_format((float)$toplamGelir, 2, ',', '.') }} TL
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body py-2">
                                        <div class="text-muted small">Toplam Gider</div>
                                        <div class="fw-bold text-danger" id="kasaToplamGider">
                                            @if (!empty($kasaKartlarAdetli))
                                                ADETLİ
                                            @else
                                                {{ number_format((float)$toplamGider, 2, ',', '.') }} TL
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body py-2">
                                        <div class="text-muted small">Teknisyen Payları</div>
                                        <div class="fw-bold text-warning" id="kasaTeknisyenPayi">
                                            @if (!empty($kasaKartlarAdetli))
                                                ADETLİ
                                            @else
                                                {{ number_format((float)$toplamTeknisyenPayi, 2, ',', '.') }} TL
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body py-2">
                                        <div class="text-muted small">Bekleyen Ödemeler</div>
                                        <div class="fw-bold text-danger" id="kasaBekleyenOdeme">
                                            @if (!empty($kasaKartlarAdetli))
                                                -
                                            @else
                                                {{ number_format((float)($bekleyenOdemeToplam ?? 0), 2, ',', '.') }} TL
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @if (!$loggedInUser || (int) $loggedInUser->poz_id !== 1077)
                            <div class="col">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body py-2">
                                        <div class="text-muted small">Net Kalan</div>
                                        <div class="fw-bold text-primary" id="kasaNetKalan">
                                            @if (!empty($kasaKartlarAdetli))
                                                ADETLİ
                                            @else
                                                {{ number_format((float)$netKalanTeknisyen, 2, ',', '.') }} TL
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                            @if ($loggedInUser && (int) $loggedInUser->poz_id === 1077)
                            <div class="col">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body py-2">
                                        <div class="text-muted small">Ödenmesi Gereken</div>
                                        <div class="fw-bold text-primary" id="kasaOdenmesiGereken">
                                            @if (!empty($kasaKartlarAdetli))
                                                ADETLİ
                                            @else
                                                {{ number_format((float)$toplamFirmaPayi, 2, ',', '.') }} TL
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                        @if ($canMesaiAksiyon)
                        <div class="d-flex justify-content-end gap-2 mb-2">
                            <button type="button" id="teknisyenMesaiKapatBtn" class="btn btn-sm btn-danger d-none">
                                MESAİ KAPAT
                            </button>
                            <button type="button" id="teknisyenMesaiAcBtn" class="btn btn-sm btn-success d-none">
                                MESAİ AÇ
                            </button>
                        </div>
                        @endif
                        <div class="table-responsive kasa-table-wrap">
                            <table id="kasaTeknisyenTable" class="table table-hover table-bordered align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center">TAR&#304;H</th>
                                        <th class="text-center">TEKN&#304;SYEN</th>
                                        @if ($canSeeMesaiColumn)
                                        <th class="text-center">MESA&#304;</th>
                                        @endif
                                        <th class="text-center">GEL&#304;R</th>
                                        <th class="text-center">G&#304;DER</th>
                                        <th class="text-center">TEKN&#304;SYEN PAY&#305;</th>
                                        <th class="text-center">F&#304;RMA PAY&#305;</th>
                                        @if ($canMesaiAksiyon)
                                        <th class="text-center">
                                            <div class="d-flex align-items-center justify-content-center gap-2">
                                                <span>SE&#199;</span>
                                                <input type="checkbox" id="selectAllTeknisyen" class="form-check-input m-0">
                                            </div>
                                        </th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @include('crm.kasa._technician_daily_rows_v2', ['gunlukOzetler' => $gunlukOzetler, 'gun' => $gun])
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('page_specific_scripts')
<script>
    (function () {
        var form = document.getElementById('kasaTarihForm');
        if (!form) {
            return;
        }

        var mesaiKapatBtn = document.getElementById('teknisyenMesaiKapatBtn');
        var mesaiAcBtn = document.getElementById('teknisyenMesaiAcBtn');
        var csrfToken = document.querySelector('meta[name="csrf-token"]');
        var csrfValue = csrfToken ? csrfToken.getAttribute('content') : '';

        var excelExportBaseUrl = "{{ url('/kasa/excel') }}";
        function updateKasaExcelLink(dateFrom, dateTo, teknisyenId, tutar) {
            var href = excelExportBaseUrl;
            var params = [];
            if (dateFrom) {
                params.push('tarih1=' + encodeURIComponent(dateFrom));
            }
            if (dateTo) {
                params.push('tarih2=' + encodeURIComponent(dateTo));
            }
            if (teknisyenId) {
                params.push('teknisyen_id=' + encodeURIComponent(teknisyenId));
            }
            if (tutar) {
                params.push('odenentutar=' + encodeURIComponent(tutar));
            }
            if (params.length) {
                href += '?' + params.join('&');
            }
            var exportBtn = document.getElementById('kasaExcelExportBtn');
            if (exportBtn) {
                exportBtn.setAttribute('href', href);
            }
        }

        function getFilterTeknisyenId() {
            var select = document.getElementById('kasaFilterTeknisyen');
            return select ? select.value : '';
        }

        function getFilterTutar() {
            var input = document.getElementById('kasaOdenenTutar');
            return input ? input.value : '';
        }

        function getSelectedTeknisyenIds() {
            return Array.from(document.querySelectorAll('.teknisyen-select:checked'))
                .map(function (checkbox) { return checkbox.value; })
                .filter(Boolean);
        }

        var filterTarih1Input = document.getElementById('kasaFilterTarih1');
        var filterTarih2Input = document.getElementById('kasaFilterTarih2');
        var filterTeknisyenSelect = document.getElementById('kasaFilterTeknisyen');
        var filterSubmitBtn = document.getElementById('kasaFilterSubmitBtn');

        if (filterTarih1Input) {
            filterTarih1Input.addEventListener('change', function () {
                updateKasaExcelLink(
                    filterTarih1Input.value,
                    filterTarih2Input ? filterTarih2Input.value : '',
                    getFilterTeknisyenId(),
                    getFilterTutar()
                );
            });
        }
        if (filterTarih2Input) {
            filterTarih2Input.addEventListener('change', function () {
                updateKasaExcelLink(
                    filterTarih1Input ? filterTarih1Input.value : '',
                    filterTarih2Input.value,
                    getFilterTeknisyenId(),
                    getFilterTutar()
                );
            });
        }

        if (filterTeknisyenSelect) {
            filterTeknisyenSelect.addEventListener('change', function () {
                updateKasaExcelLink(
                    filterTarih1Input ? filterTarih1Input.value : '{{ $gun }}',
                    filterTarih2Input ? filterTarih2Input.value : '{{ $gun }}',
                    getFilterTeknisyenId(),
                    getFilterTutar()
                );
            });
        }

        document.querySelectorAll('.kasa-date-range').forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                if (!filterTarih1Input || !filterTarih2Input) return;
                filterTarih1Input.value = this.getAttribute('data-from') || filterTarih1Input.value;
                filterTarih2Input.value = this.getAttribute('data-to') || filterTarih2Input.value;
                updateKasaExcelLink(
                    filterTarih1Input.value,
                    filterTarih2Input.value,
                    getFilterTeknisyenId(),
                    getFilterTutar()
                );
            });
        });

        if (filterSubmitBtn && form) {
            filterSubmitBtn.addEventListener('click', function () {
                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                } else {
                    form.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
                }
            });
        }

        var kasaOdenenTutarInput = document.getElementById('kasaOdenenTutar');
        if (kasaOdenenTutarInput && form) {
            kasaOdenenTutarInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.keyCode === 13) {
                    e.preventDefault();
                    if (typeof form.requestSubmit === 'function') {
                        form.requestSubmit();
                    } else {
                        form.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
                    }
                }
            });
        }

        updateKasaExcelLink(
            filterTarih1Input ? filterTarih1Input.value : '{{ $gun }}',
            filterTarih2Input ? filterTarih2Input.value : '{{ $gun }}',
            getFilterTeknisyenId(),
            getFilterTutar()
        );

        function updateMesaiButtons() {
            var selectedIds = getSelectedTeknisyenIds();
            if (mesaiKapatBtn) {
                mesaiKapatBtn.classList.toggle('d-none', selectedIds.length === 0);
            }
            if (mesaiAcBtn) {
                mesaiAcBtn.classList.toggle('d-none', selectedIds.length === 0);
            }
        }

        document.addEventListener('change', function (e) {
            if (e.target && e.target.classList.contains('teknisyen-select')) {
                var allCheckboxes = document.querySelectorAll('.teknisyen-select');
                var checkedCount = document.querySelectorAll('.teknisyen-select:checked').length;
                var selectAll = document.getElementById('selectAllTeknisyen');
                if (selectAll) {
                    selectAll.checked = (allCheckboxes.length > 0 && checkedCount === allCheckboxes.length);
                }
                updateMesaiButtons();
            }
        });

        var selectAllCheckbox = document.getElementById('selectAllTeknisyen');
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function () {
                var isChecked = selectAllCheckbox.checked;
                document.querySelectorAll('.teknisyen-select').forEach(function (checkbox) {
                    checkbox.checked = isChecked;
                });
                updateMesaiButtons();
            });
        }

        var mesaiStateById = {};
        var canMesaiAksiyon = {!! $canMesaiAksiyon ? 'true' : 'false' !!};
        var canSeeMesaiColumn = {!! $canSeeMesaiColumn ? 'true' : 'false' !!};

        function syncMesaiStateFromRow(row) {
            var id = row.getAttribute('data-teknisyen-id');
            if (!id) {
                return;
            }
            var value = row.getAttribute('data-mesai');
            if (value !== null) {
                mesaiStateById[id] = value === '1';
                return;
            }
            var badge = row.querySelector('td .badge');
            if (badge) {
                mesaiStateById[id] = badge.textContent.trim().toLowerCase() === 'çalışıyor';
            }
        }

        function updateMesaiCells(ids, isWorking) {
            var badgeClass = isWorking ? 'bg-soft-success text-success' : 'bg-soft-danger text-danger';
            var badgeText = isWorking ? 'Çalışıyor' : 'Çalışmıyor';
            ids.forEach(function (id) {
                var row = document.querySelector('#kasaTeknisyenTable tbody tr.teknisyen-row[data-teknisyen-id="' + id + '"]');
                if (!row) {
                    return;
                }
                row.setAttribute('data-mesai', isWorking ? '1' : '0');
                mesaiStateById[String(id)] = isWorking;
                var mesaiCell = row.querySelector('td.mesai-cell');
                if (!mesaiCell) {
                    var cells = row.querySelectorAll('td');
                    if (cells.length >= 3) {
                        mesaiCell = cells[2];
                    }
                }
                if (mesaiCell) {
                    mesaiCell.classList.add('mesai-cell');
                    mesaiCell.innerHTML = '<span class="badge ' + badgeClass + '">' + badgeText + '</span>';
                }
            });
        }

        function normalizeKasaTableRows() {
            var rows = document.querySelectorAll('#kasaTeknisyenTable tbody tr.teknisyen-row');
            rows.forEach(function (row) {
                syncMesaiStateFromRow(row);

                var gunValue = row.getAttribute('data-gun') || '';
                var gunFormatted = formatGunDisplay(gunValue);
                var teknisyenCell = row.querySelector('td.teknisyen-cell');
                if (teknisyenCell && teknisyenCell.previousElementSibling === null) {
                    var dateTd = document.createElement('td');
                    dateTd.textContent = gunFormatted;
                    row.insertBefore(dateTd, teknisyenCell);
                }

                if (canSeeMesaiColumn) {
                    var mesaiValue = mesaiStateById[row.getAttribute('data-teknisyen-id')] === true;
                    var badgeClass = mesaiValue ? 'bg-soft-success text-success' : 'bg-soft-danger text-danger';
                    var badgeText = mesaiValue ? 'Çalışıyor' : 'Çalışmıyor';

                    var mesaiCell = row.querySelector('td.mesai-cell');
                    if (!mesaiCell) {
                        mesaiCell = document.createElement('td');
                        mesaiCell.className = 'text-center mesai-cell';
                        mesaiCell.innerHTML = '<span class="badge ' + badgeClass + '">' + badgeText + '</span>';
                        if (teknisyenCell) {
                            row.insertBefore(mesaiCell, teknisyenCell.nextSibling);
                        } else {
                            row.insertBefore(mesaiCell, row.querySelectorAll('td')[2] || null);
                        }
                    } else {
                        mesaiCell.innerHTML = '<span class="badge ' + badgeClass + '">' + badgeText + '</span>';
                    }
                } else {
                    var existingMesai = row.querySelector('td.mesai-cell');
                    if (existingMesai) {
                        existingMesai.remove();
                    }
                }

                if (canMesaiAksiyon) {
                    var lastCell = row.querySelector('td:last-child');
                    if (!lastCell || !lastCell.querySelector('.teknisyen-select')) {
                        var secTd = document.createElement('td');
                        secTd.className = 'text-center';
                        secTd.innerHTML = '<input type="checkbox" class="form-check-input teknisyen-select" value="' + (row.getAttribute('data-teknisyen-id') || '') + '">';
                        row.appendChild(secTd);
                    }
                }
            });

            document.querySelectorAll('#kasaTeknisyenTable tbody tr.teknisyen-detail-row').forEach(function (detailRow) {
                var detailCell = detailRow.querySelector('td');
                if (detailCell) {
                    var baseColspan = canMesaiAksiyon ? 8 : 7;
                    if (!canSeeMesaiColumn) {
                        baseColspan -= 1;
                    }
                    detailCell.setAttribute('colspan', String(baseColspan));
                }
            });
        }

        function formatGunDisplay(gunValue) {
            if (!gunValue) {
                return '';
            }
            var parts = gunValue.split('-');
            if (parts.length === 3) {
                return parts[2] + '.' + parts[1] + '.' + parts[0];
            }
            return gunValue;
        }

        function applyGunToRows(gunValue) {
            var formattedGun = formatGunDisplay(gunValue);
            if (!formattedGun) {
                return;
            }
            document.querySelectorAll('#kasaTeknisyenTable tbody tr.teknisyen-row').forEach(function (row) {
                var firstCell = row.querySelector('td:first-child');
                if (firstCell && firstCell.textContent.trim() === '') {
                    firstCell.textContent = formattedGun;
                }
            });
        }

        function applyMesaiMap(mesaiMap) {
            if (!mesaiMap) {
                return;
            }
            Object.keys(mesaiMap).forEach(function (id) {
                var value = parseInt(mesaiMap[id], 10) === 1;
                updateMesaiCells([id], value);
            });
        }

        function refreshMesaiFromServer() {
            if (!canMesaiAksiyon) {
                return;
            }
            var ids = Array.from(document.querySelectorAll('#kasaTeknisyenTable tbody tr.teknisyen-row'))
                .map(function (row) { return row.getAttribute('data-teknisyen-id'); })
                .filter(function (id) { return id; });

            if (!ids.length) {
                return;
            }

            if (window.jQuery) {
                $.ajax({
                    url: "{{ url('/kasa/teknisyen-mesai-map') }}",
                    method: 'POST',
                    data: {
                        _token: csrfValue,
                        teknisyen_ids: ids
                    },
                    dataType: 'json',
                    success: function (data) {
                        if (data && data.mesaiMap) {
                            applyMesaiMap(data.mesaiMap);
                        }
                    }
                });
                return;
            }

            fetch("{{ url('/kasa/teknisyen-mesai-map') }}", {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfValue,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ teknisyen_ids: ids })
            })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (data && data.mesaiMap) {
                    applyMesaiMap(data.mesaiMap);
                }
            })
            .catch(function () {
                // Hata olursa sessizce geç
            });
        }

        function handleMesaiAction(actionUrl, confirmText, targetState) {
            var selectedIds = getSelectedTeknisyenIds();
            if (!selectedIds.length) {
                return;
            }

            var confirmAndSend = function () {
                var timeoutId = setTimeout(function () {
                    if (window.Swal) {
                        Swal.fire('Hata', 'Sunucudan yanıt alınamadı.', 'error');
                    } else {
                        alert('Sunucudan yanıt alınamadı.');
                    }
                }, 8000);

                if (window.Swal) {
                    Swal.fire({
                        title: 'İşlem yapılıyor...',
                        allowOutsideClick: false,
                        didOpen: function () {
                            Swal.showLoading();
                        }
                    });
                } else {
                    alert('İşlem başlatıldı.');
                }

                if (window.jQuery) {
                    $.ajax({
                        url: actionUrl,
                        method: 'POST',
                        data: {
                            _token: csrfValue,
                            teknisyen_ids: selectedIds
                        },
                        dataType: 'json',
                        success: function (data) {
                            clearTimeout(timeoutId);
                            var blockedIds = (data && Array.isArray(data.still_active_ids)) ? data.still_active_ids : [];
                            var effectiveIds = selectedIds.filter(function (id) { return blockedIds.indexOf(parseInt(id, 10)) === -1; });
                            if (effectiveIds.length) {
                                updateMesaiCells(effectiveIds, targetState === 1);
                            }
                            document.querySelectorAll('.teknisyen-select:checked').forEach(function (checkbox) {
                                checkbox.checked = false;
                            });
                            var selectAll = document.getElementById('selectAllTeknisyen');
                            if (selectAll) {
                                selectAll.checked = false;
                            }
                            updateMesaiButtons();

                            var successMsg = (data && data.message) ? data.message : 'İşlem tamamlandı.';
                            if (data && Array.isArray(data.still_active_ids) && data.still_active_ids.length) {
                                successMsg += ' Güncellenemeyen ID: ' + data.still_active_ids.join(', ');
                            }
                            if (window.Swal) {
                                Swal.fire('Başarılı', successMsg, 'success');
                            } else {
                                alert(successMsg);
                            }
                        },
                        error: function (xhr) {
                            clearTimeout(timeoutId);
                            var errorMessage = 'İşlem sırasında hata oluştu.';
                            if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            if (window.Swal) {
                                Swal.fire('Hata', errorMessage, 'error');
                            } else {
                                alert(errorMessage);
                            }
                        },
                        complete: function () {
                            if (window.Swal && Swal.isLoading()) {
                                Swal.close();
                            }
                        }
                    });
                    return;
                }

                fetch(actionUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfValue,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ teknisyen_ids: selectedIds })
                })
                .then(function (response) {
                    return response.text().then(function (text) {
                        var data = null;
                        try {
                            data = text ? JSON.parse(text) : null;
                        } catch (e) {
                            data = null;
                        }
                        return { ok: response.ok, status: response.status, data: data };
                    });
                })
                .then(function (result) {
                    clearTimeout(timeoutId);
                        if (result.ok && result.data && result.data.success) {
                            var blockedIds = (result.data && Array.isArray(result.data.still_active_ids)) ? result.data.still_active_ids : [];
                            var effectiveIds = selectedIds.filter(function (id) { return blockedIds.indexOf(parseInt(id, 10)) === -1; });
                            if (effectiveIds.length) {
                                updateMesaiCells(effectiveIds, targetState === 1);
                            }
                        document.querySelectorAll('.teknisyen-select:checked').forEach(function (checkbox) {
                            checkbox.checked = false;
                        });
                        var selectAll = document.getElementById('selectAllTeknisyen');
                        if (selectAll) {
                            selectAll.checked = false;
                        }
                        updateMesaiButtons();

                        var successMsg = result.data.message || 'İşlem tamamlandı.';
                        if (window.Swal) {
                            Swal.fire('Başarılı', successMsg, 'success');
                        } else {
                            alert(successMsg);
                        }
                        return;
                    }

                    var errorMessage = (result.data && result.data.message)
                        ? result.data.message
                        : 'İşlem sırasında hata oluştu. (HTTP ' + result.status + ')';
                    if (window.Swal) {
                        Swal.fire('Hata', errorMessage, 'error');
                    } else {
                        alert(errorMessage);
                    }
                })
                .catch(function () {
                    clearTimeout(timeoutId);
                    if (window.Swal) {
                        Swal.fire('Hata', 'İşlem sırasında hata oluştu.', 'error');
                    }
                });
            };

            if (window.Swal) {
                Swal.fire({
                    title: 'Emin misiniz?',
                    text: confirmText,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Evet, onayla',
                    cancelButtonText: 'Vazgeç',
                    preConfirm: function () {
                        confirmAndSend();
                        return true;
                    }
                });
            } else if (window.confirm(confirmText)) {
                confirmAndSend();
            }
        }

        if (mesaiKapatBtn) {
            mesaiKapatBtn.addEventListener('click', function () {
                handleMesaiAction("{{ url('/kasa/teknisyen-mesai-kapat') }}", 'Seçilen teknisyenlerin mesaisi kapatılacak.', 0);
            });
        }

        if (mesaiAcBtn) {
            mesaiAcBtn.addEventListener('click', function () {
                handleMesaiAction("{{ url('/kasa/teknisyen-mesai-ac') }}", 'Seçilen teknisyenlerin mesaisi açılacak.', 1);
            });
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            var formData = new FormData(form);
            var token = form.querySelector('input[name="_token"]');
            var tbody = document.querySelector('#kasaTeknisyenTable tbody');

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token ? token.value : ''
                },
                body: formData
            })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (!data || !data.success) return;
                if (tbody) {
                    tbody.innerHTML = data.html;
                    if (data.gun) {
                        document.querySelectorAll('#kasaTeknisyenTable tbody tr.teknisyen-row').forEach(function (row) {
                            row.setAttribute('data-gun', data.gun);
                        });
                    }
                    if (data.mesaiMap) {
                        Object.keys(data.mesaiMap).forEach(function (id) {
                            var row = document.querySelector('#kasaTeknisyenTable tbody tr.teknisyen-row[data-teknisyen-id="' + id + '"]');
                            if (row) {
                                row.setAttribute('data-mesai', String(data.mesaiMap[id]));
                            }
                        });
                    }
                    normalizeKasaTableRows();
                    applyGunToRows(data.gun);
                    applyMesaiMap(data.mesaiMap);
                    refreshMesaiFromServer();
                    var selectAll = document.getElementById('selectAllTeknisyen');
                    if (selectAll) {
                        selectAll.checked = false;
                    }
                    updateMesaiButtons();
                }
                updateKasaExcelLink(data.gun || (document.getElementById('tarih') ? document.getElementById('tarih').value : ''));
                if (data.gun && document.getElementById('tarih')) {
                    document.getElementById('tarih').value = data.gun;
                }
                if (data.kasaKartlarAdetli) {
                    var gelirEl = document.getElementById('kasaToplamGelir');
                    if (gelirEl) gelirEl.textContent = 'ADETLİ';
                    var giderEl = document.getElementById('kasaToplamGider');
                    if (giderEl) giderEl.textContent = 'ADETLİ';
                    var teknisyenPayiEl = document.getElementById('kasaTeknisyenPayi');
                    if (teknisyenPayiEl) teknisyenPayiEl.textContent = 'ADETLİ';
                    var bekleyenEl = document.getElementById('kasaBekleyenOdeme');
                    if (bekleyenEl) bekleyenEl.textContent = '-';
                    var odenmesiGerekenEl = document.getElementById('kasaOdenmesiGereken');
                    if (odenmesiGerekenEl) odenmesiGerekenEl.textContent = 'ADETLİ';
                    var netKalanEl = document.getElementById('kasaNetKalan');
                    if (netKalanEl) netKalanEl.textContent = 'ADETLİ';
                } else {
                    var gelirEl = document.getElementById('kasaToplamGelir');
                    if (gelirEl && typeof data.toplamGelir !== 'undefined') {
                        gelirEl.textContent = Number(data.toplamGelir || 0).toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' TL';
                    }
                    var giderEl = document.getElementById('kasaToplamGider');
                    if (giderEl && typeof data.toplamGider !== 'undefined') {
                        giderEl.textContent = Number(data.toplamGider || 0).toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' TL';
                    }
                    var teknisyenPayiEl = document.getElementById('kasaTeknisyenPayi');
                    if (teknisyenPayiEl && typeof data.toplamTeknisyenPayi !== 'undefined') {
                        teknisyenPayiEl.textContent = Number(data.toplamTeknisyenPayi || 0).toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' TL';
                    }
                    var bekleyenEl = document.getElementById('kasaBekleyenOdeme');
                    if (bekleyenEl && typeof data.bekleyenOdemeToplam !== 'undefined') {
                        bekleyenEl.textContent = Number(data.bekleyenOdemeToplam || 0).toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' TL';
                    }
                    var odenmesiGerekenEl = document.getElementById('kasaOdenmesiGereken');
                    if (odenmesiGerekenEl && typeof data.toplamFirmaPayi !== 'undefined') {
                        odenmesiGerekenEl.textContent = Number(data.toplamFirmaPayi || 0).toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' TL';
                    }
                    var netKalanEl = document.getElementById('kasaNetKalan');
                    if (netKalanEl) {
                        if (typeof data.netKalanTeknisyen !== 'undefined') {
                            netKalanEl.textContent = Number(data.netKalanTeknisyen || 0).toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' TL';
                        } else if (typeof data.netKalan !== 'undefined') {
                            netKalanEl.textContent = Number(data.netKalan || 0).toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' TL';
                        }
                    }
                }
            })
            .catch(function () {
                // Hata olursa sessizce eski tabloyu koruyoruz.
            });
        });

        function removeKasaDetailBackdrop() {
            var table = document.getElementById('kasaTeknisyenTable');
            if (table) table.classList.remove('kasa-detail-open');
            document.querySelectorAll('#kasaTeknisyenTable tbody tr.kasa-detail-active').forEach(function (tr) { tr.classList.remove('kasa-detail-active'); });
        }
        function addKasaDetailBackdrop(row, detailRow) {
            removeKasaDetailBackdrop();
            row.classList.add('kasa-detail-active');
            detailRow.classList.add('kasa-detail-active');
            var table = document.getElementById('kasaTeknisyenTable');
            if (table) table.classList.add('kasa-detail-open');
        }

        document.addEventListener('click', function (e) {
            var cell = e.target.closest('.teknisyen-cell');
            if (!cell) {
                var openDetail = document.querySelector('.teknisyen-detail-row:not(.d-none)');
                if (openDetail) {
                    var openRow = openDetail.previousElementSibling;
                    var clickedInside = e.target.closest('.teknisyen-row') || e.target.closest('.teknisyen-detail-row');
                    if (!clickedInside && openRow) {
                        openDetail.classList.add('d-none');
                        openDetail.firstElementChild.innerHTML = '';
                        var toggleOutsideIcon = openRow.querySelector('.teknisyen-toggle i');
                        if (toggleOutsideIcon) {
                            toggleOutsideIcon.classList.remove('feather-minus-circle');
                            toggleOutsideIcon.classList.add('feather-plus-circle');
                        }
                        removeKasaDetailBackdrop();
                    }
                }
                return;
            }

            var row = cell.closest('tr.teknisyen-row');
            var detailRow = row ? row.nextElementSibling : null;
            if (!row || !detailRow || !detailRow.classList.contains('teknisyen-detail-row')) {
                return;
            }

            if (!detailRow.classList.contains('d-none')) {
                detailRow.classList.add('d-none');
                detailRow.firstElementChild.innerHTML = '';
                var toggleClosed = row.querySelector('.teknisyen-toggle i');
                if (toggleClosed) {
                    toggleClosed.classList.remove('feather-minus-circle');
                    toggleClosed.classList.add('feather-plus-circle');
                }
                removeKasaDetailBackdrop();
                return;
            }

            var teknisyenId = row.getAttribute('data-teknisyen-id');
            var tarihInput = document.getElementById('tarih');
            var tarih1Input = document.getElementById('kasaFilterTarih1');
            var tarih2Input = document.getElementById('kasaFilterTarih2');
            var tarih = tarihInput ? tarihInput.value : '';
            var tarih1 = tarih1Input ? tarih1Input.value : '';
            var tarih2 = tarih2Input ? tarih2Input.value : '';
            var token = form.querySelector('input[name="_token"]');

            var params = { teknisyen_id: teknisyenId };
            if (tarih1 && tarih2) {
                params.tarih1 = tarih1;
                params.tarih2 = tarih2;
            } else {
                params.tarih = tarih || (tarih1 || tarih2 || '');
            }

            fetch("{{ url('/kasa/teknisyen-detay') }}", {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token ? token.value : ''
                },
                body: new URLSearchParams(params)
            })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (data && data.success) {
                    detailRow.firstElementChild.innerHTML = data.html;
                    detailRow.classList.remove('d-none');
                    var toggleOpen = row.querySelector('.teknisyen-toggle i');
                    if (toggleOpen) {
                        toggleOpen.classList.remove('feather-plus-circle');
                        toggleOpen.classList.add('feather-minus-circle');
                    }
                    addKasaDetailBackdrop(row, detailRow);
                }
            })
            .catch(function () {
                // Hata olursa detay açılmaz.
            });
        });

        document.addEventListener('click', function (e) {
            var link = e.target.closest('.servis-detay-link');
            if (!link) {
                return;
            }

            e.preventDefault();

            var servisId = link.getAttribute('data-servis-id');
            if (!servisId) {
                return;
            }

            if (window.jQuery) {
                var $modal = $('#servisDetayModal');
                $modal.data('servis-id', servisId);
                window.mevcutServisId = servisId;
                $modal.modal('show');
                return;
            }

            var modalEl = document.getElementById('servisDetayModal');
            if (modalEl && window.bootstrap) {
                modalEl.setAttribute('data-servis-id', servisId);
                var modalInstance = window.bootstrap.Modal.getOrCreateInstance(modalEl);
                modalInstance.show();
            }
        });

        refreshMesaiFromServer();
    })();
</script>
@endpush

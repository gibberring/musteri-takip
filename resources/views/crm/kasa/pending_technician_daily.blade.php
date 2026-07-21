@extends('layouts.app')

@section('title', 'Bekleyen Ödemeler')

@push('page_specific_css')
<style>
    .page-header-title,
    .page-header h5 {
        border-right: none !important;
        padding-right: 0 !important;
        margin-right: 0 !important;
    }
    #kasaTeknisyenTable thead th {
        font-family: 'Inter', sans-serif;
        font-size: 13px;
        font-weight: 600;
        line-height: 1.2;
        padding: 0.55rem 0.4rem;
    }
    #kasaTeknisyenTable tbody td {
        padding: 0.35rem 0.4rem;
        line-height: 1.2;
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
    .teknisyen-toggle {
        font-weight: 700;
    }
    .teknisyen-toggle i {
        vertical-align: middle;
    }
    @media (max-width: 575.98px) {
        #kasaTeknisyenTable thead th {
            font-size: 12px;
        }
    }
</style>
@endpush

@section('content')
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Bekleyen Ödemeler</h5>
            </div>
        </div>
    </div>

    <div class="main-content">
        <div class="row">
            <div class="col-12">
                <div class="card stretch stretch-full">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0">Bekleyen Ödemeler</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-muted small mb-3">
                            <em>Sadece <strong>beklemede</strong> olan ödemeler listelenir. Bekleyen müşteri ödemelerini <u>en kısa sürede</u> netliğe kavuşturunuz.</em>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body py-2">
                                        <div class="text-muted small">Toplam Gelir</div>
                                        <div class="fw-bold text-success" id="kasaToplamGelir">{{ number_format((float)$toplamGelir, 2, ',', '.') }} TL</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body py-2">
                                        <div class="text-muted small">Toplam Gider</div>
                                        <div class="fw-bold text-danger" id="kasaToplamGider">{{ number_format((float)$toplamGider, 2, ',', '.') }} TL</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body py-2">
                                        <div class="text-muted small">Net Kalan</div>
                                        <div class="fw-bold text-primary" id="kasaNetKalan">{{ number_format((float)$netKalan, 2, ',', '.') }} TL</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="kasaTeknisyenTable" class="table table-hover table-bordered align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center">TEKN&#304;SYEN</th>
                                        <th class="text-center">GEL&#304;R</th>
                                        <th class="text-center">G&#304;DER</th>
                                        <th class="text-center">TEKN&#304;SYEN PAY&#305;</th>
                                        <th class="text-center">F&#304;RMA PAY&#305;</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @include('crm.kasa._pending_technician_rows', ['gunlukOzetler' => $gunlukOzetler, 'gun' => $gun])
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
        var csrfToken = document.querySelector('meta[name="csrf-token"]');
        var csrfValue = csrfToken ? csrfToken.getAttribute('content') : '';

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
                        var toggleOutside = openRow.querySelector('.teknisyen-toggle');
                        if (toggleOutside) {
                            toggleOutside.textContent = '+';
                        }
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
                    toggleClosed.classList.remove('feather-chevron-down');
                    toggleClosed.classList.add('feather-chevron-right');
                }
                return;
            }

            var teknisyenId = row.getAttribute('data-teknisyen-id');

            fetch("{{ url('/kasa/bekleyen-odemeler/teknisyen-detay') }}", {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfValue
                },
                body: new URLSearchParams({
                    teknisyen_id: teknisyenId
                })
            })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (data && data.success) {
                    detailRow.firstElementChild.innerHTML = data.html;
                    detailRow.classList.remove('d-none');
                    var toggleOpen = row.querySelector('.teknisyen-toggle i');
                    if (toggleOpen) {
                        toggleOpen.classList.remove('feather-chevron-right');
                        toggleOpen.classList.add('feather-chevron-down');
                    }
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
    })();
</script>
@endpush

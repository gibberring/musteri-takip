@extends('layouts.app')

@section('title', 'Teknisyen Bakışı')

@push('page_specific_css')
    <style>
        .page-header-title,
        .page-header h5 {
            border-right: none !important;
            padding-right: 0 !important;
            margin-right: 0 !important;
        }
        /* stretch-full: height calc(100% - 24px) içeriği kesiyor; kart içeriğe göre büyüsün */
        .teknisyen-bakisi-ozet-card {
            height: auto !important;
            min-height: 0;
            overflow: visible;
        }
        .teknisyen-bakisi-ozet-card .card-body {
            flex: 0 0 auto;
            height: auto !important;
            overflow: visible;
            padding-top: 1rem;
            padding-bottom: 1rem;
        }
        .teknisyen-bakisi-ozet-inner {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.75rem;
            min-width: 0;
        }
        .teknisyen-bakisi-ozet-text {
            flex: 1 1 auto;
            min-width: 0;
            overflow: visible;
        }
        .teknisyen-bakisi-ozet-text .fs-4 {
            line-height: 1.25;
        }
        .teknisyen-bakisi-ozet-alt {
            white-space: nowrap;
            overflow: visible;
        }
        .teknisyen-bakisi-col {
            display: flex;
            align-items: stretch;
        }
        .teknisyen-bakisi-card {
            height: auto !important;
            min-height: 0;
            width: 100%;
            overflow: visible;
        }
        .teknisyen-bakisi-card .card-body {
            flex: 0 0 auto;
            height: auto;
            overflow: visible;
        }
        .teknisyen-bakisi-card .durum-satir:hover {
            background-color: #f8f9fa;
        }
        .teknisyen-bakisi-card .durum-satir {
            border-radius: 0.25rem;
            padding: 0.35rem 0.4rem;
            flex-shrink: 0;
        }
        .teknisyen-bakisi-card .avatar-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        @media (max-width: 575.98px) {
            .teknisyen-bakisi-card .fs-4 {
                font-size: 1.35rem !important;
            }
        }
    </style>
@endpush

@section('content')
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Teknisyen Bakışı</h5>
            </div>
        </div>
    </div>

    <div class="main-content">
        <div class="row mb-3">
            <div class="col-md-6 col-xl-4 mb-2 mb-md-0">
                <div class="card teknisyen-bakisi-ozet-card mb-0">
                    <div class="card-body">
                        <div class="teknisyen-bakisi-ozet-inner">
                            <div class="avatar-text avatar-lg bg-gray-200 flex-shrink-0">
                                <i class="feather-users"></i>
                            </div>
                            <div class="teknisyen-bakisi-ozet-text">
                                <div class="fs-4 fw-bold text-dark">{{ $toplamAcikIs }}</div>
                                <div class="fs-13 fw-semibold">Toplam açık iş</div>
                                <div class="fs-12 text-muted teknisyen-bakisi-ozet-alt">{{ $teknisyenSayisi }} teknisyen</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-4 d-flex align-items-center">
                <input type="text" id="teknisyenBakisiAra" class="form-control" placeholder="Teknisyen ara...">
            </div>
        </div>

        @if(empty($kartlar))
            <div class="card">
                <div class="card-body text-center text-muted py-5">
                    Atanmış açık iş bulunamadı.
                </div>
            </div>
        @else
            <div class="row" id="teknisyenBakisiGrid">
                @foreach($kartlar as $kart)
                    <div class="col-12 col-md-6 col-xl-4 teknisyen-bakisi-col" data-ad="{{ $kart['ad'] }}">
                        <div class="card teknisyen-bakisi-card">
                            <div class="card-body">
                                <div class="d-flex align-items-start justify-content-between mb-3">
                                    <div class="d-flex gap-3 align-items-center">
                                        <div class="avatar-image avatar-lg flex-shrink-0">
                                            <img src="{{ $kart['avatar_url'] ?? asset('crm_assets/images/avatar/1.png') }}"
                                                 alt="{{ $kart['ad'] }}"
                                                 class="img-fluid"
                                                 onerror="this.onerror=null;this.src='{{ asset('crm_assets/images/avatar/1.png') }}';">
                                        </div>
                                        <div>
                                            <h3 class="fs-13 fw-semibold text-truncate-1-line mb-0">{{ $kart['ad'] }}</h3>
                                            <a href="{{ route('servisler.teknisyenBakisi', ['personel_id' => $kart['personel_id']]) }}" class="fs-4 fw-bold text-dark text-decoration-none" title="Tüm açık işleri listele">
                                                {{ $kart['toplam'] }}
                                            </a>
                                            <div class="fs-12 text-muted">açık iş</div>
                                        </div>
                                    </div>
                                </div>
                                @foreach($kart['durumlar'] as $durum)
                                    <a href="{{ route('servisler.teknisyenBakisi', ['personel_id' => $kart['personel_id'], 'durum_id' => $durum['id']]) }}"
                                       class="durum-satir d-flex align-items-center justify-content-between text-decoration-none"
                                       title="{{ $durum['ad'] }} kayıtlarını listele">
                                        <span class="fs-13 fw-semibold text-muted">{{ $durum['kisa_ad'] }}</span>
                                        <span class="badge {{ $durum['badge'] }}">{{ $durum['count'] }}</span>
                                    </a>
                                @endforeach

                                @if(!empty($finansGoster) && !empty($kart['finans']))
                                    @php $finans = $kart['finans']; @endphp
                                    <div class="teknisyen-bakisi-finans border-top mt-3 pt-3">
                                        <div class="fs-11 fw-bold text-uppercase text-muted mb-2">Bugün · {{ $finansTarihi }}</div>
                                        <div class="d-flex justify-content-between fs-13 mb-1">
                                            <span class="text-muted fw-semibold">Ciro</span>
                                            <span class="fw-bold text-success">
                                                @if($finans['adetli'])Adetli @else{{ number_format($finans['gelir'], 2, ',', '.') }} TL @endif
                                            </span>
                                        </div>
                                        <div class="d-flex justify-content-between fs-13 mb-1">
                                            <span class="text-muted fw-semibold">Teknisyene kalan</span>
                                            <span class="fw-bold text-dark">
                                                @if($finans['adetli'])Adetli @else{{ number_format($finans['teknisyen_payi'], 2, ',', '.') }} TL @endif
                                            </span>
                                        </div>
                                        <div class="d-flex justify-content-between fs-13">
                                            <span class="text-muted fw-semibold">Firmaya ödemesi gereken</span>
                                            <span class="fw-bold text-danger">{{ number_format($finans['firma_payi'], 2, ',', '.') }} TL</span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="text-muted fs-12 mt-2">
            İptal ve Fiyatta Anlaşılamadı kartlarda (0 değilse) ayrı satır olarak görünür; Toplam açık iş sayısına dahil edilmez. Cihaz Teslim ve diğer tamamlanmış durumlar kartlara dahil değildir. İleri tarihli yönlendirmeler de görünür.
        </div>
    </div>
@endsection

@push('page_specific_scripts')
<script>
    $(document).ready(function () {
        $('#teknisyenBakisiAra').on('input', function () {
            var q = (($(this).val() || '') + '').toLocaleLowerCase('tr-TR').trim();
            $('.teknisyen-bakisi-col').each(function () {
                var ad = (($(this).attr('data-ad') || '') + '').toLocaleLowerCase('tr-TR');
                $(this).toggle(q === '' || ad.indexOf(q) !== -1);
            });
        });
    });
</script>
@endpush

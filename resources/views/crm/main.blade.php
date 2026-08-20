@extends('layouts.app')

@section('title', 'Panel')

@push('page_specific_css')
    {{-- Panel sayfasına özel CSS dosyaları (daterangepicker vb. eğer vendors.min.css'de yoksa) --}}
    <link rel="stylesheet" type="text/css" href="{{ asset('crm_assets/vendors/css/daterangepicker.min.css') }}" />
    <style>
        .page-header-title,
        .page-header h5 {
            border-right: none !important;
            padding-right: 0 !important;
            margin-right: 0 !important;
        }
        #servis-il-dagilimi-donut + .row .text-truncate {
            font-family: 'Roboto', sans-serif !important; 
        }

        /* Panel - Bugünkü İthal Marka Servisleri Tablosu Font Ayarı */
        #ithalServisTable span {
            font-family: 'Inter', sans-serif !important;
            font-size: 12px !important;
            line-height: 1.1 !important;
            font-weight: 400 !important;
        }
        #ithalServisTable th {
            font-family: 'Inter', sans-serif !important;
            font-size: 12px !important;
            line-height: 1.1 !important;
            font-weight: 600 !important;
            padding-top: 0.3rem;
            padding-bottom: 0.3rem;
        }
        #ithalServisTable td {
            font-family: 'Inter', sans-serif !important;
            font-size: 12px !important;
            line-height: 1.1 !important;
            font-weight: 400 !important;
            padding-top: 0.25rem;
            padding-bottom: 0.25rem;
        }
        .il-kazanc-filter .form-control-sm {
            height: 31px;
            padding-top: 0.25rem;
            padding-bottom: 0.25rem;
        }
        .il-kazanc-table {
            font-size: 12px;
        }
    </style>
@endpush

@section('content')
    <!-- [ page-header ] start -->
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Panel</h5>
            </div>
        </div>
    </div>
    <!-- [ page-header ] end -->

    <!-- [ Main Content ] start -->
    <div class="main-content">
        <div class="row">
            <!-- [Invoices Awaiting Payment] start -->
            <div class="col-xxl-3 col-md-6">
                <div class="card stretch stretch-full">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between mb-4">
                            <div class="d-flex gap-4 align-items-center">
                                <div class="avatar-text avatar-lg bg-gray-200">
                                    <i class="feather-users"></i>
                                </div>
                                <div>
                                    <div class="fs-4 fw-bold text-dark">{{ $bugunServisSayisi }}</div>
                                    <h3 class="fs-13 fw-semibold text-truncate-1-line">Bugün Kaydedilen Servis</h3>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="fs-13 fw-semibold text-muted">İptal</div>
                            <a href="{{ route('servisler.bugunkuIptaller') }}" class="d-flex align-items-center text-decoration-none" title="Bugünkü iptalleri listele">
                                <span class="fs-12 text-dark">{{ $bugunMusteriIptalSayisi }}</span>
                                <span class="fs-11 text-muted ms-1">({{ $bugunMusteriIptalOrani }}%)</span>
                            </a>
                        </div>
                        <div class="progress mt-3" style="height: 4px;">
                            <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $bugunMusteriIptalOrani }}%" aria-valuenow="{{ $bugunMusteriIptalOrani }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mt-2">
                            <div class="fs-13 fw-semibold text-muted">Atölye</div>
                            <div class="d-flex align-items-center">
                                <span class="fs-12 text-dark">{{ $bugunAtolyeSayisiKart ?? 0 }}</span>
                                <span class="fs-11 text-muted ms-1">({{ $bugunAtolyeOraniKart ?? 0 }}%)</span>
                            </div>
                        </div>
                        <div class="progress mt-3" style="height: 4px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $bugunAtolyeOraniKart ?? 0 }}%" aria-valuenow="{{ $bugunAtolyeOraniKart ?? 0 }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- [Invoices Awaiting Payment] end -->
            <!-- [Converted Leads] start -->
            <div class="col-xxl-3 col-md-6">
                <div class="card stretch stretch-full">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between mb-4">
                            <div class="d-flex gap-4 align-items-center">
                                <div class="avatar-text avatar-lg bg-gray-200">
                                    <i class="feather-users"></i>
                                </div>
                                <div>
                                    <div class="fs-4 fw-bold text-dark">{{ $dunServisSayisi }}</div>
                                    <h3 class="fs-13 fw-semibold text-truncate-1-line">Dün Kaydedilen Servis</h3>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="fs-13 fw-semibold text-muted">İptal</div>
                            <div class="d-flex align-items-center">
                                <span class="fs-12 text-dark">{{ $dunMusteriIptalSayisi }}</span>
                                <span class="fs-11 text-muted ms-1">({{ $dunMusteriIptalOrani }}%)</span>
                            </div>
                        </div>
                        <div class="progress mt-3" style="height: 4px;">
                            <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $dunMusteriIptalOrani }}%" aria-valuenow="{{ $dunMusteriIptalOrani }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mt-2">
                            <div class="fs-13 fw-semibold text-muted">Atölye</div>
                            <div class="d-flex align-items-center">
                                <span class="fs-12 text-dark">{{ $dunAtolyeSayisiKart ?? 0 }}</span>
                                <span class="fs-11 text-muted ms-1">({{ $dunAtolyeOraniKart ?? 0 }}%)</span>
                            </div>
                        </div>
                        <div class="progress mt-3" style="height: 4px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $dunAtolyeOraniKart ?? 0 }}%" aria-valuenow="{{ $dunAtolyeOraniKart ?? 0 }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- [Converted Leads] end -->
            <!-- [Projects in Progress] start -->
            <div class="col-xxl-3 col-md-6">
                <div class="card stretch stretch-full">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between mb-4">
                            <div class="d-flex gap-4 align-items-center">
                                <div class="avatar-text avatar-lg bg-gray-200">
                                    <i class="feather-users"></i>
                                </div>
                                <div>
                                    <div class="fs-4 fw-bold text-dark">{{ $oncekiGunServisSayisi }}</div>
                                    <h3 class="fs-13 fw-semibold text-truncate-1-line">Önceki Gün Servis</h3>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="fs-13 fw-semibold text-muted">İptal</div>
                            <div class="d-flex align-items-center">
                                <span class="fs-12 text-dark">{{ $oncekiGunMusteriIptalSayisi }}</span>
                                <span class="fs-11 text-muted ms-1">({{ $oncekiGunMusteriIptalOrani }}%)</span>
                            </div>
                        </div>
                        <div class="progress mt-3" style="height: 4px;">
                            <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $oncekiGunMusteriIptalOrani }}%" aria-valuenow="{{ $oncekiGunMusteriIptalOrani }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mt-2">
                            <div class="fs-13 fw-semibold text-muted">Atölye</div>
                            <div class="d-flex align-items-center">
                                <span class="fs-12 text-dark">{{ $oncekiGunAtolyeSayisiKart ?? 0 }}</span>
                                <span class="fs-11 text-muted ms-1">({{ $oncekiGunAtolyeOraniKart ?? 0 }}%)</span>
                            </div>
                        </div>
                        <div class="progress mt-3" style="height: 4px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $oncekiGunAtolyeOraniKart ?? 0 }}%" aria-valuenow="{{ $oncekiGunAtolyeOraniKart ?? 0 }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- [Projects in Progress] end -->
            <!-- [Conversion Rate] start -->
            <div class="col-xxl-3 col-md-6">
                <div class="card stretch stretch-full">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between mb-4">
                            <div class="d-flex gap-4 align-items-center">
                                <div class="avatar-text avatar-lg bg-gray-200">
                                    <i class="feather-check-circle"></i>
                                </div>
                                <div>
                                    <div class="fs-4 fw-bold text-dark">{{ $tamamlananOrani }}%</div>
                                    <h3 class="fs-13 fw-semibold text-truncate-1-line">Bugün Tamamlanan Servis</h3>
                                </div>
                            </div>
                        </div>
                        <div class="pt-4">
                            <div class="d-flex align-items-center justify-content-between">
                                <a href="javascript:void(0);" class="fs-12 fw-medium text-muted text-truncate-1-line">Tamamlanan</a>
                                <div class="w-100 text-end">
                                    <span class="fs-12 text-dark">{{ $bugunTamamlananServisSayisi }}</span>
                                    <span class="fs-11 text-muted">({{ $tamamlananOrani }}%)</span>
                                </div>
                            </div>
                            <div class="progress mt-2 ht-3">
                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $tamamlananOrani }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- [Conversion Rate] end -->
            <!-- [Payment Records] start -->
            <div class="col-xxl-8">
                <div class="card stretch stretch-full">
                    <div class="card-header">
                        <h5 class="card-title">Son 7 Gün Grafiği</h5>
                       
                    </div>
                    <div class="card-body custom-card-action p-0">
                        <div id="panel-son-7-gun-chart"></div> 
                    </div>
                    <div class="card-footer">
                        <div class="row g-4 justify-content-center"> 
                            <div class="col-lg-4">
                                <div class="p-3 border border-dashed rounded">
                                    <div class="fs-12 text-muted mb-1">Bugün Kaydedilen</div>
                                    <h6 class="fw-bold text-dark">{{ $bugunServisSayisi ?? 0 }}</h6>
                                    <div class="progress mt-2 ht-3">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: 100%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <a href="{{ route('servisler.bugunkuIptaller') }}" class="p-3 border border-dashed rounded d-block text-decoration-none" title="Bugünkü iptalleri listele">
                                    <div class="fs-12 text-muted mb-1">Bugün İptal Edilen</div>
                                    <h6 class="fw-bold text-dark">{{ $bugunIptalEdilenSayisi ?? 0 }}</h6>
                                    <div class="progress mt-2 ht-3">
                                        <div class="progress-bar bg-danger" role="progressbar" style="width: {{ ($bugunServisSayisi ?? 0) > 0 ? (($bugunIptalEdilenSayisi ?? 0) / ($bugunServisSayisi ?? 1)) * 100 : 0 }}%"></div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-lg-4">
                                <div class="p-3 border border-dashed rounded">
                                    <div class="fs-12 text-muted mb-1">Bugün Atölyeye Alınan</div>
                                    <h6 class="fw-bold text-dark">{{ $bugunAtolyeSayisi ?? 0 }}</h6>
                                    <div class="progress mt-2 ht-3">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ ($bugunServisSayisi ?? 0) > 0 ? (($bugunAtolyeSayisi ?? 0) / ($bugunServisSayisi ?? 1)) * 100 : 0 }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- [Payment Records] end -->
            <!-- [Total Sales] start -->
            <div class="col-xxl-4">
                <div class="card stretch stretch-full overflow-hidden">
                    <div class="bg-primary text-white">
                        <div class="p-4">
                            <div class="text-start">
                                <h4 class="text-reset mb-1">{{ number_format($kasaSummary['todayNetCash'] ?? 0, 2, ',', '.') }} TL</h4>
                                <p class="text-reset lh-1">Bugünkü Net Kasa</p>
                                <hr class="my-2 border-light opacity-50">
                                <p class="text-reset lh-1 mb-0">Toplam Gelir: <span class="fw-semibold">{{ number_format($kasaSummary['todayIncome'] ?? 0, 2, ',', '.') }} TL</span></p>
                                <p class="text-reset lh-1">Toplam Gider: <span class="fw-semibold">{{ number_format($kasaSummary['todayExpense'] ?? 0, 2, ',', '.') }} TL</span></p>
                            </div>
                        </div>
                        <div id="kasa-summary-chart"></div> 
                    </div>
                    <div class="card-body">
                        @php
                            $kasaGiderItems = [
                                [
                                    'label' => 'Maaş Ödemeleri',
                                    'value' => $kasaSummary['todaySalaryPayments'] ?? 0,
                                    'icon' => '<i class="feather feather-dollar-sign fs-3"></i>',
                                ],
                                [
                                    'label' => 'Uğur Bey Harcama',
                                    'value' => $kasaSummary['todayUgurHarcamasi'] ?? 0,
                                    'icon' => '<i class="feather feather-user fs-3"></i>',
                                ],
                                [
                                    'label' => 'Ofis Giderleri',
                                    'value' => $kasaSummary['todayOfficeExpenses'] ?? 0,
                                    'icon' => '<i class="feather feather-briefcase fs-3"></i>',
                                ],
                                [
                                    'label' => 'Reklam Ödemeleri',
                                    'value' => $kasaSummary['todayAdPayments'] ?? 0,
                                    'icon' => '<img src="' . asset('crm_assets/images/google-ads.svg') . '" alt="Google Ads" width="26" height="26">',
                                ],
                                [
                                    'label' => 'Teknisyen Payları',
                                    'value' => $kasaSummary['todayTechnicianPayments'] ?? 0,
                                    'icon' => '<i class="feather feather-users fs-3"></i>',
                                ],
                                [
                                    'label' => 'Servis Giderleri',
                                    'value' => $kasaSummary['todayServiceExpenses'] ?? 0,
                                    'icon' => '<i class="feather feather-tool fs-3"></i>',
                                ],
                            ];
                            $kasaGiderItems = array_values(array_filter($kasaGiderItems, function ($item) {
                                return ($item['value'] ?? 0) > 0;
                            }));
                        @endphp

                        @foreach($kasaGiderItems as $item)
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="hstack gap-3">
                                    <div class="avatar-image avatar-lg p-2 rounded">
                                        {!! $item['icon'] !!}
                                    </div>
                                    <div>
                                        <a href="javascript:void(0);" class="d-block">{{ $item['label'] }}</a>
                                        <span class="fs-12 text-muted">Gider</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="fw-bold text-danger">{{ number_format($item['value'] ?? 0, 2, ',', '.') }} TL</div>
                                </div>
                            </div>
                            @if(!$loop->last)
                                <hr class="border-dashed my-3" />
                            @endif
                        @endforeach
                    </div>
                    {{-- Footer linki kaldırıldı --}}
                    {{-- <a href="javascript:void(0);" class="card-footer fs-11 fw-bold text-uppercase text-center py-4">Full Details</a> --}}
                </div>
            </div>
            <!-- [Total Sales] end !-->
            <div class="col-xxl-12">
                <div class="card stretch stretch-full">
                    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div>
                            <h5 class="mb-0">İl Bazında Firma Kazancı</h5>
                            <small class="text-muted">Gerçekleşme tarihi (kasa) baz alınır</small>
                        </div>
                        <form method="GET" action="{{ route('panel.ilKazanc') }}" id="ilKazancFilterForm" class="d-flex flex-wrap align-items-end gap-2 il-kazanc-filter">
                            <div>
                                <input type="date" name="il_kazanc_tarih1" class="form-control form-control-sm" value="{{ $ilKazancTarih1 ? $ilKazancTarih1->format('Y-m-d') : '' }}">
                            </div>
                            <div>
                                <input type="date" name="il_kazanc_tarih2" class="form-control form-control-sm" value="{{ $ilKazancTarih2 ? $ilKazancTarih2->format('Y-m-d') : '' }}">
                            </div>
                            <div>
                                <button type="submit" class="btn btn-sm btn-primary">FİLTRELE</button>
                            </div>
                        </form>
                    </div>
                    <div class="card-body">
                        <div id="ilKazancTableWrapper">
                            @include('crm.partials.il-kazanc-table', ['ilBazliKazanc' => $ilBazliKazanc])
                        </div>
                    </div>
                </div>
            </div>
            <!-- [Mini] start -->
            <div class="col-lg-4">
                <div class="card mb-4 stretch stretch-full">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <div class="d-flex gap-3 align-items-center">
                            <div class="avatar-text">
                                <i class="feather feather-phone-missed"></i>
                            </div>
                            <div>
                                <div class="fw-semibold text-dark">Müşteriye Ulaşılamadı</div>
                                <div class="fs-12 text-muted">{{ $miniCardData['ulasilamadi']['today'] ?? 0 }} servis</div>
                            </div>
                        </div>
                        <div class="fs-4 fw-bold text-dark">{{ $miniCardData['ulasilamadi']['today'] ?? 0 }}</div>
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-between gap-4">
                        <div id="mini-ulasilamadi-chart" style="min-height: 70px;"></div>
                        <div class="fs-12 text-muted text-nowrap">
                            @if($miniCardData['ulasilamadi']['type'] == 'increase')
                                <span class="fw-semibold text-success">{{ $miniCardData['ulasilamadi']['diff'] ?? 0 }}% daha fazla</span><br />
                            @elseif($miniCardData['ulasilamadi']['type'] == 'decrease')
                                <span class="fw-semibold text-danger">{{ $miniCardData['ulasilamadi']['diff'] ?? 0 }}% daha az</span><br />
                            @else
                                <span class="fw-semibold text-muted">Değişim Yok</span><br />
                            @endif
                            <span>geçen haftadan</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card mb-4 stretch stretch-full">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <div class="d-flex gap-3 align-items-center">
                            <div class="avatar-text">
                                <i class="feather feather-clock"></i>
                            </div>
                            <div>
                                <div class="fw-semibold text-dark">Haber Verecek</div>
                                <div class="fs-12 text-muted">{{ $miniCardData['haberVerecek']['today'] ?? 0 }} servis</div>
                            </div>
                        </div>
                        <div class="fs-4 fw-bold text-dark">{{ $miniCardData['haberVerecek']['today'] ?? 0 }}</div>
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-between gap-4">
                        <div id="mini-haberverecek-chart" style="min-height: 70px;"></div>
                        <div class="fs-12 text-muted text-nowrap">
                            @if($miniCardData['haberVerecek']['type'] == 'increase')
                                <span class="fw-semibold text-success">{{ $miniCardData['haberVerecek']['diff'] ?? 0 }}% daha fazla</span><br />
                            @elseif($miniCardData['haberVerecek']['type'] == 'decrease')
                                <span class="fw-semibold text-danger">{{ $miniCardData['haberVerecek']['diff'] ?? 0 }}% daha az</span><br />
                            @else
                                <span class="fw-semibold text-muted">Değişim Yok</span><br />
                            @endif
                            <span>geçen haftadan</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card mb-4 stretch stretch-full">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <div class="d-flex gap-3 align-items-center">
                            <div class="avatar-text">
                                <i class="feather feather-user-x"></i>
                            </div>
                            <div>
                                <div class="fw-semibold text-dark">Müşteri İptal Etti</div>
                                <div class="fs-12 text-muted">{{ $miniCardData['musteriIptal']['today'] ?? 0 }} servis</div>
                            </div>
                        </div>
                        <div class="fs-4 fw-bold text-dark">{{ $miniCardData['musteriIptal']['today'] ?? 0 }}</div>
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-between gap-4">
                        <div id="mini-iptal-chart" style="min-height: 70px;"></div>
                        <div class="fs-12 text-muted text-nowrap">
                             @if($miniCardData['musteriIptal']['type'] == 'increase')
                                <span class="fw-semibold text-success">{{ $miniCardData['musteriIptal']['diff'] ?? 0 }}% daha fazla</span><br />
                            @elseif($miniCardData['musteriIptal']['type'] == 'decrease')
                                <span class="fw-semibold text-danger">{{ $miniCardData['musteriIptal']['diff'] ?? 0 }}% daha az</span><br />
                            @else
                                <span class="fw-semibold text-muted">Değişim Yok</span><br />
                            @endif
                            <span>geçen haftadan</span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- [Mini] end !-->
            @if (!$isTsrnTeknisyen)
            <!-- [Leads Overview] start -->
            <div class="col-xxl-4 d-flex flex-column gap-3">
                <div class="card stretch stretch-full mb-0">
                    <div class="card-header">
                        <h5 class="card-title">Bugünkü Servislerin İl Dağılımı</h5>
                    </div>
                    <div class="card-body custom-card-action">
                        <div id="servis-il-dagilimi-donut"></div>
                        <div class="row g-2 mt-3">
                            @if(isset($ilBazliServisDagilimi) && !empty($ilBazliServisDagilimi['labels']))
                                @foreach($ilBazliServisDagilimi['labels'] as $index => $label)
                                    @php
                                        $colors = ['#3454d1', '#0d519e', '#1976d2', '#1e88e5', '#2196f3', '#42a5f5', '#64b5f6', '#90caf9', '#aad6fa', '#cce5ff'];
                                        $color = $colors[$index % count($colors)];
                                        $count = $ilBazliServisDagilimi['counts'][$index] ?? 0;
                                    @endphp
                                    <div class="col-4">
                                        <a href="javascript:void(0);" class="p-2 hstack gap-2 rounded border border-dashed border-gray-5">
                                            <span class="wd-7 ht-7 rounded-circle d-inline-block" style="background-color: {{ $color }}"></span>
                                            <span class="text-truncate">{{ $label }}<span class="fs-10 text-muted ms-1">({{ $count }})</span></span>
                                        </a>
                                    </div>
                                @endforeach
                            @else
                                <div class="col-12 text-center">
                                    <p class="text-muted mb-0">Bugün için il bazlı servis verisi bulunamadı.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card stretch stretch-full mb-0">
                    <div class="card-header">
                        <h5 class="card-title">Bugünkü Servislerin Marka Dağılımı</h5>
                    </div>
                    <div class="card-body custom-card-action">
                        <div id="servis-marka-dagilimi-donut"></div>
                        <div class="row g-2 mt-3">
                            @if(isset($markaBazliServisDagilimi) && !empty($markaBazliServisDagilimi['labels']))
                                @foreach($markaBazliServisDagilimi['labels'] as $index => $label)
                                    @php
                                        $markaColors = ['#e65100', '#f57c00', '#fb8c00', '#ffa726', '#ffb74d', '#ff9800', '#ef6c00', '#ff6f00', '#ff9100', '#ffe0b2'];
                                        $mcolor = $markaColors[$index % count($markaColors)];
                                        $mcount = $markaBazliServisDagilimi['counts'][$index] ?? 0;
                                    @endphp
                                    <div class="col-4">
                                        <a href="javascript:void(0);" class="p-2 hstack gap-2 rounded border border-dashed border-gray-5">
                                            <span class="wd-7 ht-7 rounded-circle d-inline-block" style="background-color: {{ $mcolor }}"></span>
                                            <span class="text-truncate">{{ $label }}<span class="fs-10 text-muted ms-1">({{ $mcount }})</span></span>
                                        </a>
                                    </div>
                                @endforeach
                            @else
                                <div class="col-12 text-center">
                                    <p class="text-muted mb-0">Bugün için marka bazlı servis verisi bulunamadı.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <!-- [Leads Overview] end -->
            @endif
            @if (!$isTsrnTeknisyen)
            <!-- [Latest Leads] start -->
            <div class="col-xxl-8">
                <div class="card stretch stretch-full">
                    <div class="card-header">
                        <h5 class="card-title">Bugünkü İthal Marka Servisleri</h5>
                        {{-- Card Header Action Kaldırıldı --}}
                    </div>
                    <div class="card-body custom-card-action p-0">
                        <div class="table-responsive">
                            <table id="ithalServisTable" class="table table-hover mb-0">
                                <thead>
                                    <tr class="border-b">
                                        <th scope="row">MÜŞTERİ</th>
                                        <th>İLÇE / İL</th>
                                        <th>MARKA / CİHAZ</th>
                                        <th>DURUM</th>
                                        {{-- Actions sütunu kaldırıldı --}}
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($bugunIthalMarkaServisleri as $servis)
                                        <tr class="clickable-row"
                                            style="cursor: pointer;"
                                            data-bs-toggle="modal"
                                            data-bs-target="#servisDetayModal"
                                            data-servis-id="{{ $servis->id ?? '' }}"
                                            data-servis-kayit-tarihi="{{ $servis->created_at ? $servis->created_at->format('d.m.Y H:i') : '-' }}"
                                            data-servis-operatoru="{{ $servis->operator?->ad ?? ($servis->user?->name ?? ($servis->kullanici?->ad ?? 'N/A')) }}"
                                            data-musteri-ad="{{ $servis->musteri?->ad ?? 'N/A' }}"
                                            data-musteri-tel1="{{ $servis->musteri?->tel1 ?? '' }}"
                                            data-musteri-tel2="{{ $servis->musteri?->tel2 ?? '' }}"
                                            data-musteri-adres="{{ $servis->musteri?->adres ?? '' }}"
                                            data-musteri-il="{{ $servis->musteri?->il?->ad ?? '' }}"
                                            data-musteri-ilce="{{ $servis->musteri?->ilce?->ad ?? '' }}"
                                            data-musteri-vergi-dairesi="{{ $servis->musteri?->vdaire ?? '' }}"
                                            data-musteri-vergi-no="{{ $servis->musteri?->vno ?? '' }}"
                                            data-marka-ad="{{ $servis->marka?->ad ?? 'N/A' }}"
                                            data-cihazturu-ad="{{ $servis->cihazTuru?->ad ?? 'N/A' }}"
                                            data-cihaz-model="{{ $servis->cihaz_model ?? '' }}"
                                            data-seri-no="{{ $servis->seri_no ?? '' }}"
                                            data-cihaz-arizasi="{{ $servis->cihaz_arizasi ?? '' }}"
                                            data-operator-not="{{ $servis->operator_not ?? '' }}"
                                            data-mevcut-durum-text="{{ $servis->servisDurum?->ad ?? 'Belirsiz' }}"
                                            data-mevcut-durum-class="{{ ($servisDurumStilleri[$servis->servisDurum?->ad ?? 'Belirsiz'] ?? 'bg-soft-secondary text-secondary') }}"
                                            data-servis-durum-id="{{ $servis->servis_durum_id ?? '' }}"
                                            >
                                            <td>
                                                <div class="d-flex align-items-center gap-3">
                                                    <a href="{{ $servis->musteri ? route('musteriler.show', $servis->musteri_id) : '#' }}" target="_blank">
                                                        {{-- Accessor otomatik çalışacak --}}
                                                        <span class="d-block">{{ $servis->musteri->ad ?? 'N/A' }}</span> 
                                                        @php
                                                            $telefon = $servis->musteri->tel1 ?? $servis->musteri->tel2 ?? 'Telefon Yok';
                                                        @endphp
                                                        <span class="fs-12 d-block fw-normal text-muted">{{ $telefon }}</span>
                                                    </a>
                                                </div>
                                            </td>
                                            <td>
                                                {{ $servis->musteri && $servis->musteri->ilce ? $servis->musteri->ilce->ad : 'N/A' }} / {{ $servis->musteri && $servis->musteri->il ? $servis->musteri->il->ad : 'N/A' }}
                                            </td>
                                            <td>
                                                <span class="d-block">{{ $servis->marka->ad ?? 'N/A' }} / {{ $servis->cihazTuru->ad ?? 'N/A' }}</span>
                                                <span class="fs-12 d-block fw-normal text-muted">{{ Str::limit($servis->cihaz_ariza ?? '', 30) }}</span>
                                            </td>
                                            <td>
                                                @php
                                                    $durumId = $servis->servis_durum_id;
                                                    $badgeClass = 'badge'; // Temel sınıf

                                                    // Bu ID'ler proposal.blade.php dosyasındaki mantıkla aynı olmalıdır
                                                    if (in_array($durumId, [9097, 9103, 9477])) { // Örnek ID'ler (Dark)
                                                        $badgeClass .= ' bg-soft-dark text-dark';
                                                    } elseif ($durumId == 9098) { // Teknisyen yönlendirildi (Gri)
                                                        $badgeClass .= ' bg-soft-secondary text-secondary';
                                                    } elseif (in_array($durumId, [9113, 9100])) { // Örnek ID'ler (Warning)
                                                        $badgeClass .= ' bg-soft-warning text-warning';
                                                    } elseif ($durumId == 9334) { // Örnek ID (Primary)
                                                        $badgeClass .= ' bg-soft-primary text-primary';
                                                    } elseif (in_array($durumId, [9115, 9114, 9105, 9099])) { // Örnek ID'ler (Success)
                                                        $badgeClass .= ' bg-soft-success text-success';
                                                    } else { // Diğer tüm durumlar (Danger veya varsayılan)
                                                        $badgeClass .= ' bg-soft-danger text-danger';
                                                    }
                                                @endphp
                                                <span class="{{ $badgeClass }}">{{ $servis->servisDurum->ad ?? 'N/A' }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="text-center py-3">Bugün için ithal marka servisi bulunamadı.</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($bugunIthalMarkaServisleri->count() > 0)
                    <div class="card-footer">
                        {{-- Basit bir 'Daha Fazla' linki veya sayfalama eklenebilir --}}
                        <a href="{{ route('servisler.index') }}" class="fs-11 fw-bold text-uppercase text-center d-block">TÜM SERVİSLER</a> 
                    </div>
                    @endif
                </div>
            </div>
            <!-- [Latest Leads] end -->
            @endif
            <!--! BEGIN: [Upcoming Schedule] !-->

            <!--! END: [Upcoming Schedule] !-->
            <!--! BEGIN: [Project Status] !-->

            <!--! END: [Project Status] !-->
            <!--! BEGIN: [Team Progress] !-->

            <!--! END: [Team Progress] !-->
        </div>
    </div>
    <!-- [ Main Content ] end -->
@endsection

@section('modals')
    {{-- Panel sayfasına özel ek modallar buraya gelebilir --}}
@endsection


@push('page_specific_vendor_js')
    <script src="{{ asset('crm_assets/vendors/js/daterangepicker.min.js') }}"></script>
    <script src="{{ asset('crm_assets/vendors/js/apexcharts.min.js') }}"></script>
    <script src="{{ asset('crm_assets/vendors/js/circle-progress.min.js') }}"></script>
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
@endpush

@push('page_specific_main_scripts')
    <script src="{{ asset('crm_assets/js/proposal.js') }}?v={{ filemtime(public_path('crm_assets/js/proposal.js')) }}"></script>
    <script src="{{ asset('crm_assets/js/helpers.js') }}"></script>
    <script>
        $(document).ready(function () {
            if (!$.fn.DataTable) {
                return;
            }
            if ($('#ithalServisTable').length && !$.fn.DataTable.isDataTable('#ithalServisTable')) {
                $('#ithalServisTable').DataTable({
                    paging: true,
                    pageLength: 20,
                    lengthChange: false,
                    searching: false,
                    info: false,
                    ordering: false,
                    dom: 'rt<"row"<"col-12 d-flex justify-content-center"p>>',
                    language: {
                        paginate: {
                            previous: 'Önceki',
                            next: 'Sonraki'
                        }
                    }
                });
            }
        });
    </script>
@endpush

@push('page_specific_init_js')
    <script src="{{ asset('crm_assets/js/dashboard-init.min.js') }}"></script>
@endpush

@push('page_specific_scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Son 7 Gün Chart
            if (typeof ApexCharts !== 'undefined' && document.querySelector("#panel-son-7-gun-chart")) {
                var options = {
                    chart: { type: 'bar', height: 380, toolbar: { show: false } },
                    plotOptions: { bar: { horizontal: false, columnWidth: '30%', endingShape: 'rounded' } },
                    dataLabels: { enabled: false },
                    stroke: { show: true, width: 2, colors: ['transparent'] },
                    series: [{
                        name: 'Kaydedilen',
                        data: @json($chartData['total'] ?? [])
                    }, {
                        name: 'İptal Edilen',
                        data: @json($chartData['cancelled'] ?? [])
                    }, {
                        name: 'Atölyeye Alınan',
                        data: @json($chartData['workshop'] ?? [])
                    }],
                    xaxis: {
                        categories: @json($chartData['dates'] ?? []),
                        axisBorder: { show: false }, axisTicks: { show: false },
                        labels: { style: { fontSize: "10px", colors: "#A0ACBB" } }
                    },
                    yaxis: {
                        labels: { formatter: function(val) { return parseInt(val); }, offsetX: -5, offsetY: 0, style: { color: "#A0ACBB" } }
                    },
                    fill: { opacity: 1 },
                    colors: ['#3454d1', '#dc3545', '#198754'],
                    tooltip: { y: { formatter: function (val) { return val + " servis" } }, style: { fontSize: "12px", fontFamily: "Inter" } },
                    grid: { xaxis: { lines: { show: false } }, yaxis: { lines: { show: false } }, borderColor: '#f1f1f1' },
                    legend: { show: true, position: 'top', horizontalAlign: 'right', fontFamily: "Inter", fontWeight: 500, fontSize: '12px', labels:{ colors: "#A0ACBB", fontFamily:"Inter" }, markers: { width: 10, height: 10 }, itemMargin: { horizontal: 10, vertical: 0 } }
                };
                var chart = new ApexCharts(document.querySelector("#panel-son-7-gun-chart"), options);
                try { chart.render(); } catch (error) { console.error("Grafik oluşturulurken hata:", error); }
            }

            // Mini Kart Grafikleri
            const miniChartOptions = {
                chart: { type: 'area', height: 70, toolbar: { show: false }, sparkline: { enabled: true } },
                stroke: { width: 2, curve: 'smooth' },
                fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.2, opacityTo: 0.75, stops: [0, 90, 100] } },
                grid: { show: false }, legend: { show: false }, dataLabels: { enabled: false },
                xaxis: { labels: { show: false }, axisBorder: { show: false }, axisTicks: { show: false } },
                yaxis: { labels: { show: false } }, tooltip: { enabled: false }
            };
            if (typeof ApexCharts !== 'undefined' && document.querySelector("#mini-ulasilamadi-chart")) {
                let chart1Options = JSON.parse(JSON.stringify(miniChartOptions));
                chart1Options.series = [{ name: 'Ulaşılamadı', data: @json($miniCardData['ulasilamadi']['daily'] ?? array_fill(0, 7, 0)) }];
                chart1Options.colors = ['#ffb1c1'];
                new ApexCharts(document.querySelector("#mini-ulasilamadi-chart"), chart1Options).render();
            }
            if (typeof ApexCharts !== 'undefined' && document.querySelector("#mini-haberverecek-chart")) {
                let chart2Options = JSON.parse(JSON.stringify(miniChartOptions));
                chart2Options.series = [{ name: 'Haber Verecek', data: @json($miniCardData['haberVerecek']['daily'] ?? array_fill(0, 7, 0)) }];
                chart2Options.colors = ['#FFC107'];
                new ApexCharts(document.querySelector("#mini-haberverecek-chart"), chart2Options).render();
            }
            if (typeof ApexCharts !== 'undefined' && document.querySelector("#mini-iptal-chart")) {
                let chart3Options = JSON.parse(JSON.stringify(miniChartOptions));
                chart3Options.series = [{ name: 'Müşteri İptal', data: @json($miniCardData['musteriIptal']['daily'] ?? array_fill(0, 7, 0)) }];
                chart3Options.colors = ['#dc3545'];
                new ApexCharts(document.querySelector("#mini-iptal-chart"), chart3Options).render();
            }

            // Kasa Özeti Grafiği
            if (typeof ApexCharts !== 'undefined' && document.querySelector("#kasa-summary-chart")) {
                var optionsKasa = {
                    chart: { type: 'area', height: 150, sparkline: { enabled: true } },
                    dataLabels: { enabled: false },
                    colors: [({{ $kasaSummary['todayNetCash'] ?? 0 }} >= 0 ? '#198754' : '#dc3545')], 
                    fill: { type: 'solid', opacity: 0.4 },
                    stroke: { curve: 'smooth', width: 3 },
                    series: [{ name: 'Net Kasa', data: @json($kasaSummary['netCashLast7Days'] ?? array_fill(0, 7, 0)) }],
                    xaxis: { categories: @json($kasaSummary['chartDates'] ?? []), labels: { show: true, style: { colors: '#A0ACBB', fontSize: '10px' } }, axisBorder: { show: false }, axisTicks: { show: false } },
                    yaxis: { min: 0 }, 
                    tooltip: { theme: 'dark', y: { formatter: function (val) { return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(val); } }, style: { fontSize: "12px", fontFamily: "Inter" } }
                };
                new ApexCharts(document.querySelector("#kasa-summary-chart"), optionsKasa).render();
            }

            // Bugünkü Servislerin İl Dağılımı Donut Grafiği
            if (typeof ApexCharts !== 'undefined' && document.querySelector("#servis-il-dagilimi-donut")) {
                var ilDagilimiOptions = {
                    chart: { type: 'donut', height: 350, fontFamily: 'Inter, sans-serif', toolbar: { show: false } },
                    series: @json($ilBazliServisDagilimi['counts'] ?? []),
                    labels: @json($ilBazliServisDagilimi['labels'] ?? []),
                    colors: ['#3454d1', '#0d519e', '#1976d2', '#1e88e5', '#2196f3', '#42a5f5', '#64b5f6', '#90caf9', '#aad6fa', '#cce5ff'],
                    legend: { show: false },
                    dataLabels: { enabled: true, formatter: function (val, opts) { return val.toFixed(1) + "%" }, style: { fontSize: '10px', colors: ['#fff'] }, dropShadow: { enabled: true, top: 1, left: 1, blur: 1, color: '#000', opacity: 0.35 } },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '65%',
                                labels: {
                                    show: true,
                                    name: { show: true, fontSize: '16px', fontFamily: 'Inter, sans-serif', color: '#495057', offsetY: -10 },
                                    value: { show: true, fontSize: '14px', fontFamily: 'Inter, sans-serif', color: '#495057', offsetY: 16, formatter: function (val) { return val; } },
                                    total: { show: true, showAlways: true, label: 'Toplam Servis', fontSize: '12px', fontFamily: 'Inter, sans-serif', color: '#6c757d', formatter: function (w) { return w.globals.seriesTotals.reduce((a, b) => { return a + b }, 0) } }
                                }
                            }
                        }
                    },
                    tooltip: { y: { formatter: function(value) { return value + " servis" } } }
                };
                new ApexCharts(document.querySelector("#servis-il-dagilimi-donut"), ilDagilimiOptions).render();
            }

            // Bugünkü Servislerin Marka Dağılımı Donut Grafiği
            if (typeof ApexCharts !== 'undefined' && document.querySelector("#servis-marka-dagilimi-donut")) {
                var markaCountsRaw = @json($markaBazliServisDagilimi['counts'] ?? []);
                if (markaCountsRaw.length > 0) {
                    var markaDagilimiOptions = {
                        chart: { type: 'donut', height: 350, fontFamily: 'Inter, sans-serif', toolbar: { show: false } },
                        series: markaCountsRaw,
                        labels: @json($markaBazliServisDagilimi['labels'] ?? []),
                        colors: ['#e65100', '#f57c00', '#fb8c00', '#ffa726', '#ffb74d', '#ff9800', '#ef6c00', '#ff6f00', '#ff9100', '#ffe0b2'],
                        legend: { show: false },
                        dataLabels: { enabled: true, formatter: function (val, opts) { return val.toFixed(1) + "%" }, style: { fontSize: '10px', colors: ['#fff'] }, dropShadow: { enabled: true, top: 1, left: 1, blur: 1, color: '#000', opacity: 0.35 } },
                        plotOptions: {
                            pie: {
                                donut: {
                                    size: '65%',
                                    labels: {
                                        show: true,
                                        name: { show: true, fontSize: '16px', fontFamily: 'Inter, sans-serif', color: '#495057', offsetY: -10 },
                                        value: { show: true, fontSize: '14px', fontFamily: 'Inter, sans-serif', color: '#495057', offsetY: 16, formatter: function (val) { return val; } },
                                        total: { show: true, showAlways: true, label: 'Toplam Servis', fontSize: '12px', fontFamily: 'Inter, sans-serif', color: '#6c757d', formatter: function (w) { return w.globals.seriesTotals.reduce((a, b) => { return a + b }, 0) } }
                                    }
                                }
                            }
                        },
                        tooltip: { y: { formatter: function(value) { return value + " servis" } } }
                    };
                    new ApexCharts(document.querySelector("#servis-marka-dagilimi-donut"), markaDagilimiOptions).render();
                }
            }
        });
    </script>
@endpush

@push('page_specific_scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var form = document.getElementById('ilKazancFilterForm');
            var wrapper = document.getElementById('ilKazancTableWrapper');
            if (!form || !wrapper) return;
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                var url = form.getAttribute('action');
                var params = new URLSearchParams(new FormData(form)).toString();
                wrapper.innerHTML = '<p class="text-center text-muted mb-0">Yükleniyor...</p>';
                fetch(url + (params ? ('?' + params) : ''), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function(res){ return res.json(); })
                    .then(function(data){
                        if (data && data.html) {
                            wrapper.innerHTML = data.html;
                        } else {
                            wrapper.innerHTML = '<p class="text-center text-muted mb-0">Veri bulunamadı.</p>';
                        }
                        try { window.history.replaceState({}, document.title, window.location.pathname); } catch (e) { /* no-op */ }
                    })
                    .catch(function(){
                        wrapper.innerHTML = '<p class="text-center text-danger mb-0">Veriler yüklenemedi.</p>';
                    });
            });
        });
    </script>
@endpush

</body>

</html>
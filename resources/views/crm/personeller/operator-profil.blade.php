@extends('layouts.app')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;
    $operatorPozisyonId = 1073;
    $hariciOperatorPozisyonId = 1076;
    $profileTitle = 'Operatör Profili';
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
        $canSeeKasaCards = !$loggedInUser || !in_array((int) $loggedInUser->poz_id, [$operatorPozisyonId, $hariciOperatorPozisyonId], true);
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
                                        {{-- Personel Aktif Durumu İçin Check İşareti --}}
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
                            <div class="col-12 d-flex flex-column">
                                <div class="card border-0 h-100 flex-grow-1 d-flex flex-column">
                                    <div class="card-header d-flex align-items-center justify-content-between p-3">
                                        <h5 class="mb-0">Sondaj Takibi</h5>
                                    </div>
                                    <div class="card-body d-flex flex-column gap-4">
                                        <div class="d-flex flex-wrap align-items-center justify-content-between">
                                            <div class="text-muted">İş başı kazanç</div>
                                            <div class="fw-semibold text-dark">{{ number_format($operatorKazancBirimTutar ?? 0, 2, ',', '.') }} TL</div>
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <div class="border border-dashed rounded p-3 h-100">
                                                    <div class="fs-12 text-muted">Bugün</div>
                                                    <div class="fw-bold">{{ $operatorKazancBugunAdet ?? 0 }} iş</div>
                                                    <div class="text-success fw-semibold">{{ number_format($operatorKazancBugunTutar ?? 0, 2, ',', '.') }} TL</div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="border border-dashed rounded p-3 h-100">
                                                    <div class="fs-12 text-muted">Dün</div>
                                                    <div class="fw-bold">{{ $operatorKazancDunAdet ?? 0 }} iş</div>
                                                    <div class="text-success fw-semibold">{{ number_format($operatorKazancDunTutar ?? 0, 2, ',', '.') }} TL</div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="border border-dashed rounded p-3 h-100">
                                                    <div class="fs-12 text-muted">Bu Ay Toplam</div>
                                                    <div class="fw-bold">{{ $operatorKazancAyAdet ?? 0 }} iş</div>
                                                    <div class="text-success fw-semibold">{{ number_format($operatorKazancAyTutar ?? 0, 2, ',', '.') }} TL</div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>SERVİS</th>
                                                        <th>MÜŞTERİ</th>
                                                        <th>DURUM</th>
                                                        <th>TARİH</th>
                                                        <th>KAZANÇ</th>
                                                        <th>ÖDEME</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($operatorKazancKayitlar as $kayit)
                                                        <tr>
                                                            <td>
                                                                <a href="javascript:void(0);" class="servis-detay-ac-btn" data-servis-id="{{ $kayit->servis_id }}">#{{ $kayit->servis_id }}</a>
                                                            </td>
                                                            <td>{{ $kayit->servis->musteri->ad ?? 'N/A' }}</td>
                                                            <td>{{ $kayit->servis->servisDurum->ad ?? 'N/A' }}</td>
                                                            <td>{{ $kayit->kazanım_tarihi ? \Carbon\Carbon::parse($kayit->kazanım_tarihi)->format('d.m.Y') : '' }}</td>
                                                            <td class="fw-bold text-success">{{ number_format($operatorKazancBirimTutar ?? 0, 2, ',', '.') }} TL</td>
                                                            <td>{{ $kayit->odeme_sekli->ad ?? '-' }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="6" class="text-center text-muted">Henüz kazanç kaydı yok.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                        @if($operatorKazancKayitlar instanceof \Illuminate\Pagination\LengthAwarePaginator && $operatorKazancKayitlar->hasPages())
                                            <div class="mt-3">
                                                {{ $operatorKazancKayitlar->links() }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
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
                            {{-- Son 7 Gün Detayı ve Tekrar Arıza grafikleri kaldırıldı --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- Sekmeli servis listeleri kaldırıldı --}}

    </div>
    <!-- [ Main Content ] end -->
@endsection

@push('page_specific_vendor_js')
<script src="{{ asset('crm_assets/vendors/js/apexcharts.min.js') }}"></script>
@endpush

@push('page_specific_scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var kasaChartData = @json($kasaChartData);

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
                    min: 0,
                    forceNiceScale: true,
                    tickAmount: 5
                },
                colors: ['#198754', '#dc3545'],
                tooltip: { y: { formatter: function (val) { return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(val); } }, style: { fontSize: "12px", fontFamily: "Inter" } },
                grid: {
                    show: false,
                    padding: { top: 0, right: 0, bottom: 0, left: 0 }
                },
                legend: { show: true, position: 'top', horizontalAlign: 'right', fontFamily: "Inter", fontWeight: 500, fontSize: '12px', labels:{ colors: "#A0ACBB", fontFamily:"Inter" }, markers: { width: 10, height: 10 }, itemMargin: { horizontal: 10, vertical: 0 } }
            };
            var kasaHareketleriApexChart = new ApexCharts(document.querySelector("#kasaHareketleriChart"), kasaHareketleriOptions);
            try { kasaHareketleriApexChart.render(); } catch (error) { console.error("Kasa hareketleri grafiği oluşturulurken hata:", error); }
        }
    });

    // Bekleyen işler ve diğer sekmelerdeki servis ID linklerine tıklandığında modalı aç
    document.addEventListener('click', function(e) {
        var target = e.target;
        if (target && target.classList && target.classList.contains('servis-detay-ac-btn')) {
            e.preventDefault();
            var servisId = target.getAttribute('data-servis-id');
            if (servisId && window.openServisDetay) {
                window.openServisDetay(servisId);
            }
        }
    });
</script>
@endpush

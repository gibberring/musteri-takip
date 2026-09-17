@extends('layouts.app')

@section('title', 'Silinen Kayıtlar - Diğer')

@section('content')
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Silinen Kayıtlar</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('panel') }}">Ana Sayfa</a></li>
            <li class="breadcrumb-item"><a href="{{ route('settings.index') }}">Ayarlar</a></li>
            <li class="breadcrumb-item">Silinen Kayıtlar</li>
            <li class="breadcrumb-item">Diğer</li>
        </ul>
    </div>
</div>

<div class="card shadow-sm mt-3">
    @include('settings.partials.deleted-records-nav', ['active' => 'diger'])

    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success mb-3">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger mb-3">{{ session('error') }}</div>
        @endif

        @include('settings.partials.deleted-records-date-filter', [
            'tarih1Id' => 'digerTarih1',
            'tarih2Id' => 'digerTarih2',
        ])

        <ul class="nav nav-tabs" id="digerTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="islemlog-tab" data-bs-toggle="tab" data-bs-target="#islemlog" type="button" role="tab">İşlem Logları</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="musteri-guncelleme-tab" data-bs-toggle="tab" data-bs-target="#musteri-guncelleme" type="button" role="tab">Müşteri ad / tel güncellemeleri</button>
            </li>
        </ul>

        <div class="tab-content pt-3">
            <div class="tab-pane fade show active" id="islemlog" role="tabpanel">
                <div class="d-flex justify-content-end mb-2">
                    <input type="search" id="islemLogSearchInput" class="form-control form-control-sm w-auto" style="max-width: 280px;" placeholder="Servis, açıklama, yapan, durum, tarih..." autocomplete="off" aria-label="İşlem loglarında ara">
                </div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle" id="deletedIslemLogTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Silinme Tarihi</th>
                                <th>Servis</th>
                                <th>Durum</th>
                                <th>Açıklama</th>
                                <th>Silen Kişi</th>
                                <th class="text-end">Aksiyon</th>
                            </tr>
                        </thead>
                        <tbody id="deletedIslemLogTbody">
                            @include('settings.partials.deleted-islemlog-rows')
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="tab-pane fade" id="musteri-guncelleme" role="tabpanel">
                <p class="text-muted small mb-2">Müşteri kaydında <strong>ad</strong>, <strong>tel1</strong> veya <strong>tel2</strong> alanı değiştiğinde kayıt burada listelenir (üstteki tarih filtresine göre, en fazla 500 kayıt).</p>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th>Tarih</th>
                                <th>Müşteri</th>
                                <th>İşlemi yapan</th>
                                <th>Özet</th>
                            </tr>
                        </thead>
                        <tbody id="deletedMusteriGuncellemeTbody">
                            @include('settings.partials.deleted-musteri-guncelleme-rows')
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page_specific_main_scripts')
@include('settings.partials.deleted-records-scripts')
<script>
    window.bindDeletedFilter({
        tarih1Id: 'digerTarih1',
        tarih2Id: 'digerTarih2',
        searchInputId: 'islemLogSearchInput',
        tbodyId: 'deletedIslemLogTbody',
        searchUrl: @json(route('settings.deletedRecords.searchIslemLog')),
        colCount: 7
    });
    window.bindDeletedFilter({
        tarih1Id: 'digerTarih1',
        tarih2Id: 'digerTarih2',
        tbodyId: 'deletedMusteriGuncellemeTbody',
        searchUrl: @json(route('settings.deletedRecords.searchMusteriGuncelleme')),
        colCount: 4
    });
</script>
@endpush

@extends('layouts.app')

@section('title', 'Silinen Kayıtlar - Servis')

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
            <li class="breadcrumb-item">Servis</li>
        </ul>
    </div>
</div>

<div class="card shadow-sm mt-3">
    @include('settings.partials.deleted-records-nav', ['active' => 'servis'])

    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success mb-3">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger mb-3">{{ session('error') }}</div>
        @endif

        @include('settings.partials.deleted-records-date-filter', [
            'tarih1Id' => 'servisTarih1',
            'tarih2Id' => 'servisTarih2',
            'searchInputId' => 'servisSearchInput',
            'searchPlaceholder' => 'Servis, müşteri, telefon, teknisyen, durum, marka...',
        ])

        <div class="table-responsive">
            <table class="table table-sm align-middle" id="deletedServisTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Silinme Tarihi</th>
                        <th>Müşteri</th>
                        <th>Teknisyen</th>
                        <th>Durum</th>
                        <th>Silen Kişi</th>
                        <th class="text-end">Aksiyon</th>
                    </tr>
                </thead>
                <tbody id="deletedServisTbody">
                    @include('settings.partials.deleted-servis-rows')
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('page_specific_main_scripts')
@include('settings.partials.deleted-records-scripts')
<script>
    window.bindDeletedFilter({
        tarih1Id: 'servisTarih1',
        tarih2Id: 'servisTarih2',
        searchInputId: 'servisSearchInput',
        tbodyId: 'deletedServisTbody',
        searchUrl: @json(route('settings.deletedRecords.searchServis')),
        colCount: 7
    });
</script>
@endpush

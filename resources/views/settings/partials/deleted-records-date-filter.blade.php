<div class="row g-2 align-items-end mb-3">
    <div class="col-auto">
        <label for="{{ $tarih1Id }}" class="form-label form-label-sm mb-1">Silinme Tarihi (başlangıç)</label>
        <input type="date" id="{{ $tarih1Id }}" class="form-control form-control-sm" value="{{ $tarih1 ?? '' }}">
    </div>
    <div class="col-auto">
        <label for="{{ $tarih2Id }}" class="form-label form-label-sm mb-1">Silinme Tarihi (bitiş)</label>
        <input type="date" id="{{ $tarih2Id }}" class="form-control form-control-sm" value="{{ $tarih2 ?? '' }}">
    </div>
    @isset($searchInputId)
        <div class="col flex-grow-1" style="min-width: 220px;">
            <label for="{{ $searchInputId }}" class="form-label form-label-sm mb-1">Ara</label>
            <input type="search" id="{{ $searchInputId }}" class="form-control form-control-sm" placeholder="{{ $searchPlaceholder ?? 'Ara...' }}" autocomplete="off">
        </div>
    @endisset
</div>

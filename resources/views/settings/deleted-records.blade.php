@extends('layouts.app')

@section('title', 'Silinen Kayıtlar')

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
        </ul>
    </div>
</div>

<div class="card shadow-sm mt-3">
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success mb-3">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger mb-3">{{ session('error') }}</div>
        @endif
        <ul class="nav nav-tabs" id="deletedRecordsTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="servis-tab" data-bs-toggle="tab" data-bs-target="#servis" type="button" role="tab">Servis</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="kasa-tab" data-bs-toggle="tab" data-bs-target="#kasa" type="button" role="tab">Kasa</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="islemlog-tab" data-bs-toggle="tab" data-bs-target="#islemlog" type="button" role="tab">İşlem Logları</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="musteri-guncelleme-tab" data-bs-toggle="tab" data-bs-target="#musteri-guncelleme" type="button" role="tab">Müşteri ad / tel güncellemeleri</button>
            </li>
        </ul>

        <div class="tab-content pt-3">
            <div class="tab-pane fade show active" id="servis" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
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
                        <tbody>
                            @forelse($silinenServisler as $servis)
                                <tr data-row-id="servis-{{ $servis->id }}">
                                    <td>#{{ $servis->id }}</td>
                                    <td>{{ $servis->silinme_tarihi ?? '-' }}</td>
                                    <td>{{ $servis->musteri?->ad ?? '-' }}</td>
                                    <td>{{ $servis->personel?->ad ?? '-' }}</td>
                                    <td>{{ $servis->servisDurum?->ad ?? '-' }}</td>
                                    <td>{{ $servis->silenKisi?->ad ?? '-' }}</td>
                                    <td class="text-end">
                                        <form method="POST" action="{{ route('settings.deletedRecords.restoreServis', $servis->id) }}" class="d-inline restore-form">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success restore-btn" data-restore-url="{{ route('settings.deletedRecords.restoreServis', $servis->id) }}" onclick="return window.restoreDeletedRecord(this);">
                                                <i class="feather-rotate-ccw"></i> Geri Al
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted">Silinen servis kaydı bulunamadı.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="tab-pane fade" id="kasa" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Silinme Tarihi</th>
                                <th>Servis</th>
                                <th>Teknisyen</th>
                                <th>Ödeme Türü</th>
                                <th>Tutar</th>
                                <th>Silen Kişi</th>
                                <th class="text-end">Aksiyon</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($silinenKasa as $kasa)
                                <tr data-row-id="kasa-{{ $kasa->id }}">
                                    <td>#{{ $kasa->id }}</td>
                                    <td>{{ $kasa->silinme_tarihi ?? '-' }}</td>
                                    <td>{{ $kasa->servis?->id ? '#'.$kasa->servis->id : '-' }}</td>
                                    <td>{{ $kasa->ilgiliPersonel?->ad ?? $kasa->personel?->ad ?? '-' }}</td>
                                    <td>{{ $kasa->odemeTuru?->ad ?? '-' }}</td>
                                    <td>{{ number_format((float) $kasa->tutar, 2, ',', '.') }} TL</td>
                                    <td>{{ $kasa->silenKisi?->ad ?? '-' }}</td>
                                    <td class="text-end">
                                        <form method="POST" action="{{ route('settings.deletedRecords.restoreKasa', $kasa->id) }}" class="d-inline restore-form">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success restore-btn" data-restore-url="{{ route('settings.deletedRecords.restoreKasa', $kasa->id) }}" onclick="return window.restoreDeletedRecord(this);">
                                                <i class="feather-rotate-ccw"></i> Geri Al
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-muted">Silinen kasa kaydı bulunamadı.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="tab-pane fade" id="islemlog" role="tabpanel">
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
                <p class="text-muted small mb-2">Müşteri kaydında <strong>ad</strong>, <strong>tel1</strong> veya <strong>tel2</strong> alanı değiştiğinde kayıt burada listelenir (son 500 kayıt).</p>
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
                        <tbody>
                            @forelse($musteriIletisimGuncellemeleri as $row)
                                <tr>
                                    <td class="text-nowrap">{{ $row->created_at ? $row->created_at->format('d.m.Y H:i') : '-' }}</td>
                                    <td>#{{ $row->subject_id }}</td>
                                    <td>{{ $row->personel?->ad ?? '-' }}</td>
                                    <td class="small">{{ $row->summary }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">Henüz kayıt yok.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page_specific_main_scripts')
<script>
(function () {
    var input = document.getElementById('islemLogSearchInput');
    var tbody = document.getElementById('deletedIslemLogTbody');
    var searchUrl = @json(route('settings.deletedRecords.searchIslemLog'));
    var timer = null;
    var xhr = null;
    var lastQ = null;

    function setTbodyHtml(html) {
        if (tbody) tbody.innerHTML = html;
    }

    function loadIslemLogs(q) {
        if (lastQ === q) return;
        if (q === '' && lastQ === null) return;
        if (xhr && xhr.abort) xhr.abort();
        setTbodyHtml('<tr><td colspan="7" class="text-center text-muted">Aranıyor...</td></tr>');
        xhr = new XMLHttpRequest();
        xhr.open('GET', searchUrl + '?q=' + encodeURIComponent(q), true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.onreadystatechange = function () {
            if (xhr.readyState !== 4) return;
            if (xhr.status === 0) return;
            var data = null;
            try { data = JSON.parse(xhr.responseText || ''); } catch (e) { data = null; }
            if (xhr.status >= 200 && xhr.status < 300 && data && typeof data.html === 'string') {
                lastQ = q;
                setTbodyHtml(data.html);
                return;
            }
            lastQ = null;
            setTbodyHtml('<tr><td colspan="7" class="text-center text-danger">Arama başarısız.</td></tr>');
        };
        xhr.send();
    }

    if (input) {
        input.addEventListener('input', function () {
            clearTimeout(timer);
            timer = setTimeout(function () {
                loadIslemLogs((input.value || '').trim());
            }, 300);
        });
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(timer);
                loadIslemLogs((input.value || '').trim());
            }
        });
    }
})();

window.restoreDeletedRecord = function (btnEl) {
    try {
        if (!btnEl) return false;
        var btn = btnEl;
        var form = btn.closest('form');
        var row = btn.closest('tr');
        var url = btn.getAttribute('data-restore-url') || (form ? form.getAttribute('action') : null);
        var tokenEl = form ? form.querySelector('input[name="_token"]') : null;
        var token = (tokenEl && tokenEl.value) || (document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '');

        function showError(msg) {
            if (window.Swal && Swal.fire) {
                Swal.fire('Hata', msg || 'İşlem başarısız.', 'error');
            } else {
                alert(msg || 'İşlem başarısız.');
            }
        }

        function doRestore() {
            if (!url) { showError('İşlem adresi bulunamadı.'); return; }
            var xhr = new XMLHttpRequest();
            xhr.open('POST', url, true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
            if (token) {
                xhr.setRequestHeader('X-CSRF-TOKEN', token);
            }
            xhr.onreadystatechange = function () {
                if (xhr.readyState !== 4) return;
                var text = xhr.responseText || '';
                var data = null;
                try { data = JSON.parse(text); } catch (e) { data = null; }
                if (xhr.status >= 200 && xhr.status < 300 && data && data.success) {
                    if (row) row.remove();
                    if (window.Swal && Swal.fire) {
                        Swal.fire('Başarılı', data.message || 'Kayıt geri alındı.', 'success');
                    }
                    return;
                }
                var msg = (data && data.message) ? data.message : (text || 'İşlem başarısız.');
                showError(msg);
            };
            xhr.onerror = function () { showError('İşlem başarısız.'); };
            xhr.send('_token=' + encodeURIComponent(token || ''));
        }

        if (window.Swal && Swal.fire) {
            Swal.fire({
                title: 'Emin misiniz?',
                text: 'Kaydı geri almak istiyor musunuz?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Evet, geri al',
                cancelButtonText: 'Vazgeç'
            }).then(function (result) {
                if (result && (result.isConfirmed || result.value)) {
                    doRestore();
                }
            });
        } else {
            if (confirm('Kaydı geri almak istiyor musunuz?')) {
                doRestore();
            }
        }
        return false;
    } catch (e) {
        return false;
    }
};
</script>
@endpush

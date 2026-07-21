@extends('layouts.app')

@section('title', 'Ayarlar')

@section('content')
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Ayarlar</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('panel') }}">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Ayarlar</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto"></div>
    </div>

    <div class="row g-3 mt-3 justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#genelAyarModal"><i class="feather-settings me-2"></i>Genel Ayarlar</button>
                    <div class="text-muted small">Genel uygulama ayarları için bu butonu kullanın.</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#personelAyarModal"><i class="feather-sliders me-2"></i>Personel Ayarları</button>
                    <div class="text-muted small">Personel yetkilerini ayarlamak için bu butonu kullanın.</div>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-3 mt-3 justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#cihazTurModal"><i class="feather-layers me-2"></i>CİHAZ TÜRLERİ</button>
                    <div class="text-muted small">Cihaz türlerini ekleyin, düzenleyin veya silin.</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#markalarModal"><i class="feather-box me-2"></i>CİHAZ MARKALARI</button>
                    <div class="text-muted small">Markaları ekleyin, düzenleyin veya silin.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-3 justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#whatsappApiModal"><i class="feather-message-circle me-2"></i>Whatsapp API</button>
                    <div class="text-muted small">Whatsapp API ayarlarını buradan yapın.</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#duyuruAyarModal"><i class="feather-bell me-2"></i>Duyurular</button>
                    <div class="text-muted small">Patron duyurularını burada yönetin.</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#bolgeAyarModal"><i class="feather-map-pin me-2"></i>Bölge Ayarları</button>
                    <div class="text-muted small">Aktif il/ilçe seçimlerinizi yönetin.</div>
                </div>
            </div>
        </div>
        @if(auth()->user() && (int) auth()->user()->poz_id === 1071)
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <a href="{{ route('settings.deletedRecords.index') }}" class="btn btn-outline-primary">
                            <i class="feather-trash-2 me-2"></i>SİLİNEN KAYITLAR
                        </a>
                        <div class="text-muted small">Silinen servis, kasa ve işlem logları ile müşteri ad/telefon güncelleme günlüğünü görüntüleyin.</div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    
@endsection

@section('modals')
    @include('partials.modals.settings-personel')
    @include('partials.modals.settings-general')
    @include('partials.modals.settings-markalar')
    @include('partials.modals.settings-cihaztur')
    @include('partials.modals.settings-announcements')
    @include('partials.modals.settings-regions')
    @include('partials.modals.settings-whatsapp')
@endsection

@push('page_specific_css')
<style>
    .table thead th { position: sticky; top: 0; background: #f8f9fa; z-index: 1; }
    .izin-check .form-check-input { cursor: pointer; width: 1.05rem; height: 1.05rem; border-radius: .25rem; }
    .izin-check .form-check-input:focus { box-shadow: none; }
    .izin-check .form-check-input:checked { background-color: #2f55d4; border-color: #2f55d4; }
    .card .card-body { padding: 1rem 1rem; }
    .table tbody td { vertical-align: middle; }
    .table-responsive { max-height: 70vh; }
    .ayar-mini-input{ height: 28px; padding: 2px 8px; font-size: 0.85rem; }
}</style>
@endpush

@push('page_specific_main_scripts')
<script>
$(function(){
    $(document).on('click', '#izinleriKaydetBtn', function(){
        var payload = {};
        $('.izin-checkbox').each(function(){
            var role = $(this).data('role');
            var ab = $(this).data('ability');
            var checked = $(this).is(':checked');
            if (!payload[role]) payload[role] = {};
            payload[role][ab] = checked;
        });
        var btn = $(this);
        var old = btn.html();
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Kaydediliyor...');
        $.ajax({
            url: '{{ route('settings.update') }}',
            method: 'POST',
            data: JSON.stringify({ abilities: payload, _token: $('meta[name="csrf-token"]').attr('content') }),
            contentType: 'application/json; charset=UTF-8',
            dataType: 'json',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        }).done(function(resp){
            if (window.Swal && Swal.fire) { Swal.fire('Başarılı', (resp && resp.message) ? resp.message : 'İzinler güncellendi.', 'success'); }
            var modalEl = document.getElementById('personelAyarModal');
            if (modalEl) { var m = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl); m.hide(); }
        }).fail(function(xhr){
            if (window.Swal && Swal.fire) { Swal.fire('Hata', (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Kayıt başarısız.', 'error'); } else { alert('Kayıt başarısız.'); }
        }).always(function(){
            btn.prop('disabled', false).html(old);
        });
    });
});

// Duyurular yönetimi
$(document).on('show.bs.modal', '#duyuruAyarModal', function(){
    var tbody = $('#duyurularTable tbody');
    tbody.html('<tr><td colspan="6" class="text-center">Yükleniyor...</td></tr>');
    $.getJSON('{{ route('settings.annc.index') }}', function(resp){
        if (!resp || !resp.success) { tbody.html('<tr><td colspan="6" class="text-center text-danger">Yüklenemedi</td></tr>'); return; }
        var html='';
        (resp.items||[]).forEach(function(it){
            html += '<tr data-id="'+it.id+'">'
                 + '<td style="width:22%"><input class="form-control form-control-sm ayar-mini-input" value="'+(it.baslik||'')+'" /></td>'
                 + '<td style="width:16%"><select class="form-select form-select-sm ayar-mini-input"><option value="TEKNISYEN"'+(it.hedef_rol==='TEKNISYEN'?' selected':'')+'>TEKNİSYEN</option><option value="OPERATOR"'+(it.hedef_rol==='OPERATOR'?' selected':'')+'>OPERATÖR</option></select></td>'
                 + '<td style="width:10%"><select class="form-select form-select-sm ayar-mini-input"><option value="1"'+(it.aktif? ' selected':'')+'>Evet</option><option value="0"'+(!it.aktif? ' selected':'')+'>Hayır</option></select></td>'
                 + '<td style="width:18%"><input type="datetime-local" class="form-control form-control-sm ayar-mini-input" value="'+(it.published_at? it.published_at.replace(' ','T').slice(0,16):'')+'" /></td>'
                 + '<td style="width:34%"><div class="form-control form-control-sm" style="height:auto; min-height:42px; white-space:pre-wrap; overflow:auto; max-height:120px;">'+(it.icerik? it.icerik.replaceAll('<','&lt;') : '')+'</div></td>'
                 + '<td class="text-end">'
                 + '<button class="btn btn-sm btn-primary duyuru-kaydet-btn"><i class="feather-save"></i></button> '
                 + '<button class="btn btn-sm btn-danger duyuru-sil-btn"><i class="feather-trash-2"></i></button>'
                 + '</td>'
                 + '</tr>';
        });
        tbody.html(html||'<tr><td colspan="6" class="text-center">Kayıt yok</td></tr>');
    });
});

// Bölge Ayarları
$(document).on('show.bs.modal', '#bolgeAyarModal', function(){
    const ilForIlceList = $('#ilForIlceList');
    const ilcelerWrap = $('#ilcelerList');
    ilForIlceList.html(''); ilcelerWrap.html('<div class="text-center text-muted">Yükleniyor...</div>');

    $.getJSON('{{ route('settings.regions.index') }}', function(resp){
        if (!resp || !resp.success) { ilForIlceList.html('<div class="text-center text-danger">Yüklenemedi</div>'); return; }

        // Global state: seçili ilçe ids
        var ilceIdToIlId = {};
        (resp.ilceler||[]).forEach(function(i){ ilceIdToIlId[String(i.id)] = Number(i.il_id); });
        var selectedSet = new Set((resp.ilceler||[]).filter(function(x){ return !!x.aktif; }).map(function(x){ return String(x.id); }));
        window._regionsSelectedIlceIds = selectedSet;

        function activeCountForIl(ilId){
            var count = 0;
            selectedSet.forEach(function(id){ if (ilceIdToIlId[id] === Number(ilId)) { count++; } });
            return count;
        }

        function buildIlList(){
            ilForIlceList.html('');
            (resp.iller||[]).forEach(function(il){
                var activeCnt = activeCountForIl(il.id);
                var row = '<a href="#" class="list-group-item list-group-item-action il-select d-flex justify-content-between align-items-center" data-il="'+il.id+'">'
                        + '<span>'+il.ad+'</span>'
                        + '<span class="badge bg-success ms-2 il-badge'+(activeCnt>0?'':' d-none')+'">Aktif</span>'
                        + '</a>';
                ilForIlceList.append(row);
            });
        }

        function renderIlceler(ilId){
            var html='';
            (resp.ilceler||[]).filter(function(x){ return Number(x.il_id)===Number(ilId); }).forEach(function(ilce){
                var checked = selectedSet.has(String(ilce.id)) ? ' checked' : '';
                html += '<div class="col-md-4 mb-2">'
                     +   '<label class="form-check small">'
                     +     '<input class="form-check-input ilce-check" type="checkbox" value="'+ilce.id+'"'+checked+'> '
                     +     '<span class="form-check-label">'+ilce.ad+'</span>'
                     +   '</label>'
                     + '</div>';
            });
            ilcelerWrap.html(html||'<div class="text-center text-muted">Kayıt yok</div>');

            // Bağla: ilçe seçimleri state'e yazılsın
            ilcelerWrap.off('change', '.ilce-check').on('change', '.ilce-check', function(){
                var id = String($(this).val());
                if ($(this).is(':checked')) { selectedSet.add(id); } else { selectedSet.delete(id); }
                // Sol listedeki ilgili ilin rozeti güncelle
                var ilId = ilceIdToIlId[id];
                var badge = ilForIlceList.find('.il-select[data-il="'+ilId+'"]').find('.il-badge');
                if (activeCountForIl(ilId) > 0) { badge.removeClass('d-none'); } else { badge.addClass('d-none'); }
            });
        }

        buildIlList();
        var firstIl = $('#ilForIlceList .il-select').first();
        firstIl.addClass('active');
        var currentIlId = firstIl.data('il');
        if (currentIlId) { renderIlceler(currentIlId); }

        $('#ilForIlceList').off('click', '.il-select').on('click', '.il-select', function(e){
            e.preventDefault();
            $('#ilForIlceList .il-select').removeClass('active');
            $(this).addClass('active');
            renderIlceler($(this).data('il'));
        });

        $('#ilcelerSelectAll').off('click').on('click', function(){
            var ilId = $('#ilForIlceList .il-select.active').data('il');
            $('.ilce-check').each(function(){ var id = String($(this).val()); if (!$(this).is(':checked')) { $(this).prop('checked', true); selectedSet.add(id); } });
            var badge = ilForIlceList.find('.il-select[data-il="'+ilId+'"]').find('.il-badge');
            if (activeCountForIl(ilId) > 0) { badge.removeClass('d-none'); }
        });
        $('#ilcelerClearAll').off('click').on('click', function(){
            var ilId = $('#ilForIlceList .il-select.active').data('il');
            $('.ilce-check').each(function(){ var id = String($(this).val()); if ($(this).is(':checked')) { $(this).prop('checked', false); selectedSet.delete(id); } });
            var badge = ilForIlceList.find('.il-select[data-il="'+ilId+'"]').find('.il-badge');
            if (activeCountForIl(ilId) === 0) { badge.addClass('d-none'); }
        });
    });
});

$(document).on('click', '#bolgeAyarKaydetBtn', function(){
    var ilceIds = Array.from(window._regionsSelectedIlceIds || []);
    $.post('{{ route('settings.regions.save') }}', { aktifIlceIds: ilceIds, _token: $('meta[name="csrf-token"]').attr('content') })
      .done(function(resp){ Swal && Swal.fire('Başarılı', (resp && resp.message)||'Kaydedildi', 'success'); })
      .fail(function(){ Swal && Swal.fire('Hata','Kaydetme başarısız','error'); });
});

$(document).on('click', '#duyuruEkleBtn', function(){
    var data = {
        baslik: $('#duyuruYeniBaslik').val(),
        icerik: $('#duyuruYeniIcerik').val(),
        hedef_rol: $('#duyuruYeniHedefRol').val(),
        aktif: $('#duyuruYeniAktif').val(),
        published_at: $('#duyuruYeniYayin').val(),
        _token: $('meta[name="csrf-token"]').attr('content')
    };
    $.post('{{ route('settings.annc.store') }}', data)
     .done(function(){ $('#duyuruAyarModal').trigger('show.bs.modal'); $('#duyuruYeniBaslik').val(''); $('#duyuruYeniIcerik').val(''); })
     .fail(function(){ Swal && Swal.fire('Hata','Duyuru eklenemedi','error'); });
});

$(document).on('click', '.duyuru-kaydet-btn', function(){
    var tr = $(this).closest('tr');
    var id = tr.data('id');
    var baslik = tr.find('td:eq(0) input').val();
    var hedef = tr.find('td:eq(1) select').val();
    var aktif = tr.find('td:eq(2) select').val();
    var yayin = tr.find('td:eq(3) input').val();
    var icerik = tr.find('td:eq(4) textarea').val();
    $.post('{{ url('/ayarlar/duyurular') }}/'+id, { _method:'POST', baslik: baslik, icerik: icerik, hedef_rol: hedef, aktif: aktif, published_at: yayin, _token: $('meta[name="csrf-token"]').attr('content') })
     .done(function(){ Swal && Swal.fire('Kaydedildi','','success'); })
     .fail(function(){ Swal && Swal.fire('Hata','Kaydedilemedi','error'); });
});

$(document).on('click', '.duyuru-sil-btn', function(){
    var tr = $(this).closest('tr');
    var id = tr.data('id');
    $.ajax({ url:'{{ url('/ayarlar/duyurular') }}/'+id, method:'POST', data:{ _method:'DELETE', _token: $('meta[name="csrf-token"]').attr('content') } })
     .done(function(){ tr.remove(); })
     .fail(function(){ Swal && Swal.fire('Hata','Silinemedi','error'); });
});
// Markalar modal load and actions
$(document).on('show.bs.modal', '#markalarModal', function(){
    var left = $('#markalarTableLeft tbody');
    var right = $('#markalarTableRight tbody');
    left.html('<tr><td colspan="2">Yükleniyor...</td></tr>');
    right.empty();
    $.getJSON('{{ route('settings.markalar.index') }}', function(list){
        var half = Math.ceil((list||[]).length/2);
        var leftHtml=''; var rightHtml='';
        (list||[]).forEach(function(m,idx){
            var row = '<tr data-id="'+m.id+'">'
                + '<td><input type="text" class="form-control form-control-sm ayar-mini-input marka-ad-input" value="'+(m.ad||'')+'"></td>'
                + '<td class="text-end">'
                + '<button class="btn btn-sm btn-primary marka-kaydet"><i class="feather-save"></i></button> '
                + '<button class="btn btn-sm btn-danger marka-sil"><i class="feather-trash-2"></i></button>'
                + '</td>'
                + '</tr>';
            if (idx < half) leftHtml += row; else rightHtml += row;
        });
        left.html(leftHtml||'<tr><td colspan="2">Kayıt yok</td></tr>');
        right.html(rightHtml);
    });
});
$(document).on('click', '#yeniMarkaEkleBtn', function(){
    var ad = ($('#yeniMarkaAd').val()||'').trim();
    if(!ad) return;
    $.post('{{ route('settings.markalar.store') }}', { ad: ad, _token: $('meta[name="csrf-token"]').attr('content') }, function(){
        $('#markalarModal').trigger('show.bs.modal');
        $('#yeniMarkaAd').val('');
    });
});
$(document).on('click', '.marka-kaydet', function(){
    var tr = $(this).closest('tr');
    var id = tr.data('id');
    var ad = tr.find('.marka-ad-input').val();
    $.ajax({ url:'{{ url('/ayarlar/markalar') }}/'+id, method:'POST', data:{ _method:'PUT', ad: ad, _token:$('meta[name="csrf-token"]').attr('content') } })
    .done(function(){ Swal && Swal.fire('Kaydedildi','','success'); })
    .fail(function(){ Swal && Swal.fire('Hata','Kaydedilemedi','error'); });
});
$(document).on('click', '.marka-sil', function(){
    var tr = $(this).closest('tr');
    var id = tr.data('id');
    $.ajax({ url:'{{ url('/ayarlar/markalar') }}/'+id, method:'POST', data:{ _method:'DELETE', _token:$('meta[name="csrf-token"]').attr('content') } })
    .done(function(){ tr.remove(); })
    .fail(function(){ Swal && Swal.fire('Hata','Silinemedi','error'); });
});

// Cihaz Türleri modal load and actions
$(document).on('show.bs.modal', '#cihazTurModal', function(){
    var left = $('#cihazTurTableLeft tbody');
    var right = $('#cihazTurTableRight tbody');
    left.html('<tr><td colspan="2">Yükleniyor...</td></tr>');
    right.empty();
    $.getJSON('{{ route('settings.cihazTurleri.index') }}', function(list){
        var half = Math.ceil((list||[]).length/2);
        var leftHtml=''; var rightHtml='';
        (list||[]).forEach(function(m,idx){
            var row = '<tr data-id="'+m.id+'">'
                + '<td><input type="text" class="form-control form-control-sm ayar-mini-input cihaz-tur-ad-input" value="'+(m.ad||'')+'"></td>'
                + '<td class="text-end">'
                + '<button class="btn btn-sm btn-primary cihaz-tur-kaydet"><i class="feather-save"></i></button> '
                + '<button class="btn btn-sm btn-danger cihaz-tur-sil"><i class="feather-trash-2"></i></button>'
                + '</td>'
                + '</tr>';
            if (idx < half) leftHtml += row; else rightHtml += row;
        });
        left.html(leftHtml||'<tr><td colspan="2">Kayıt yok</td></tr>');
        right.html(rightHtml);
    });
});
$(document).on('click', '#yeniCihazTurEkleBtn', function(){
    var ad = ($('#yeniCihazTurAd').val()||'').trim();
    if(!ad) return;
    $.post('{{ route('settings.cihazTurleri.store') }}', { ad: ad, _token: $('meta[name="csrf-token"]').attr('content') }, function(){
        $('#cihazTurModal').trigger('show.bs.modal');
        $('#yeniCihazTurAd').val('');
    });
});
$(document).on('click', '.cihaz-tur-kaydet', function(){
    var tr = $(this).closest('tr');
    var id = tr.data('id');
    var ad = tr.find('.cihaz-tur-ad-input').val();
    $.ajax({ url:'{{ url('/ayarlar/cihaz-turleri') }}/'+id, method:'POST', data:{ _method:'PUT', ad: ad, _token:$('meta[name="csrf-token"]').attr('content') } })
    .done(function(){ Swal && Swal.fire('Kaydedildi','','success'); })
    .fail(function(){ Swal && Swal.fire('Hata','Kaydedilemedi','error'); });
});
$(document).on('click', '.cihaz-tur-sil', function(){
    var tr = $(this).closest('tr');
    var id = tr.data('id');
    $.ajax({ url:'{{ url('/ayarlar/cihaz-turleri') }}/'+id, method:'POST', data:{ _method:'DELETE', _token:$('meta[name="csrf-token"]').attr('content') } })
    .done(function(){ tr.remove(); })
    .fail(function(){ Swal && Swal.fire('Hata','Silinemedi','error'); });
});
</script>
@endpush



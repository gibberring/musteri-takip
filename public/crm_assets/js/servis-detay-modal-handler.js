(function(window, $) {
    'use strict';

    if (window._servisDetayGlobalHandlersBound) return; // çift kayıt engelle
    window._servisDetayGlobalHandlersBound = true;
    // Bu bayrak, sayfa içinde proposal.js benzeri yerel handler'lar varsa onları pasifleştirmek için kullanılacak
    window._useGlobalServisDetayHandlers = true;

    // Global: CSRF header ayarı (profil vb. sayfalarda gereklidir)
    try {
        if ($ && $.ajaxSetup) {
            var csrf = document.querySelector('meta[name="csrf-token"]');
            if (csrf && csrf.getAttribute('content')) {
                $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': csrf.getAttribute('content') } });
            }
        }
    } catch (e) { /* no-op */ }

    // Eğer proposal.js yüklenmediyse, modal show handler'ını minimum işlevle sağlayalım
    function normalizeLogAciklama(raw) {
        var aciklama = raw || '-';
        if (aciklama !== '-' && aciklama.length > 0) {
            aciklama = aciklama.replace(/^\s*-?\s*açıklama\s*:\s*/i, '');
            aciklama = aciklama.charAt(0).toLocaleUpperCase('tr-TR') + aciklama.slice(1);
            aciklama = aciklama.replace(/&lt;br\s*\/?&gt;/gi, '<br>');
            aciklama = aciklama.replace(/\r\n|\r|\n/g, '<br>');
            aciklama = aciklama.replace(/\b(\d{4})-(\d{2})-(\d{2})\b/g, function(_, y, m, d){
                return d + '.' + m + '.' + y;
            });
        }
        return aciklama;
    }
    function formatDateTr(ymd) {
        if (!ymd) return '-';
        var s = String(ymd).trim();
        var m = s.match(/^(\d{4})-(\d{2})-(\d{2})/);
        if (m) return m[3] + '.' + m[2] + '.' + m[1];
        return s;
    }

    // ServisDetay modal aç/kapa: her durumda body sınıfı uygula
    $(document).on('shown.bs.modal', '#servisDetayModal', function() {
        try {
            document.body.classList.add('servis-detay-modal-open');
            document.body.style.paddingRight = '0px';
            this.style.paddingRight = '0px';
        } catch (e) { /* no-op */ }
    });
    $(document).on('hidden.bs.modal', '#servisDetayModal', function() {
        try {
            document.body.classList.remove('servis-detay-modal-open');
            document.body.style.paddingRight = '';
            this.style.paddingRight = '';
        } catch (e) { /* no-op */ }
    });

    var _servisDetayDetailXhr = null;
    var _servisDetayRequestedId = null;

    function clearServisDetayModalUi(servisIdHint) {
        var loading = 'Yükleniyor…';
        try {
            $('#modalServisId').text(servisIdHint ? String(servisIdHint) : '…');
            $('#modalServisKayitTarihiBaslik').text(loading);
            $('#modalServisOperatoruBaslik').text(loading);

            $('#modalMusteriAd').text(loading).removeData('raw-ad');
            $('#modalMusteriTel').text(loading);
            $('#modalMusteriAdres').text(loading).removeData('raw-adres');
            $('#modalMusteriAdresLink').attr('href', '#').removeClass('is-mobile').addClass('disabled').addClass('d-none');
            $('#modalMusteriIl').text(loading).removeData('il-id');
            $('#modalMusteriIlce').text(loading).removeData('ilce-id');
            $('#modalVergiDairesi').text(loading).removeData('raw-vdaire');
            $('#modalVergiNo').text(loading).removeData('raw-vno');

            $('#modalMarkaAd').text(loading).removeData('marka-id');
            $('#modalCihazTuruAd').text(loading).removeData('cihaz-turu-id');
            $('#modalCihazModel').text(loading);
            $('#modalSeriNo').text(loading);
            $('#modalCihazAriza').text(loading);
            $('#modalOperatorNotu').text(loading);

            $('#modalMevcutDurumWrapper').html('<span class="badge bg-secondary">' + loading + '</span>');
            try {
                $('#modalTeknisyenGorulduIcon').removeClass('text-success text-muted').addClass('text-muted');
                $('#modalTeknisyenGorulduText').removeClass('text-success text-muted').addClass('text-muted').text(loading);
            } catch (eGor) { /* no-op */ }
            $('#modalIslemLoglariBody').html('<tr><td colspan="5" class="text-center text-muted">' + loading + '</td></tr>');
            $('#modalKasaHareketleriBody').html('<tr><td colspan="7" class="text-center text-muted">' + loading + '</td></tr>');

            var $imgContainer = $('#servisResimleriContainer');
            if ($imgContainer.length) {
                $imgContainer.empty();
            }
            $('#noImageText').show();
            $('#resimUploadServisId').val(servisIdHint || '');

            $('#modalDurumGuncelleSelect').val('');
            $('#modalDinamikFormAlani').html('');
            $('#modalDurumKaydetBtn').hide();

            // Düzenleme modundan kalıntı olmasın
            try {
                $('#modalFooterNormal').show();
                $('#modalFooterEdit').hide();
                $('#modalMusteriIlRow').addClass('d-none');
                $('#modalMusteriIlceRow').addClass('d-none');
            } catch (e2) { /* no-op */ }

            window.currentServisMusteriTel1 = '';
            window.currentServisMusteriTel2 = '';
            window.currentServisMevcutDurumId = '';
            window.currentServisKasaHareketleri = [];
            window.currentServisTahsilEdenName = '-';
        } catch (e) { /* no-op */ }
    }

    window.applyTeknisyenGorulduUi = function(data) {
        try {
            var $icon = $('#modalTeknisyenGorulduIcon');
            var $text = $('#modalTeknisyenGorulduText');
            if (!$icon.length || !$text.length) return;
            var raw = data && (data.teknisyen_goruldu_at || data.teknisyenGorulduAt);
            if (raw) {
                var label = 'Görüldü';
                try {
                    var d = new Date(raw);
                    if (!isNaN(d.getTime())) {
                        var tarih = d.toLocaleDateString('tr-TR', { day: '2-digit', month: '2-digit', year: 'numeric' });
                        var saat = d.toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' });
                        label = 'Görüldü · ' + tarih + ' ' + saat;
                    }
                } catch (eFmt) { /* keep default */ }
                $icon.removeClass('text-muted').addClass('text-success');
                $text.removeClass('text-muted').addClass('text-success').text(label);
            } else {
                $icon.removeClass('text-success').addClass('text-muted');
                $text.removeClass('text-success').addClass('text-muted').text('Teknisyen henüz görmedi');
            }
        } catch (e) { /* no-op */ }
    };

    function applyServisDetayModalData(data) {
            if (!data) return;
            try {
                window.currentServisMevcutDurumId = (data.mevcutDurumId !== undefined && data.mevcutDurumId !== null) ? String(data.mevcutDurumId) : '';
            } catch (e) { /* no-op */ }
            // Başlık
            $('#modalServisId').text(data.id || 'N/A');
            (function(){
                let t = '-';
                if (data.created_at) {
                    try {
                        const d = new Date(data.created_at);
                        const tarih = d.toLocaleDateString('tr-TR', { day:'2-digit', month:'2-digit', year:'numeric' });
                        const saat = d.toLocaleTimeString('tr-TR', { hour:'2-digit', minute:'2-digit' });
                        t = `${tarih} - ${saat}`;
                    } catch(e) { t = data.created_at; }
                }
                $('#modalServisKayitTarihiBaslik').text(t);
            })();
            // Operatör başlığı: kaydı oluşturan kişi (ilk logun yapanı), yoksa atanan personel, o da yoksa '-'
            (function(){
                var opText = '-';
                if (data.olusturan_personel && data.olusturan_personel.ad) {
                    opText = data.olusturan_personel.ad;
                } else if (data.personel && data.personel.ad) {
                    opText = data.personel.ad;
                }
                $('#modalServisOperatoruBaslik').text(opText);
            })();

            // Müşteri
            $('#modalMusteriAd').text(data.musteri ? (data.musteri.ad || 'N/A') : 'N/A');
            $('#modalMusteriAd').data('raw-ad', data.musteri ? (data.musteri.ad || '') : '');
            window.currentServisMusteriTel1 = data.musteri ? (data.musteri.tel1 || '') : '';
            window.currentServisMusteriTel2 = data.musteri ? (data.musteri.tel2 || '') : '';
            (function(){
                var tel1 = data.musteri ? (data.musteri.tel1 || '') : '';
                var tel2 = data.musteri ? (data.musteri.tel2 || '') : '';
                var isMobileUA = /Mobi|Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
                if (isMobileUA) {
                    var t1 = tel1 ? ('<a href="tel:' + tel1.replace(/\D/g, '') + '">' + tel1 + '</a>') : '-';
                    var t2 = tel2 ? ('<a href="tel:' + tel2.replace(/\D/g, '') + '">' + tel2 + '</a>') : '';
                    $('#modalMusteriTel').html(t1 + (t2 ? ' / ' + t2 : ''));
                } else {
                    $('#modalMusteriTel').text(tel1 ? (tel1 + (tel2 ? ' / ' + tel2 : '')) : 'N/A');
                }
            })();
            (function(){
                let adres = data.musteri ? (data.musteri.adres || 'N/A') : 'N/A';
                $('#modalMusteriAdres').data('raw-adres', data.musteri ? (data.musteri.adres || '') : '');
                if (adres !== 'N/A' && adres.length > 0) {
                    adres = adres.charAt(0).toLocaleUpperCase('tr-TR') + adres.slice(1);
                }
                if (adres !== 'N/A' && data.musteri) {
                    if (data.musteri.ilce && data.musteri.ilce.ad) adres += ' / ' + data.musteri.ilce.ad;
                    if (data.musteri.il && data.musteri.il.ad) adres += ' / ' + data.musteri.il.ad;
                }
                $('#modalMusteriAdres').text(adres);
                var $adresLink = $('#modalMusteriAdresLink');
                var isMobileUA = /Mobi|Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
                if (isMobileUA && adres && adres !== 'N/A') {
                    $adresLink.attr('href', 'https://yandex.com/maps/?text=' + encodeURIComponent(adres));
                    $adresLink.removeClass('disabled').addClass('is-mobile').removeClass('d-none');
                } else {
                    $adresLink.attr('href', '#');
                    $adresLink.removeClass('is-mobile').addClass('disabled').addClass('d-none');
                }
            })();
            (function(){
                var ilAd = (data.musteri && data.musteri.il && data.musteri.il.ad) ? data.musteri.il.ad : '-';
                var ilceAd = (data.musteri && data.musteri.ilce && data.musteri.ilce.ad) ? data.musteri.ilce.ad : '-';
                $('#modalMusteriIl').text(ilAd).data('il-id', (data.musteri && data.musteri.il_id) ? data.musteri.il_id : '');
                $('#modalMusteriIlce').text(ilceAd).data('ilce-id', (data.musteri && data.musteri.ilce_id) ? data.musteri.ilce_id : '');
            })();
            (function(){
                const vergiDairesiRow = $('#modalVergiDairesi').closest('.col-12');
                const vergiNoRow = $('#modalVergiNo').closest('.col-12');
                const tip = (data.musteri && typeof data.musteri.musteri_tip !== 'undefined') ? String(data.musteri.musteri_tip) : null;
                if (tip === '1') {
                    vergiDairesiRow.removeClass('d-none').addClass('d-flex');
                    vergiNoRow.removeClass('d-none').addClass('d-flex');
                    $('#modalVergiDairesi').text(data.musteri.vdaire || '-').data('raw-vdaire', data.musteri.vdaire || '');
                    $('#modalVergiNo').text(data.musteri.vno || '-').data('raw-vno', data.musteri.vno || '');
                } else {
                    vergiDairesiRow.removeClass('d-flex').addClass('d-none');
                    vergiNoRow.removeClass('d-flex').addClass('d-none');
                    $('#modalVergiDairesi').text('-').data('raw-vdaire', '');
                    $('#modalVergiNo').text('-').data('raw-vno', '');
                }
            })();

            // Cihaz/Servis bilgileri
            $('#modalMarkaAd').text(data.marka ? data.marka.ad : (data.marka && data.marka.ad ? data.marka.ad : (data.marka?.ad || 'N/A'))).data('marka-id', data.marka_id);
            $('#modalCihazTuruAd').text((data.cihaz_turu && data.cihaz_turu.ad) ? data.cihaz_turu.ad : (data.cihazTuru && data.cihazTuru.ad ? data.cihazTuru.ad : 'N/A')).data('cihaz-turu-id', data.cihaz_turu_id || data.cihaz_tur_id);
            $('#modalCihazModel').text(data.cihaz_model || 'N/A');
            (function(){
                const row = $('#modalSeriNo').closest('.col-12');
                const val = data.seri_no;
                if (val && val.trim() !== '' && val.toUpperCase() !== 'N/A') {
                    row.removeClass('d-none').addClass('d-flex');
                    $('#modalSeriNo').text(val);
                } else {
                    row.removeClass('d-flex').addClass('d-none');
                    $('#modalSeriNo').text('N/A');
                }
            })();
            (function(){
                var ariza = data.cihaz_arizasi || 'N/A';
                if (ariza !== 'N/A' && ariza.length > 0) {
                    ariza = ariza.charAt(0).toLocaleUpperCase('tr-TR') + ariza.slice(1);
                }
                $('#modalCihazAriza').text(ariza);
            })();
            $('#modalOperatorNotu').text(data.operator_not || 'N/A');
            (function(){
                function normalizeVal(val) {
                    return (val || '').toString().trim().toUpperCase();
                }
                function shouldHide(val) {
                    return !val || val === 'N/A' || val === 'NULL';
                }
                function toggleRowByValue($el, dataVal) {
                    var raw = (typeof dataVal === 'undefined') ? $el.text() : dataVal;
                    var v = normalizeVal(raw);
                    if (shouldHide(v)) {
                        $el.closest('.col-12').addClass('d-none');
                        return;
                    }
                    $el.closest('.col-12').removeClass('d-none');
                }
                toggleRowByValue($('#modalCihazModel'), data.cihaz_model);
                toggleRowByValue($('#modalOperatorNotu'), data.operator_not);
            })();

            // Mevcut durum badge
            (function(){
                let badgeClass = 'badge';
                const durumId = Number(data.mevcutDurumId);
                if ([9097, 9103, 9477].includes(durumId)) badgeClass += ' bg-soft-dark text-dark';
                else if ([9098].includes(durumId)) badgeClass += ' bg-soft-secondary text-secondary';
                else if ([9113, 9100].includes(durumId)) badgeClass += ' bg-soft-warning text-warning';
                else if (durumId === 9334) badgeClass += ' bg-soft-primary text-primary';
                else if ([9115, 9114, 9105, 9099].includes(durumId)) badgeClass += ' bg-soft-success text-success';
                else badgeClass += ' bg-soft-danger text-danger';
                $('#modalMevcutDurumWrapper').html(data.mevcutDurumAdi ? `<span class="${badgeClass}">${data.mevcutDurumAdi}</span>` : '<span class="badge bg-secondary">Bilinmiyor</span>');
            })();

            if (typeof window.applyTeknisyenGorulduUi === 'function') {
                window.applyTeknisyenGorulduUi(data);
            }

            // Giriş yapan kullanıcının pozisyonunu globalde tut (aksiyon ikonları için)
            if (typeof data.loggedInUserPozId !== 'undefined') {
                window.loggedInUserPozId = data.loggedInUserPozId;
            }

            // İşlem logları (aksiyonlarla)
            (function(){
                const logs = data.islemloglari || [];
                const tbody = $('#modalIslemLoglariBody');
                if (!tbody.length) return;
                if (!logs.length) { tbody.html('<tr><td colspan="5" class="text-center">İşlem kaydı bulunamadı.</td></tr>'); return; }
                let html = '';
                const canEditLog = (window.PERM && window.PERM.can && window.PERM.can.canEditLogs && window.PERM.can.canEditLogs());
                const canDeleteLog = (window.PERM && window.PERM.can && window.PERM.can.canDeleteLogs && window.PERM.can.canDeleteLogs());
                logs.forEach(function(log) {
                    const tarih = (formatDateTr(log.tarih) || '-') + ' ' + (log.saat || '');
                    const yapan = log.personel ? log.personel.ad : ((log.is_system || log.islemi_yapan_personel_id == null) ? 'Sistem' : '-');
                    const ad = log.servis_durum ? log.servis_durum.ad : '-';
                    var aciklama = normalizeLogAciklama(log.aciklama);
                    var actionButtons = '';
                    if (canEditLog) {
                        actionButtons += '<button class="btn btn-sm btn-secondary log-duzenle-btn" data-log-id="' + log.id + '" style="padding: 0.1rem 0.3rem;"><i class="feather feather-edit-3"></i></button>';
                    }
                    if (canDeleteLog) {
                        actionButtons += '<button class="btn btn-sm btn-danger log-sil-btn" data-log-id="' + log.id + '" style="padding: 0.1rem 0.3rem;"><i class="feather feather-trash-2"></i></button>';
                    }
                    html += '<tr>'
                         + `<td class="tdd">${tarih}</td>`
                         + `<td class="tdd">${yapan}</td>`
                         + `<td class="tdd">${ad}</td>`
                         + `<td class="tdd">${aciklama}</td>`
                         + '<td class="tdd text-center">' + (actionButtons ? '<div class="d-flex justify-content-center gap-1">' + actionButtons + '</div>' : '') + '</td>'
                         + '</tr>';
                });
                tbody.html(html);
            })();

            // Resim Galerisi (güvenli gösterim rotası)
            (function(){
                var container = $('#servisResimleriContainer');
                if (!container.length) return;
                container.empty();
                $('#resimUploadServisId').val(data.id);
                if (data.servisResimleri && data.servisResimleri.length > 0) {
                    $('#noImageText').hide();
                    data.servisResimleri.forEach(function(resim){
                        var imageUrl = '/servis-resimleri/' + resim.id + '/goster';
                        var ekleyen = (resim.ekleyen_personel && resim.ekleyen_personel.ad) ? resim.ekleyen_personel.ad : 'Bilinmiyor';
                        var tarihSaat = new Date(resim.created_at).toLocaleString('tr-TR', { day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit' });
                        var html = ''
                          + '<div class="image-item border rounded p-2 d-flex flex-column align-items-center bg-light shadow-sm" style="width: 150px; flex-shrink: 0;">'
                          + '<a href="' + imageUrl + '" target="_blank">'
                          + '<img src="' + imageUrl + '" alt="' + (resim.aciklama || 'Servis Resmi') + '" class="img-fluid rounded" style="max-height: 100px; object-fit: cover;">'
                          + '</a>'
                          + '<small class="text-muted mt-2 text-center text-truncate-2-lines" style="width: 100%;">' + (resim.aciklama || 'Açıklama Yok') + '</small>'
                          + '<small class="text-muted text-center mt-1" style="width: 100%; font-size: 0.7em;">Ekleyen: ' + ekleyen + '</small>'
                          + '<small class="text-muted text-center" style="width: 100%; font-size: 0.7em;">Tarih: ' + tarihSaat + '</small>'
                          + '</div>';
                        container.append(html);
                    });
                } else {
                    $('#noImageText').show();
                }
            })();

            // Tahsil Eden: servise atanmış teknisyen
            var tahsilEdenPersonel = (data && data.personel) ? data.personel : null;

            // Tahsil eden (servise atanmış teknisyen adı) cache
            window.currentServisTahsilEdenName = (data && data.personel && data.personel.ad) ? data.personel.ad : '-';

            // Kasa hareketleri (özet)
            (function(){
                const kasa = data.kasa_hareketleri || [];
                const tbody = $('#modalKasaHareketleriBody');
                if (!tbody.length) return;
                if (!kasa.length) { tbody.html('<tr><td colspan="7" class="text-center">Bu servise ait kasa hareketi bulunamadı.</td></tr>'); return; }
                var canEditKasa = (window.PERM && window.PERM.can && window.PERM.can.canEditKasa && window.PERM.can.canEditKasa());
                var canDeleteKasa = (window.PERM && window.PERM.can && window.PERM.can.canDeleteKasa && window.PERM.can.canDeleteKasa());
                let html = '';
                kasa.forEach(function(h) {
                    let tutarClass = '';
                    let tutarPrefix = '';
                    if (h.odeme_yonu == 1) tutarClass = 'text-success';
                    else if (h.odeme_yonu == -1) { tutarClass = 'text-danger'; tutarPrefix = '-'; }
                    let odemeDurumuText = '-';
                    if (typeof h.gerceklesme !== 'undefined') odemeDurumuText = (h.gerceklesme == 1) ? '<span class="badge bg-soft-success text-success">Tamamlandı</span>' : '<span class="badge bg-soft-warning text-warning">Beklemede</span>';
                    let kayitTarihi = '-';
                    const gosterilecekTarih = h.tarih || '';
                    if (gosterilecekTarih) {
                        try { const d = new Date(gosterilecekTarih); kayitTarihi = d.toLocaleDateString('tr-TR', { day:'2-digit', month:'2-digit', year:'numeric' }); if (h.saat) kayitTarihi += ' ' + h.saat.substring(0,5); } catch(e) { kayitTarihi = gosterilecekTarih; }
                    }
                    var tahsilEden = (h.ilgili_personel && h.ilgili_personel.ad)
                        ? h.ilgili_personel.ad
                        : ((tahsilEdenPersonel && tahsilEdenPersonel.ad) ? tahsilEdenPersonel.ad : (window.currentServisTahsilEdenName || '-'));
                    html += '<tr data-kasa-id="' + h.id + '" data-odeme-yonu="' + (typeof h.odeme_yonu !== 'undefined' ? h.odeme_yonu : '') + '" data-odeme-turu-id="' + (h.odeme_turu_id || (h.odeme_turu && h.odeme_turu.id) || '') + '">'
                          + `<td class="tdd">${kayitTarihi}</td>`
                          + `<td class="tdd">${h.personel ? h.personel.ad : '-'}</td>`
                          + `<td class="tdd">${tahsilEden}</td>`
                          + `<td class="tdd">${h.odeme_sekli ? h.odeme_sekli.ad : '-'}</td>`
                          + `<td class="tdd">${odemeDurumuText}</td>`
                          + `<td class="tdd ${tutarClass} fw-bold">${tutarPrefix}${parseFloat(h.tutar || 0).toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} TL</td>`
                          + '<td class="tdd text-center">'
                          + (function(){
                                var buttons = '';
                                if (canEditKasa) {
                                    buttons += '<button class="btn btn-sm btn-secondary p-1 kasa-duzenle-btn"'
                                        + ' data-kasa-id="' + h.id + '"'
                                        + ' data-odeme-sekli-id="' + (h.odeme_sekli_id || (h.odeme_sekli && h.odeme_sekli.id) || '') + '"'
                                        + ' data-odeme-turu-id="' + (h.odeme_turu_id || (h.odeme_turu && h.odeme_turu.id) || '') + '"'
                                        + ' data-gerceklesme="' + (typeof h.gerceklesme !== 'undefined' ? h.gerceklesme : '') + '"'
                                        + ' data-tutar="' + (typeof h.tutar !== 'undefined' ? h.tutar : '') + '"'
                                        + ' data-odeme-yonu="' + (typeof h.odeme_yonu !== 'undefined' ? h.odeme_yonu : '') + '"'
                                        + ' data-tarih="' + (h.tarih || '') + '"'
                                        + ' data-islem-tarihi="' + (h.islem_tarihi || '') + '"'
                                        + ' data-saat="' + (h.saat || '') + '"'
                                        + ' title="Düzenle"><i class="feather feather-edit-3"></i></button>';
                                }
                                if (canDeleteKasa) {
                                    buttons += '<button class="btn btn-sm btn-danger p-1 kasa-sil-btn" data-kasa-id="' + h.id + '" title="Sil"><i class="feather feather-trash-2"></i></button>';
                                }
                                return buttons ? ('<div class="d-flex justify-content-center gap-1">' + buttons + '</div>') : '';
                            })()
                          + '</td>'
                          + '</tr>';
                });
                tbody.html(html);
            })();

            // Durum dropdown'ı
            if (typeof window.populateServisDurumDropdown === 'function') {
                window.populateServisDurumDropdown(data.mevcutDurumId, data.mevcutDurumAdi, data.tumDurumlar || []);
            }
            // Ödeme şekilleri cache (butonlar kapalı olsa da veri hazır olsun)
            if (data && data.tumOdemeSekilleri) {
                window.tumOdemeSekilleriCache = data.tumOdemeSekilleri;
            }
            // Kasa hareketleri cache (teknisyen ödeme kontrolü için)
            var kasaList = [];
            if (data) {
                if (Array.isArray(data.kasaHareketleri)) kasaList = data.kasaHareketleri;
                else if (Array.isArray(data.kasa_hareketleri)) kasaList = data.kasa_hareketleri;
            }
            window.currentServisKasaHareketleri = kasaList;
    }

    /**
     * Servis detayını yükler. Önceki isteği abort eder, UI'ı hemen temizler,
     * response id eşleşmezse DOM'a yazmaz.
     */
    window.loadServisDetay = function(servisId) {
        if (servisId === undefined || servisId === null || servisId === '') return;
        var istenenServisId = String(servisId);

        try {
            $('#servisDetayModal').data('servis-id', istenenServisId);
            window.mevcutServisId = istenenServisId;
        } catch (e) { /* no-op */ }

        if (_servisDetayDetailXhr && typeof _servisDetayDetailXhr.abort === 'function') {
            try { _servisDetayDetailXhr.abort(); } catch (e) { /* no-op */ }
        }
        _servisDetayRequestedId = istenenServisId;
        clearServisDetayModalUi(istenenServisId);

        var detailUrl = '/servisler/' + istenenServisId + '/detay';
        var isHariciOperator = window.crmData && String(window.crmData.loggedInUserPozId) === '1076';
        var searchValue = ($('#servisGenelArama').val() || $('#servisGenelAramaMobile').val() || '').trim();
        if (isHariciOperator && searchValue.length >= 7) {
            detailUrl += '?search_override=1&search_len=' + encodeURIComponent(searchValue.length);
        }

        _servisDetayDetailXhr = $.get(detailUrl, function(data) {
            if (!data) return;
            if (String(data.id) !== String(_servisDetayRequestedId)) return;
            applyServisDetayModalData(data);
        }).fail(function(_xhr, textStatus) {
            if (textStatus === 'abort') return;
            if (String(_servisDetayRequestedId) !== istenenServisId) return;
            try {
                $('#modalMusteriAd').text('Yüklenemedi');
                $('#modalMusteriTel').text('-');
                $('#modalMusteriAdres').text('-');
                $('#modalMevcutDurumWrapper').html('<span class="badge bg-danger">Yüklenemedi</span>');
                $('#modalIslemLoglariBody').html('<tr><td colspan="5" class="text-center text-danger">Detay yüklenemedi.</td></tr>');
                $('#modalKasaHareketleriBody').html('<tr><td colspan="7" class="text-center text-danger">Detay yüklenemedi.</td></tr>');
            } catch (e) { /* no-op */ }
        });
    };

    $(document).on('show.bs.modal', '#servisDetayModal', function(event) {
        if (window._proposalServisDetayShowHandler) {
            // proposal.js zaten show handler'ı bağlamış
            return;
        }

        // Durum güncelleme alanını temizle (önceki seçim kalıntısını önle)
        try {
            $('#modalDurumGuncelleSelect').val('');
            $('#modalDinamikFormAlani').html('');
            $('#modalDurumKaydetBtn').hide();
        } catch (e) { /* no-op */ }

        // Ödeme Ekle butonunun görünürlüğünü merkezi izinle yönet
        try {
            var $odemeEkleBtn = $('#odemeEkleBtn');
            if ($odemeEkleBtn && $odemeEkleBtn.length) {
                if (window.PERM && window.PERM.can && window.PERM.can.canAddKasa && window.PERM.can.canAddKasa()) {
                    $odemeEkleBtn.show();
                } else {
                    $odemeEkleBtn.hide();
                }
            }
            // Servisi Güncelle
            var $servisGuncelleBtn = $('#servisGuncelleBtn');
            if ($servisGuncelleBtn && $servisGuncelleBtn.length) {
                if (window.PERM && window.PERM.can && window.PERM.can.canUpdateServis && window.PERM.can.canUpdateServis()) {
                    $servisGuncelleBtn.show();
                } else {
                    $servisGuncelleBtn.hide();
                }
            }
            // Servisi Sil
            var $servisSilBtn = $('#servisSilBtn');
            if ($servisSilBtn && $servisSilBtn.length) {
                if (window.PERM && window.PERM.can && window.PERM.can.canDeleteServis && window.PERM.can.canDeleteServis()) {
                    $servisSilBtn.show();
                } else {
                    $servisSilBtn.hide();
                }
            }
            // PDF Fiş
            var $pdfBtn = $('#servisFisiPdfBtn');
            if ($pdfBtn && $pdfBtn.length) {
                if (window.PERM && window.PERM.can && window.PERM.can.canViewPdfFis && window.PERM.can.canViewPdfFis()) {
                    $pdfBtn.show();
                } else {
                    $pdfBtn.hide();
                }
            }
            // Resim Ekle
            var $resimEkleBtn = $('#resimEkleBtn');
            if ($resimEkleBtn && $resimEkleBtn.length) {
                if (window.PERM && window.PERM.can && window.PERM.can.canAddResim && window.PERM.can.canAddResim()) {
                    $resimEkleBtn.show();
                } else {
                    $resimEkleBtn.hide();
                }
            }
        } catch(e) { /* no-op */ }

        // open() zaten loadServisDetay çağırdıysa tekrar yükleme
        if (window._servisDetaySkipNextShowLoad) {
            window._servisDetaySkipNextShowLoad = false;
            return;
        }

        var button = $(event.relatedTarget);
        var servisId = (button && button.length) ? button.data('servis-id') : ($('#servisDetayModal').data('servis-id') || null);
        if (!servisId) return;
        window.loadServisDetay(servisId);
    });

    // Modal zaten açıkken data-bs-toggle tıklaması show() no-op olduğu için detayı elle yenile
    $(document).on('click', '[data-bs-target="#servisDetayModal"][data-servis-id]', function() {
        if (window._proposalServisDetayShowHandler) return;
        var modalEl = document.getElementById('servisDetayModal');
        if (!modalEl || !modalEl.classList.contains('show')) return;
        var sid = $(this).data('servis-id');
        if (sid && typeof window.loadServisDetay === 'function') {
            window.loadServisDetay(sid);
        }
    });

    // === Global: Durum seçildiğinde dinamik form render ===
    $(document).on('change', '#modalDurumGuncelleSelect', function() {
        if (window._proposalServisDetayShowHandler) return; // /servisler sayfası kendi yönetir
        var secilenDurumId = $(this).val();
        var dinamikFormAlani = $('#modalDinamikFormAlani');
        dinamikFormAlani.html('');
        $('#modalDurumKaydetBtn').hide();

        if (!secilenDurumId) return;

        // Teknisyen için ödeme zorunluluğu: belirli hedef durumlar VEYA Parça Gidecek/Atölyede iken sonlandırma-tamamlama hedefleri
        try {
            var TEKNISYEN_ODEME_ZORUNLU_HEDEF_DURUM_IDS = ['9105', '9115', '9106'];
            var TEKNISYEN_ODEME_ZORUNLU_KAYNAK_DURUMLAR = ['9100', '9103'];
            var TEKNISYEN_ODEME_ZORUNLU_SONLANDIRMA_HEDEF_IDS = ['9099', '9114'];
            var loggedPozId = (window.crmData && window.crmData.loggedInUserPozId) ? String(window.crmData.loggedInUserPozId) : null;
            var loggedUserId = (window.crmData && window.crmData.loggedInUserId) ? String(window.crmData.loggedInUserId) : null;
            var mevcutDurumStr = (typeof window.currentServisMevcutDurumId !== 'undefined' && window.currentServisMevcutDurumId !== null) ? String(window.currentServisMevcutDurumId) : '';
            var secilenStr = String(secilenDurumId);
            var odemeZorunluBuGecis = (TEKNISYEN_ODEME_ZORUNLU_HEDEF_DURUM_IDS.indexOf(secilenStr) !== -1)
                || (TEKNISYEN_ODEME_ZORUNLU_KAYNAK_DURUMLAR.indexOf(mevcutDurumStr) !== -1 && TEKNISYEN_ODEME_ZORUNLU_SONLANDIRMA_HEDEF_IDS.indexOf(secilenStr) !== -1);
            if (odemeZorunluBuGecis && loggedPozId === '1077') {
                var hareketler = Array.isArray(window.currentServisKasaHareketleri) ? window.currentServisKasaHareketleri : [];
                var teknisyenOdemeVar = hareketler.some(function(h){
                    var personelId = h && (h.personel_id || (h.personel && h.personel.id)) ? String(h.personel_id || (h.personel && h.personel.id)) : null;
                    var odemeYonu = (typeof h !== 'undefined' && h !== null && typeof h.odeme_yonu !== 'undefined') ? parseInt(h.odeme_yonu) : null;
                    return personelId && loggedUserId && personelId === loggedUserId && (odemeYonu === 1 || odemeYonu === 0 || odemeYonu === null);
                });
                if (!teknisyenOdemeVar) {
                    // DOM'daki kasa tablosundan da kontrol et (cache gecikmesine karşı)
                    try {
                        $('#modalKasaHareketleriBody tr').each(function(){
                            var $tr = $(this);
                            var rowPersonelId = $tr.data('personel-id') || $tr.data('personelId');
                            var rowOdemeYonu = $tr.data('odeme-yonu');
                            if (rowPersonelId && loggedUserId && String(rowPersonelId) === loggedUserId) {
                                teknisyenOdemeVar = true;
                                return false;
                            }
                            if (rowOdemeYonu && loggedUserId && String(rowOdemeYonu) === '1' && rowPersonelId && String(rowPersonelId) === loggedUserId) {
                                teknisyenOdemeVar = true;
                                return false;
                            }
                        });
                    } catch (e) { /* no-op */ }
                }
                if (!teknisyenOdemeVar) {
                    $('#modalDurumGuncelleSelect').val('');
                    dinamikFormAlani.html('');
                    $('#modalDurumKaydetBtn').hide();
                    Swal && Swal.fire('Uyarı!', 'Önce ödeme ekleyin.', 'warning');
                    return;
                }
            }
        } catch (e) { /* no-op */ }

        var sorular = (window.crmData && Array.isArray(window.crmData.servisDurumSorular))
            ? window.crmData.servisDurumSorular.filter(function(s) { return String(s.servis_durum_id) === String(secilenDurumId); })
            : [];
        // Nakliyede için tarih/teknisyen sorularını gösterme
        if (String(secilenDurumId) === '9117') {
            sorular = sorular.filter(function(s){
                return s.cevap_format !== '[personelSor]' && s.cevap_format !== '[tarihSor]';
            });
        }

        if (sorular.length > 0) {
            var formHtml = '<div class="border rounded p-3 bg-light">';
            var isTeyidAramasi = String(secilenDurumId) === '9334';
            var isAtolyeAlindi = String(secilenDurumId) === '9100';
            sorular.sort(function(a,b){ return (a.sira||0) - (b.sira||0); }).forEach(function(soru){
                var inputHtml = '<div class="mb-1">';
                inputHtml += '<label class="form-label small fw-bold mb-0" style="font-size: 0.75rem;">' + (soru.soru || 'Açıklama') + ':</label>';
                var inputName = 'dinamik_soru[' + soru.id + ']';
                var requiredAttr = (isTeyidAramasi || isAtolyeAlindi) ? '' : ' required';
                if (soru.cevap_format === '[personelSor]') {
                    inputHtml += '<select class="form-select form-select-sm" name="' + inputName + '"' + requiredAttr + ' style="height: 24px; padding-top: 0; padding-bottom: 0;"><option value="">Seçiniz...</option>';
                    var teknisyenPozId = 1077;
                    (window.crmData.personeller || []).forEach(function(p){
                        if (!p || String(p.poz_id) !== String(teknisyenPozId)) return;
                        inputHtml += '<option value="' + p.id + '">' + p.ad + '</option>';
                    });
                    inputHtml += '</select>';
                } else if (soru.cevap_format === '[tarihSor]') {
                    var todayValue = '';
                    try {
                        var now = new Date();
                        var mm = String(now.getMonth() + 1).padStart(2, '0');
                        var dd = String(now.getDate()).padStart(2, '0');
                        todayValue = now.getFullYear() + '-' + mm + '-' + dd;
                    } catch (e) { /* no-op */ }
                    var valueAttr = todayValue ? ' value="' + todayValue + '"' : '';
                    inputHtml += '<input type="date" class="form-control form-control-sm" name="' + inputName + '"' + valueAttr + requiredAttr + ' style="height: 24px; padding-top: 0; padding-bottom: 0;">';
                } else if (soru.cevap_format === '[saatAraligiSec]') {
                    inputHtml += '<div class="row g-1"><div class="col"><input type="time" class="form-control form-control-sm" name="' + inputName + '_baslangic"' + requiredAttr + ' style="height: 24px; padding-top: 0; padding-bottom: 0;"></div><div class="col-auto align-self-center" style="font-size: 0.75rem;">-</div><div class="col"><input type="time" class="form-control form-control-sm" name="' + inputName + '_bitis"' + requiredAttr + ' style="height: 24px; padding-top: 0; padding-bottom: 0;"></div></div>';
                } else if (soru.cevap_format === '[fiyatSor]') {
                    inputHtml += '<input type="number" step="0.01" class="form-control form-control-sm" name="' + inputName + '" placeholder="0.00"' + requiredAttr + ' style="height: 24px; padding-top: 0; padding-bottom: 0;">';
                } else {
                    inputHtml += '<input type="text" class="form-control form-control-sm" name="' + inputName + '"' + requiredAttr + ' style="height: 24px; padding-top: 0; padding-bottom: 0;">';
                }
                inputHtml += '</div>';
                formHtml += inputHtml;
            });
            formHtml += '</div>';
            dinamikFormAlani.html(formHtml);
        }

        $('#modalDurumKaydetBtn').show();
    });

    // === Global: Durum Kaydet ===
    $(document).on('click', '#modalDurumKaydetBtn', function() {
        if (window._proposalServisDetayShowHandler) return; // /servisler sayfası kendi yönetir
        var yeniDurumId = $('#modalDurumGuncelleSelect').val();
        var servisId = window.mevcutServisId || $('#servisDetayModal').data('servis-id');
        if (!yeniDurumId) { Swal && Swal.fire('Uyarı!', 'Lütfen bir durum seçin.', 'warning'); return; }
        if (!servisId) { Swal && Swal.fire('Hata!', 'Servis ID bulunamadı!', 'error'); return; }

        var dinamikFormData = {};
        var formGecerli = true;
        $('#modalDinamikFormAlani').find('input, select, textarea').each(function() {
            var $input = $(this);
            var nameAttr = $input.attr('name');
            if (nameAttr && nameAttr.startsWith('dinamik_soru')) {
                if ($input.prop('required') && !$input.val() && $input.is(':visible')) {
                    formGecerli = false;
                    $input.addClass('is-invalid');
                } else {
                    $input.removeClass('is-invalid');
                    if ($input.attr('type') === 'time') {
                        var baseName = nameAttr.replace('_baslangic', '').replace('_bitis', '');
                        if (!dinamikFormData[baseName]) dinamikFormData[baseName] = {};
                        if (nameAttr.endsWith('_baslangic')) dinamikFormData[baseName].baslangic = $input.val();
                        else if (nameAttr.endsWith('_bitis')) dinamikFormData[baseName].bitis = $input.val();
                    } else {
                        dinamikFormData[nameAttr] = $input.val();
                    }
                }
            }
        });

        if (!formGecerli) { Swal && Swal.fire('Uyarı!', 'Lütfen tüm zorunlu alanları doldurun.', 'warning'); return; }

        Object.keys(dinamikFormData).forEach(function(key){
            if (typeof dinamikFormData[key] === 'object' && dinamikFormData[key].baslangic && dinamikFormData[key].bitis) {
                dinamikFormData[key] = dinamikFormData[key].baslangic + ' - ' + dinamikFormData[key].bitis;
            }
        });

        var $btn = $('#modalDurumKaydetBtn');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Kaydediliyor...');

        $.ajax({
            url: '/servisler/' + servisId + '/durum-guncelle-detayli',
            type: 'POST',
            data: { _method: 'PUT', servis_durum_id: yeniDurumId, dinamik_veriler: dinamikFormData },
            dataType: 'json'
        }).done(function(response){
            if (response && response.success && response.servis) {
                // Mevcut durum badge
                var g = response.servis;
                try {
                    if (g.servis_durum && g.servis_durum.id != null) {
                        window.currentServisMevcutDurumId = String(g.servis_durum.id);
                    }
                } catch (e) { /* no-op */ }
                if (g.servis_durum) {
                    var durumId = parseInt(g.servis_durum.id);
                    var badgeClass = 'badge';
                    if ([9097, 9103, 9477].includes(durumId)) badgeClass += ' bg-soft-dark text-dark';
                    else if ([9098].includes(durumId)) badgeClass += ' bg-soft-secondary text-secondary';
                    else if ([9113, 9100].includes(durumId)) badgeClass += ' bg-soft-warning text-warning';
                    else if (durumId === 9334) badgeClass += ' bg-soft-primary text-primary';
                    else if ([9115, 9114, 9105, 9099].includes(durumId)) badgeClass += ' bg-soft-success text-success';
                    else badgeClass += ' bg-soft-danger text-danger';
                    $('#modalMevcutDurumWrapper').html('<span class="' + badgeClass + '">' + g.servis_durum.ad + '</span>');
                } else {
                    $('#modalMevcutDurumWrapper').html('<span class="badge bg-secondary">Bilinmiyor</span>');
                }
                if (typeof window.applyTeknisyenGorulduUi === 'function') {
                    window.applyTeknisyenGorulduUi(g);
                }
                try {
                    var tw = response.teknisyen_whatsapp;
                    if (tw && tw.sent) {
                        // API ile sessiz otomatik gönderildi
                    } else if (tw && !tw.sent && window.Swal) {
                        var errMsg = (tw.error && String(tw.error).trim())
                            ? String(tw.error)
                            : 'WhatsApp API ayarlı değil veya mesaj gönderilemedi.';
                        Swal.fire({
                            icon: 'warning',
                            title: 'WhatsApp gönderilemedi',
                            text: errMsg,
                            confirmButtonText: 'Tamam'
                        });
                    }
                } catch (eWa) { /* no-op */ }
                // Formu sıfırla
                $('#modalDurumGuncelleSelect').val('');
                $('#modalDinamikFormAlani').empty();
                $('#modalDurumKaydetBtn').hide();

                // Güncel durum seçeneklerini yeniden yükle (modali kapatmaya gerek yok)
                var sid = window.mevcutServisId || $('#servisDetayModal').data('servis-id');
                if (sid) {
                    $.get('/servisler/' + sid + '/detay', function(data){
                        try {
                            window.currentServisMevcutDurumId = (data.mevcutDurumId !== undefined && data.mevcutDurumId !== null) ? String(data.mevcutDurumId) : '';
                        } catch (e) { /* no-op */ }
                        if (typeof window.applyTeknisyenGorulduUi === 'function') {
                            window.applyTeknisyenGorulduUi(data);
                        }
                        if (typeof window.populateServisDurumDropdown === 'function') {
                            window.populateServisDurumDropdown(data.mevcutDurumId, data.mevcutDurumAdi, data.tumDurumlar || []);
                        }
                        try {
                            $(document).trigger('servisDurumuGuncellendi', [sid, data]);
                            window.lastServisDurumuGuncellemesi = { servisId: sid, servis: data };
                        } catch (eventError) {
                            console.warn('servisDurumuGuncellendi olayı tetiklenemedi:', eventError);
                        }
                    });
                }
                // Logları güncelle (varsa)
                if (g.islemloglari && Array.isArray(g.islemloglari)) {
                    var tbody = $('#modalIslemLoglariBody');
                    var html = '';
                    var canEditLog = (window.PERM && window.PERM.can && window.PERM.can.canEditLogs && window.PERM.can.canEditLogs());
                    var canDeleteLog = (window.PERM && window.PERM.can && window.PERM.can.canDeleteLogs && window.PERM.can.canDeleteLogs());
                    g.islemloglari.forEach(function(log){
                        var tarih = (formatDateTr(log.tarih) || '-') + ' ' + (log.saat || '');
                        var yapan = log.personel ? log.personel.ad : ((log.is_system || log.islemi_yapan_personel_id == null) ? 'Sistem' : '-');
                        var ad = (log.islem_adi ? log.islem_adi : (log.servis_durum ? log.servis_durum.ad : '-'));
                        var aciklama = normalizeLogAciklama(log.aciklama);
                        html += '<tr>'
                              + '<td class="tdd">' + tarih + '</td>'
                              + '<td class="tdd">' + yapan + '</td>'
                              + '<td class="tdd">' + ad + '</td>'
                              + '<td class="tdd">' + aciklama + '</td>'
                              + '<td class="tdd text-center">' + (function(){
                                    var buttons = '';
                                    if (canEditLog) {
                                        buttons += '<button class="btn btn-sm btn-secondary log-duzenle-btn" data-log-id="' + log.id + '" style="padding: 0.1rem 0.3rem;"><i class="feather feather-edit-3"></i></button>';
                                    }
                                    if (canDeleteLog) {
                                        buttons += '<button class="btn btn-sm btn-danger log-sil-btn" data-log-id="' + log.id + '" style="padding: 0.1rem 0.3rem;"><i class="feather feather-trash-2"></i></button>';
                                    }
                                    return buttons ? ('<div class="d-flex justify-content-center gap-1">' + buttons + '</div>') : '';
                                })() + '</td>'
                              + '</tr>';
                    });
                    tbody.html(html);
                }
                try {
                    // Servisler listesini yenilemek için global bir tetikleyici yayınla
                    $(document).trigger('servisDurumuGuncellendi', [servisId, g]);
                    window.lastServisDurumuGuncellemesi = { servisId: servisId, servis: g };
                } catch (eventError) {
                    console.warn('servisDurumuGuncellendi olayı tetiklenemedi:', eventError);
                }
            } else {
                Swal && Swal.fire('Hata!', (response && response.message) ? response.message : 'Bir hata oluştu.', 'error');
            }
        }).fail(function(jqXHR){
            Swal && Swal.fire('Hata!', 'Durum güncellenirken bir hata oluştu: ' + ((jqXHR.responseJSON && jqXHR.responseJSON.message) ? jqXHR.responseJSON.message : ''), 'error');
        }).always(function(){
            $btn.prop('disabled', false).html('Kaydet');
        });
    });

    // === Global: Cihaz bilgisi düzenleme / kaydet ===
    $(document).on('click', '#servisGuncelleBtn', function(){
        if (window._proposalServisDetayShowHandler) return;
        // Düzenleme moduna geçir: alanları input/select yap
        var markalar = (window.crmData && window.crmData.markalar) || [];
        var cihazTurleri = (window.crmData && window.crmData.cihazTurleri) || [];
        var fields = {
            '#modalMarkaAd': { type: 'select', dataKey: 'marka-id', options: markalar, text: $('#modalMarkaAd').text().trim() },
            '#modalCihazTuruAd': { type: 'select', dataKey: 'cihaz-turu-id', options: cihazTurleri, text: $('#modalCihazTuruAd').text().trim() },
            '#modalCihazModel': { type: 'input', text: $('#modalCihazModel').text().trim() },
            '#modalSeriNo': { type: 'input', text: $('#modalSeriNo').text().trim() },
            '#modalCihazAriza': { type: 'input', text: $('#modalCihazAriza').text().trim() }
        };
        Object.keys(fields).forEach(function(sel){
            var $el = $(sel);
            var f = fields[sel];
            $el.data('original-text', f.text);
            var html = '';
            if (f.type === 'select') {
                html = '<select class="form-select form-select-sm cihaz-bilgi-input" style="font-size: 0.85rem; background-color: #f8f9fa; height: 28px; padding-top: 0; padding-bottom: 0;">';
                html += '<option value="">Seçiniz</option>';
                var selectedVal = $el.data(f.dataKey);
                (f.options || []).forEach(function(opt){
                    html += '<option value="' + opt.id + '"' + (String(selectedVal) === String(opt.id) ? ' selected' : '') + '>' + opt.ad + '</option>';
                });
                html += '</select>';
            } else {
                html = '<input type="text" class="form-control form-control-sm cihaz-bilgi-input" value="' + (f.text === 'N/A' ? '' : f.text) + '" style="font-size: 0.85rem; background-color: #f8f9fa; height: 28px; padding-top: 0; padding-bottom: 0;">';
            }
            $el.html(html);
        });
        // Müşteri bilgilerini düzenlenebilir yap
        var musteriFields = {
            '#modalMusteriAd': { type: 'input', text: ($('#modalMusteriAd').data('raw-ad') || $('#modalMusteriAd').text().trim()) },
            '#modalMusteriTel': { type: 'tel', tel1: (window.currentServisMusteriTel1 || ''), tel2: (window.currentServisMusteriTel2 || '') },
            '#modalMusteriAdres': { type: 'input', text: ($('#modalMusteriAdres').data('raw-adres') || '') },
            '#modalVergiDairesi': { type: 'input', text: ($('#modalVergiDairesi').data('raw-vdaire') || '') },
            '#modalVergiNo': { type: 'input', text: ($('#modalVergiNo').data('raw-vno') || '') }
        };
        Object.keys(musteriFields).forEach(function(sel){
            var $el = $(sel);
            var f = musteriFields[sel];
            $el.data('original-text', $el.text().trim());
            var html = '';
            if (f.type === 'tel') {
                html = '<div class="d-flex gap-1 w-100">'
                    + '<input type="text" class="form-control form-control-sm musteri-bilgi-input" data-field="tel1" value="' + (f.tel1 || '') + '" style="font-size: 0.85rem; background-color: #f8f9fa; height: 28px; padding-top: 0; padding-bottom: 0;">'
                    + '<input type="text" class="form-control form-control-sm musteri-bilgi-input" data-field="tel2" value="' + (f.tel2 || '') + '" style="font-size: 0.85rem; background-color: #f8f9fa; height: 28px; padding-top: 0; padding-bottom: 0;">'
                    + '</div>';
            } else {
                html = '<input type="text" class="form-control form-control-sm musteri-bilgi-input" value="' + (f.text || '') + '" style="font-size: 0.85rem; background-color: #f8f9fa; height: 28px; padding-top: 0; padding-bottom: 0;">';
            }
            $el.html(html);
        });
        // İl ve İlçe satırlarını sadece düzenleme modunda göster
        $('#modalMusteriIlRow').removeClass('d-none');
        $('#modalMusteriIlceRow').removeClass('d-none');
        // İl ve İlçe alanlarını select yap (iller / ilceler API)
        var $ilEl = $('#modalMusteriIl');
        var $ilceEl = $('#modalMusteriIlce');
        var currentIlId = ($ilEl.data('il-id') || '').toString();
        var currentIlceId = ($ilceEl.data('ilce-id') || '').toString();
        $ilEl.data('original-text', $ilEl.text().trim());
        $ilceEl.data('original-text', $ilceEl.text().trim());
        $.get('/iller', function(iller){
            var ilOptions = iller || [];
            var htmlIl = '<select class="form-select form-select-sm musteri-bilgi-input" data-field="il_id" style="font-size: 0.85rem; background-color: #f8f9fa; height: 28px; padding-top: 0; padding-bottom: 0;">';
            htmlIl += '<option value="">Seçiniz</option>';
            ilOptions.forEach(function(o){
                htmlIl += '<option value="' + (o.id || '') + '"' + (currentIlId && String(o.id) === currentIlId ? ' selected' : '') + '>' + (o.ad || '') + '</option>';
            });
            htmlIl += '</select>';
            $ilEl.html(htmlIl);
            var ilIdForIlceler = currentIlId || (ilOptions.length ? String(ilOptions[0].id) : '0');
            $.get('/ilceler/' + ilIdForIlceler, function(ilceler){
                var ilceOptions = ilceler || [];
                var htmlIlce = '<select class="form-select form-select-sm musteri-bilgi-input" data-field="ilce_id" style="font-size: 0.85rem; background-color: #f8f9fa; height: 28px; padding-top: 0; padding-bottom: 0;">';
                htmlIlce += '<option value="">Seçiniz</option>';
                ilceOptions.forEach(function(o){
                    htmlIlce += '<option value="' + (o.id || '') + '"' + (currentIlceId && String(o.id) === currentIlceId ? ' selected' : '') + '>' + (o.ad || '') + '</option>';
                });
                htmlIlce += '</select>';
                $ilceEl.html(htmlIlce);
                $ilEl.find('select').on('change', function(){
                    var ilId = $(this).val();
                    $.get('/ilceler/' + (ilId || '0'), function(ilceler2){
                        var opts = ilceler2 || [];
                        var h = '<option value="">Seçiniz</option>';
                        opts.forEach(function(o){ h += '<option value="' + (o.id || '') + '">' + (o.ad || '') + '</option>'; });
                        $ilceEl.find('select').html(h);
                    });
                });
            }).fail(function(){ $ilceEl.html('<select class="form-select form-select-sm musteri-bilgi-input" data-field="ilce_id" style="font-size: 0.85rem; background-color: #f8f9fa; height: 28px;"><option value="">Seçiniz</option></select>'); });
        }).fail(function(){ $ilEl.text($ilEl.data('original-text') || '-'); $ilceEl.text($ilceEl.data('original-text') || '-'); });
        // Telefon alanlarını formatla
        var $telInputs = $('#modalMusteriTel').find('input.musteri-bilgi-input');
        if ($telInputs.length && typeof formatPhoneNumber === 'function') {
            $telInputs.each(function(){ formatPhoneNumber(this); });
            $telInputs.on('input', function(){ formatPhoneNumber(this); });
        }
        // Servis düzenleme izni
        var canUpdateServis = (window.PERM && window.PERM.can && window.PERM.can.canUpdateServis && window.PERM.can.canUpdateServis());
        if (!canUpdateServis) {
            $('#modalFooterNormal #servisGuncelleBtn').hide();
        }
        $('#modalFooterNormal').hide();
        $('#modalFooterEdit').show();
        $('#modalSeriNo').parent().show();
    });

    $(document).on('click', '#vazgecBtn', function(){
        if (window._proposalServisDetayShowHandler) return;
        // Önce UI'ı anında eski haline döndür (footer ve alanlar)
        try {
            $('#modalFooterNormal').show();
            $('#modalFooterEdit').hide();
            ['#modalMarkaAd','#modalCihazTuruAd','#modalCihazModel','#modalSeriNo','#modalCihazAriza','#modalMusteriAd','#modalMusteriTel','#modalMusteriAdres','#modalMusteriIl','#modalMusteriIlce','#modalVergiDairesi','#modalVergiNo'].forEach(function(sel){
                var $el = $(sel);
                var orig = $el.data('original-text');
                if (typeof orig !== 'undefined') { $el.text(orig); $el.removeData('original-text'); }
            });
            $('#modalMusteriIlRow').addClass('d-none');
            $('#modalMusteriIlceRow').addClass('d-none');
        } catch(e) { /* no-op */ }
        // İçeriği tazele (güncel veriyi getir)
        var servisId = window.mevcutServisId || $('#servisDetayModal').data('servis-id');
        if (!servisId) return;
        if (typeof window.loadServisDetay === 'function') {
            var _sidReload = window.mevcutServisId || $('#servisDetayModal').data('servis-id');
            if (_sidReload) { window.loadServisDetay(_sidReload); }
        } else {
            $('#servisDetayModal').trigger('show.bs.modal');
        }
    });

    // Modal kapandığında footer'ları güvenli şekilde sıfırla
    $(document).on('hidden.bs.modal', '#servisDetayModal', function(){
        try { $('#modalFooterNormal').show(); $('#modalFooterEdit').hide(); } catch(e) { /* no-op */ }
    });

    $(document).on('click', '#modalCihazKaydetBtn', function(){
        if (window._proposalServisDetayShowHandler) return;
        var servisId = window.mevcutServisId || $('#servisDetayModal').data('servis-id');
        if (!servisId) { Swal && Swal.fire('Hata!', 'Servis ID bulunamadı!', 'error'); return; }
        var formData = {
            _method: 'PUT',
            marka_id: $('#modalMarkaAd').find('select.cihaz-bilgi-input').val(),
            cihaz_tur_id: $('#modalCihazTuruAd').find('select.cihaz-bilgi-input').val(),
            cihaz_model: $('#modalCihazModel').find('input.cihaz-bilgi-input').val(),
            seri_no: $('#modalSeriNo').find('input.cihaz-bilgi-input').val(),
            cihaz_arizasi: $('#modalCihazAriza').find('input.cihaz-bilgi-input').val(),
            musteri_ad: $('#modalMusteriAd').find('input.musteri-bilgi-input').val(),
            musteri_tel1: $('#modalMusteriTel').find('input.musteri-bilgi-input[data-field="tel1"]').val(),
            musteri_tel2: $('#modalMusteriTel').find('input.musteri-bilgi-input[data-field="tel2"]').val(),
            musteri_adres: $('#modalMusteriAdres').find('input.musteri-bilgi-input').val(),
            musteri_il_id: $('#modalMusteriIl').find('select.musteri-bilgi-input').val() || null,
            musteri_ilce_id: $('#modalMusteriIlce').find('select.musteri-bilgi-input').val() || null,
            musteri_vdaire: $('#modalVergiDairesi').find('input.musteri-bilgi-input').val(),
            musteri_vno: $('#modalVergiNo').find('input.musteri-bilgi-input').val()
        };
        var $btn = $('#modalCihazKaydetBtn');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Kaydediliyor...');
        $.ajax({ url: '/servisler/' + servisId, type: 'POST', data: formData })
        .done(function(response){
            if (response && response.success && response.servis) {
                var g = response.servis;
                $('#modalMarkaAd').text(g.marka ? g.marka.ad : 'N/A').data('marka-id', g.marka_id);
                $('#modalCihazTuruAd').text(g.cihazTuru ? g.cihazTuru.ad : 'N/A').data('cihaz-turu-id', g.cihaz_tur_id);
                $('#modalCihazModel').text(g.cihaz_model || 'N/A');
                $('#modalCihazAriza').text(g.cihaz_arizasi || 'N/A');
                (function(){
                    function normalizeVal(val) {
                        return (val || '').toString().trim().toUpperCase();
                    }
                    function shouldHide(val) {
                        return !val || val === 'N/A' || val === 'NULL';
                    }
                    function toggleRowByValue($el, dataVal) {
                        var raw = (typeof dataVal === 'undefined') ? $el.text() : dataVal;
                        var v = normalizeVal(raw);
                        if (shouldHide(v)) {
                            $el.closest('.col-12').addClass('d-none');
                            return;
                        }
                        $el.closest('.col-12').removeClass('d-none');
                    }
                    toggleRowByValue($('#modalCihazModel'), g.cihaz_model);
                })();
                var seriNoRow = $('#modalSeriNo').closest('.col-12');
                var sn = g.seri_no;
                if (sn && sn.trim() !== '' && sn.toUpperCase() !== 'N/A') {
                    seriNoRow.removeClass('d-none').addClass('d-flex');
                    $('#modalSeriNo').text(sn);
                } else {
                    seriNoRow.removeClass('d-flex').addClass('d-none');
                    $('#modalSeriNo').text('N/A');
                }
                if (g.musteri) {
                    $('#modalMusteriAd').text(g.musteri.ad || 'N/A').data('raw-ad', g.musteri.ad || '');
                    window.currentServisMusteriTel1 = g.musteri.tel1 || '';
                    window.currentServisMusteriTel2 = g.musteri.tel2 || '';
                    $('#modalMusteriTel').text((g.musteri.tel1 || '-') + (g.musteri.tel2 ? ' / ' + g.musteri.tel2 : ''));
                    $('#modalMusteriAdres').data('raw-adres', g.musteri.adres || '');
                    var adresText = g.musteri.adres || 'N/A';
                    if (adresText !== 'N/A' && adresText.length > 0) {
                        adresText = adresText.charAt(0).toLocaleUpperCase('tr-TR') + adresText.slice(1);
                    }
                    if (adresText !== 'N/A') {
                        if (g.musteri.ilce && g.musteri.ilce.ad) adresText += ' / ' + g.musteri.ilce.ad;
                        if (g.musteri.il && g.musteri.il.ad) adresText += ' / ' + g.musteri.il.ad;
                    }
                    $('#modalMusteriAdres').text(adresText);
                    $('#modalMusteriIl').text(g.musteri.il ? g.musteri.il.ad : '-').data('il-id', g.musteri.il_id || '');
                    $('#modalMusteriIlce').text(g.musteri.ilce ? g.musteri.ilce.ad : '-').data('ilce-id', g.musteri.ilce_id || '');
                    $('#modalMusteriIlRow').addClass('d-none');
                    $('#modalMusteriIlceRow').addClass('d-none');
                    $('#modalVergiDairesi').text(g.musteri.vdaire || '-').data('raw-vdaire', g.musteri.vdaire || '');
                    $('#modalVergiNo').text(g.musteri.vno || '-').data('raw-vno', g.musteri.vno || '');
                }
                // Loglar güncelle
                if (g.islemloglari && Array.isArray(g.islemloglari)) {
                    var tbody = $('#modalIslemLoglariBody');
                    var html = '';
                    var canEditLog = (window.PERM && window.PERM.can && window.PERM.can.canEditLogs && window.PERM.can.canEditLogs());
                    var canDeleteLog = (window.PERM && window.PERM.can && window.PERM.can.canDeleteLogs && window.PERM.can.canDeleteLogs());
                    g.islemloglari.forEach(function(log){
                        var tarih = (formatDateTr(log.tarih) || '-') + ' ' + (log.saat || '');
                        var yapan = log.personel ? log.personel.ad : ((log.is_system || log.islemi_yapan_personel_id == null) ? 'Sistem' : '-');
                        var ad = log.servis_durum ? log.servis_durum.ad : '-';
                        var aciklama = normalizeLogAciklama(log.aciklama);
                        html += '<tr>'
                              + '<td class="tdd">' + tarih + '</td>'
                              + '<td class="tdd">' + yapan + '</td>'
                              + '<td class="tdd">' + ad + '</td>'
                              + '<td class="tdd">' + aciklama + '</td>'
                              + '<td class="tdd text-center">' + (function(){
                                    var buttons = '';
                                    if (canEditLog) {
                                        buttons += '<button class="btn btn-sm btn-secondary log-duzenle-btn" data-log-id="' + log.id + '" style="padding: 0.1rem 0.3rem;"><i class="feather feather-edit-3"></i></button>';
                                    }
                                    if (canDeleteLog) {
                                        buttons += '<button class="btn btn-sm btn-danger log-sil-btn" data-log-id="' + log.id + '" style="padding: 0.1rem 0.3rem;"><i class="feather feather-trash-2"></i></button>';
                                    }
                                    return buttons ? ('<div class="d-flex justify-content-center gap-1">' + buttons + '</div>') : '';
                                })() + '</td>'
                              + '</tr>';
                    });
                    tbody.html(html);
                }
                try {
                    $(document).trigger('servisDurumuGuncellendi', [servisId, g]);
                } catch (eventError) {
                    console.warn('servisDurumuGuncellendi olayı tetiklenemedi:', eventError);
                }
                // Footerları eski haline getir
                $('#modalFooterNormal').show();
                $('#modalFooterEdit').hide();
            } else {
                Swal && Swal.fire('Hata!', (response && response.message) ? response.message : 'Bir hata oluştu.', 'error');
            }
        })
        .fail(function(jqXHR){
            Swal && Swal.fire('Hata!', 'Cihaz güncellenirken bir hata oluştu: ' + ((jqXHR.responseJSON && jqXHR.responseJSON.message) ? jqXHR.responseJSON.message : ''), 'error');
        })
        .always(function(){
            $btn.prop('disabled', false).html('DEĞİŞİKLİKLERİ KAYDET');
        });
    });

    // === Global: Resim yükleme ===
    // Modal açıldığında input'u hazırla ve kamera desteği kontrolü
    $(document).on('show.bs.modal', '#resimYukleModal', function(){
        var container = $('#resimInputContainer');
        var isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
        var isAndroid = /Android/i.test(navigator.userAgent);
        var isMobile = isIOS || isAndroid;
        var isHTTPS = location.protocol === 'https:';

        console.log('Cihaz ve bağlantı tespit:', {
            isIOS: isIOS,
            isAndroid: isAndroid,
            isMobile: isMobile,
            isHTTPS: isHTTPS,
            userAgent: navigator.userAgent.substring(0, 100) + '...'
        });

        // Mevcut input'u temizle ve yeniden oluştur
        container.empty();

        var inputHtml = '<input class="form-control" type="file" id="resimDosyasi" name="resim" accept="image/*" required>';

        if (isMobile) {
            // Mobil cihazlar için genişletilmiş accept ve capture ekle
            inputHtml = '<input class="form-control" type="file" id="resimDosyasi" name="resim" accept="image/*,image/jpeg,image/jpg,image/png,image/gif,image/webp"';

            if (isHTTPS) {
                // Sadece HTTPS altında capture özelliğini ekle
                if (isIOS) {
                    inputHtml += ' capture="environment"';
                    console.log('iOS + HTTPS: capture="environment" eklendi');
                } else if (isAndroid) {
                    inputHtml += ' capture="camera"';
                    console.log('Android + HTTPS: capture="camera" eklendi');
                }
            } else {
                console.log('HTTP bağlantısı: capture özelliği eklenmedi (HTTPS gereklidir)');
            }

            inputHtml += ' required>';
        }

        container.html(inputHtml);

        // HTTPS kontrolü ve uyarı
        if (isMobile && !isHTTPS) {
            setTimeout(function() {
                if (window.Swal) {
                    Swal.fire({
                        title: 'HTTPS Gerekli',
                        text: 'Kamera özelliğini kullanabilmek için HTTPS bağlantısı gereklidir. HTTP altında sadece galeri seçimi çalışır.',
                        icon: 'warning',
                        confirmButtonText: 'Anladım'
                    });
                }
            }, 1000);
        }

        console.log('Input yeniden oluşturuldu');
    });

    // File seçildiğinde kontrol
    $(document).on('change', '#resimDosyasi', function(){
        var file = this.files[0];
        if (file) {
            // Dosya boyutu kontrolü (10MB)
            if (file.size > 10 * 1024 * 1024) {
                Swal && Swal.fire('Uyarı!', 'Dosya boyutu 10MB\'dan büyük olamaz!', 'warning');
                this.value = '';
                return;
            }

            // Dosya türü kontrolü
            if (!file.type.startsWith('image/')) {
                Swal && Swal.fire('Uyarı!', 'Lütfen sadece resim dosyası seçin!', 'warning');
                this.value = '';
                return;
            }

            console.log('Seçilen resim:', file.name, 'Boyut:', (file.size / 1024 / 1024).toFixed(2) + 'MB', 'Tür:', file.type);
        }
    });

    $(document).on('submit', '#resimUploadForm', function(e){
        if (window._proposalServisDetayShowHandler) return; // /servisler sayfası kendi yönetir
        e.preventDefault();

        // File kontrolü
        var fileInput = $('#resimDosyasi')[0];
        if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
            Swal && Swal.fire('Uyarı!', 'Lütfen bir resim dosyası seçin!', 'warning');
            return;
        }

        var servisId = window.mevcutServisId || $('#servisDetayModal').data('servis-id');
        if (!servisId) { Swal && Swal.fire('Hata!', 'Servis ID bulunamadı. Lütfen önce servis detaylarını açın.', 'error'); return; }

        var form = $(this);
        var formData = new FormData(form[0]);
        var submitBtn = $('#resimYukleSubmitBtn');
        var originalBtnHtml = submitBtn.html();
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Yükleniyor...');

        $.ajax({
            url: '/servisler/' + servisId + '/resim-yukle',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            timeout: 30000 // 30 saniye timeout
        }).done(function(response){
            if (response && response.success) {
                Swal && Swal.fire('Başarılı!', response.message || 'Resim başarıyla yüklendi!', 'success').then(function(){
                    $('#resimYukleModal').modal('hide');
                    form[0].reset();
                    // Modal içeriğini tazele
                    if (typeof window.loadServisDetay === 'function') {
                        var _sidReload = window.mevcutServisId || $('#servisDetayModal').data('servis-id');
                        if (_sidReload) { window.loadServisDetay(_sidReload); }
                    } else {
                        $('#servisDetayModal').trigger('show.bs.modal');
                    }
                });
            } else {
                var msg = (response && response.message) ? response.message : 'Resim yüklenirken bir sorun oluştu.';
                if (response && response.errors) {
                    msg += '<br>' + Object.values(response.errors).map(function(e){ return e[0]; }).join('<br>');
                }
                Swal && Swal.fire('Hata!', msg, 'error');
            }
        }).fail(function(jqXHR, textStatus, errorThrown){
            var msg = 'Resim yüklenirken bir sunucu hatası oluştu.';
            var title = 'Yükleme Hatası';

            console.error('Resim yükleme hatası:', {
                status: jqXHR.status,
                statusText: jqXHR.statusText,
                textStatus: textStatus,
                errorThrown: errorThrown,
                responseText: jqXHR.responseText,
                responseJSON: jqXHR.responseJSON
            });

            if (textStatus === 'timeout') {
                msg = 'Resim yükleme zaman aşımına uğradı (30 saniye). Dosya çok büyük olabilir veya internet bağlantısı yavaş.';
                title = 'Zaman Aşımı';
            } else if (jqXHR.status === 413) {
                msg = 'Dosya boyutu çok büyük! Sunucu limiti aşılmış. Daha küçük bir dosya deneyin.';
                title = 'Dosya Çok Büyük';
            } else if (jqXHR.status === 422) {
                // Validation hatası
                if (jqXHR.responseJSON && jqXHR.responseJSON.errors) {
                    var errors = jqXHR.responseJSON.errors;
                    if (errors.resim) {
                        msg = 'Resim hatası: ' + errors.resim.join(', ');
                    } else {
                        msg = Object.values(errors).map(function(e){ return e[0]; }).join('<br>');
                    }
                } else if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    msg = jqXHR.responseJSON.message;
                }
                title = 'Doğrulama Hatası';
            } else if (jqXHR.status === 500) {
                msg = 'Sunucu hatası oluştu. Lütfen daha sonra tekrar deneyin.';
                title = 'Sunucu Hatası';
            } else if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                msg = jqXHR.responseJSON.message;
            } else if (errorThrown) {
                msg = 'Bağlantı hatası: ' + errorThrown;
            }

            Swal && Swal.fire(title, msg, 'error');
        }).always(function(){
            submitBtn.prop('disabled', false).html(originalBtnHtml);
        });
    });

    // === Global: Kasa hareketi ekleme/silme (özet) ===
    // Çift bağlanmayı kesin engellemek için namespaced delegate kullan ve önce off et
    $(document).off('click.servisKasa', '#odemeEkleBtn').on('click.servisKasa', '#odemeEkleBtn', function(){
        if (!(window.PERM && window.PERM.can && window.PERM.can.canAddKasa && window.PERM.can.canAddKasa())) {
            if (window.Swal && Swal.fire) { Swal.fire('Yetki yok', 'Ödeme ekleme yetkiniz yok.', 'warning'); }
            return;
        }
        if ($('#yeni-odeme-satiri').length > 0) return;
        var kasaTableBody = $('#modalKasaHareketleriBody');
        var today = new Date();
        var yyyy = today.getFullYear();
        var mm = String(today.getMonth() + 1).padStart(2, '0');
        var dd = String(today.getDate()).padStart(2, '0');
        var todayStr = yyyy + '-' + mm + '-' + dd;
        var hh = String(today.getHours()).padStart(2, '0');
        var min = String(today.getMinutes()).padStart(2, '0');
        var saatStr = hh + ':' + min;
        var pozId = window.crmData && window.crmData.loggedInUserPozId != null ? Number(window.crmData.loggedInUserPozId) : null;
        var canEditOdemeTarih = (pozId === 1071 || pozId === 1080); // Patron veya Muhasebe
        var readonlyAttr = canEditOdemeTarih ? '' : ' readonly ';
        var odemeSekliOptions = '<option value="">Seçiniz...</option>';
        (window.tumOdemeSekilleriCache || []).forEach(function(sekil){ odemeSekliOptions += '<option value="' + sekil.id + '">' + sekil.ad + '</option>'; });
        var yeniSatirHtml = ''
            + '<tr id="yeni-odeme-satiri" class="table-info">'
            + '<td class="tdd"><input type="date" id="yeni-odeme-tarih" class="form-control form-control-sm" value="' + todayStr + '"' + readonlyAttr + 'style="font-size: 0.85rem; height: 28px; padding-top: 0; padding-bottom: 0;">'
            + '<input type="time" id="yeni-odeme-saat" class="form-control form-control-sm mt-1" value="' + saatStr + '"' + readonlyAttr + 'style="font-size: 0.85rem; height: 28px; padding-top: 0; padding-bottom: 0;"></td>'
            + '<td class="tdd">' + (window.crmData.loggedInUserName || 'Mevcut Kullanıcı') + '</td>'
            + '<td class="tdd">' + (window.currentServisTahsilEdenName || '-') + '</td>'
            + '<td class="tdd"><select id="yeni-odeme-sekli" class="form-select form-select-sm" style="font-size: 0.85rem; background-color: #f8f9fa; height: 28px; padding-top: 0; padding-bottom: 0; min-width: 120px;">' + odemeSekliOptions + '</select></td>'
            + '<td class="tdd"><select id="yeni-odeme-durumu" class="form-select form-select-sm" style="font-size: 0.85rem; background-color: #f8f9fa; height: 28px; padding-top: 0; padding-bottom: 0; min-width: 120px;"><option value="1" selected>Tamamlandı</option><option value="0">Beklemede</option></select></td>'
            + '<td class="tdd"><input type="number" id="yeni-odeme-tutar" class="form-control form-control-sm" placeholder="0.00" step="0.01" style="font-size: 0.85rem; background-color: #f8f9fa; height: 28px; padding-top: 0; padding-bottom: 0; width: 100px;"></td>'
            + '<td class="tdd text-center">'
            + '<button class="btn btn-sm btn-success p-1" id="yeniOdemeKaydetBtn" title="Bu ödemeyi kaydet"><i class="feather feather-check"></i></button>'
            + '<button class="btn btn-sm btn-danger p-1" id="yeniOdemeIptalBtn" title="İptal et"><i class="feather feather-x"></i></button>'
            + '</td>'
            + '</tr>';
        if (kasaTableBody.find('td[colspan="7"]').length > 0) kasaTableBody.empty();
        var $yeniSatir = $(yeniSatirHtml);
        // Güvenlik: kolon sayısı 7 değilse tahsil eden sütununu düzelt
        if ($yeniSatir.children('td').length === 6) {
            $yeniSatir.children('td').eq(1).after('<td class="tdd">' + (window.currentServisTahsilEdenName || '-') + '</td>');
        }
        kasaTableBody.prepend($yeniSatir);
    });

    $(document).off('click.servisKasa', '#yeniOdemeIptalBtn').on('click.servisKasa', '#yeniOdemeIptalBtn', function(){
        $('#yeni-odeme-satiri').remove();
    });

    // Düzenleme: mevcut satırı input/select'e çevir
    $(document).off('click.servisKasa', '.kasa-duzenle-btn').on('click.servisKasa', '.kasa-duzenle-btn', function(){
        if (!(window.PERM && window.PERM.can.canEditKasa && window.PERM.can.canEditKasa())) return;
        var $row = $(this).closest('tr');
        var kasaId = $(this).data('kasa-id');
        var currentOdemeSekliId = $(this).data('odeme-sekli-id') || '';
        var currentOdemeTuruId = $(this).data('odeme-turu-id') || '';
        var currentGerceklesme = String($(this).data('gerceklesme') ?? '');
        var currentTutar = $(this).data('tutar') || '';
        // Tutarı input için 2 haneye normalize et (nokta ayırıcı)
        if (typeof currentTutar === 'string') { currentTutar = currentTutar.replace(',', '.'); }
        if (currentTutar !== '' && !isNaN(currentTutar)) {
            currentTutar = (Number(currentTutar)).toFixed(2);
        }
        // Mevcut yön ve türü satırdan al (data-*), butondaki data fall-back
        var currentOdemeYonu = String(($row.data('odeme-yonu') ?? $(this).data('odeme-yonu')) ?? '');
        var currentTarih = $(this).data('islem-tarihi') || $(this).data('tarih') || '';
        var currentKayitTarih = $(this).data('tarih') || '';
        var currentSaat = $(this).data('saat') || '';
        // Zaman değerini H:i formatına normalize et ("08:52:37" -> "08:52")
        if (currentSaat && currentSaat.length >= 5) { currentSaat = String(currentSaat).slice(0, 5); }
        if (!kasaId) return;
        var editPozId = window.crmData && window.crmData.loggedInUserPozId != null ? Number(window.crmData.loggedInUserPozId) : null;
        var canEditTarih = (editPozId === 1071 || editPozId === 1080);
        var editReadonlyAttr = canEditTarih ? '' : ' readonly ';
        var odemeSekliOptions = '<option value="">Seçiniz...</option>';
        (window.tumOdemeSekilleriCache || []).forEach(function(sekil){
            odemeSekliOptions += '<option value="' + sekil.id + '"' + (String(currentOdemeSekliId) === String(sekil.id) ? ' selected' : '') + '>' + sekil.ad + '</option>';
        });
        var odemeTuruOptions = '<option value="">Seçiniz...</option>';
        (window.tumOdemeTurleriCache || []).forEach(function(turu){
            odemeTuruOptions += '<option value="' + turu.id + '"' + (String(currentOdemeTuruId) === String(turu.id) ? ' selected' : '') + '>' + turu.ad + '</option>';
        });
        // Kolonlar: Tarih-İşlemi Yapan-Tahsil Eden-Ödeme Şekli-Ödeme Durumu-Tutar-Aksiyon
        var $cells = $row.children('td');
        $cells.eq(0).html('<input type="date" class="form-control form-control-sm" id="edit-tarih" value="' + currentTarih + '"' + editReadonlyAttr + 'style="font-size: 0.85rem; background-color: #f8f9fa; height: 28px; padding-top: 0; padding-bottom: 0;">'
            + '<input type="time" class="form-control form-control-sm mt-1" id="edit-saat" value="' + currentSaat + '"' + editReadonlyAttr + 'style="font-size: 0.85rem; background-color: #f8f9fa; height: 28px; padding-top: 0; padding-bottom: 0;">');
        $cells.eq(3).html('<select class="form-select form-select-sm" id="edit-odeme-sekli" style="font-size: 0.85rem; background-color: #f8f9fa; height: 28px; padding-top: 0; padding-bottom: 0; min-width: 120px;">' + odemeSekliOptions + '</select>');
        $cells.eq(4).html('<select class="form-select form-select-sm" id="edit-gerceklesme" style="font-size: 0.85rem; background-color: #f8f9fa; height: 28px; padding-top: 0; padding-bottom: 0; min-width: 120px;"><option value="0"' + (currentGerceklesme==='0'?' selected':'') + '>Beklemede</option><option value="1"' + (currentGerceklesme==='1'?' selected':'') + '>Tamamlandı</option></select>');
        $cells.eq(5).html('<input type="number" step="0.01" class="form-control form-control-sm" id="edit-tutar" value="' + currentTutar + '" style="font-size: 0.85rem; background-color: #f8f9fa; height: 28px; padding-top: 0; padding-bottom: 0; width: 100px;">');
        var odemeTuruSelect = '';
        if ((window.tumOdemeTurleriCache || []).length > 0) {
            var otOptions = '<option value="">Seçiniz...</option>';
            (window.tumOdemeTurleriCache || []).forEach(function(t){
                otOptions += '<option value="' + t.id + '"' + (String(currentOdemeTuruId) === String(t.id) ? ' selected' : '') + '>' + t.ad + '</option>';
            });
            odemeTuruSelect = '<select class="form-select form-select-sm mb-1" id="edit-odeme-turu" style="font-size: 0.85rem; background-color: #f8f9fa; height: 28px; padding-top: 0; padding-bottom: 0; min-width: 120px;">' + otOptions + '</select>';
        } else {
            // Cache yoksa mevcut değeri korumak için hidden input
            odemeTuruSelect = '<input type="hidden" id="edit-odeme-turu" value="' + currentOdemeTuruId + '">';
        }
        // Kullanıcı giriş/çıkış (odeme_yonu) değiştirmeyecek; mevcut değeri gizli alanla taşıyalım
        var odemeYonuSelect = '<input type="hidden" id="edit-odeme-yonu" value="' + currentOdemeYonu + '">';

        $cells.eq(6).html('<div class="d-flex flex-column align-items-center">'
            + odemeTuruSelect
            + odemeYonuSelect
            + '<div class="d-flex justify-content-center gap-1">'
            +   '<button class="btn btn-sm btn-success p-1 kasa-kaydet-btn" data-kasa-id="' + kasaId + '"><i class="feather feather-check"></i></button>'
            +   '<button class="btn btn-sm btn-secondary p-1 kasa-iptal-btn"><i class="feather feather-x"></i></button>'
            + '</div>'
            + '</div>');
    });

    // Düzenlemeyi iptal: modalı tazeleyelim
    $(document).off('click.servisKasa', '.kasa-iptal-btn').on('click.servisKasa', '.kasa-iptal-btn', function(){
        if (typeof window.loadServisDetay === 'function') {
            var _sidReload = window.mevcutServisId || $('#servisDetayModal').data('servis-id');
            if (_sidReload) { window.loadServisDetay(_sidReload); }
        } else {
            $('#servisDetayModal').trigger('show.bs.modal');
        }
    });

    // Düzenlemeyi kaydet
    $(document).off('click.servisKasa', '.kasa-kaydet-btn').on('click.servisKasa', '.kasa-kaydet-btn', function(){
        if (!(window.PERM && window.PERM.can.canEditKasa && window.PERM.can.canEditKasa())) return;
        var kasaId = $(this).data('kasa-id');
        var servisId = window.mevcutServisId || $('#servisDetayModal').data('servis-id');
        if (!kasaId || !servisId) return;
        var $row = $(this).closest('tr');
        var currentKayitTarih = $row.data('tarih') || '';
        var odeme_sekli_id = $('#edit-odeme-sekli').val();
        var gerceklesme = $('#edit-gerceklesme').val();
        var tutar = $('#edit-tutar').val();
        var tarih = $('#edit-tarih').val();
        var saat = $('#edit-saat').val();
        // Saat alanını H:i formatına zorla
        if (saat && saat.length >= 5) { saat = String(saat).slice(0, 5); }
        var odeme_turu_id = $('#edit-odeme-turu').val() || String(($('#edit-odeme-turu').length ? '' : ($row.data('odeme-turu-id') || '')));
        var odeme_yonu = $('#edit-odeme-yonu').val() || String($row.data('odeme-yonu') || '');
        var $btn = $(this);
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
        $.ajax({ url:'/genelkasa/' + kasaId, method:'POST', data:{ _method:'PUT', odeme_sekli_id: odeme_sekli_id, gerceklesme: gerceklesme, tutar: tutar, tarih: currentKayitTarih || tarih, islem_tarihi: tarih, saat: saat, odeme_turu_id: odeme_turu_id, personel_id: (window.crmData && window.crmData.loggedInUserId) || null, _token: $('meta[name="csrf-token"]').attr('content') } })
        .done(function(resp){
            if (resp && resp.success) {
                Swal && Swal.fire('Başarılı!', resp.message || 'Kasa hareketi güncellendi.', 'success');
                if (typeof window.loadServisDetay === 'function') {
                    var _sidReload = window.mevcutServisId || $('#servisDetayModal').data('servis-id');
                    if (_sidReload) { window.loadServisDetay(_sidReload); }
                } else {
                    $('#servisDetayModal').trigger('show.bs.modal');
                }
            } else {
                Swal && Swal.fire('Hata!', (resp && resp.message) ? resp.message : 'Güncelleme başarısız.', 'error');
            }
        }).fail(function(){
            Swal && Swal.fire('Hata!', 'Güncelleme sırasında bir sorun oluştu.', 'error');
        }).always(function(){
            $btn.prop('disabled', false).html('<i class="feather feather-check"></i>');
        });
    });

    // Silme (ikon handler)
    $(document).off('click.servisKasa', '.kasa-sil-btn').on('click.servisKasa', '.kasa-sil-btn', function(){
        if (!(window.PERM && window.PERM.can.canDeleteKasa && window.PERM.can.canDeleteKasa())) return;
        var kasaId = $(this).data('kasa-id');
        if (kasaId) { window.silServisKasaHareketi(kasaId); }
    });
    // Tek POST koruması
    if (typeof window._kasaEkleInFlight === 'undefined') { window._kasaEkleInFlight = false; }
    $(document).off('click.servisKasa', '#yeniOdemeKaydetBtn').on('click.servisKasa', '#yeniOdemeKaydetBtn', function(){
        if (window._kasaEkleInFlight) return;
        var servisId = window.mevcutServisId || $('#servisDetayModal').data('servis-id');
        if (!servisId) { Swal && Swal.fire('Hata', 'İşlem yapılacak servis bulunamadı!', 'error'); return; }
        var $btn = $(this);
        var odemeSekliId = $('#yeni-odeme-sekli').val();
        var gerceklesme = $('#yeni-odeme-durumu').val();
        var tutar = $('#yeni-odeme-tutar').val();
        var odemeTuruId = 5; // mevcut sistem ile aynı varsayım
        if (!odemeSekliId || !tutar || parseFloat(tutar) < 0) { Swal && Swal.fire('Eksik Bilgi', 'Ödeme şekli ve geçerli bir tutar giriniz.', 'warning'); return; }
        var tarihInput = document.getElementById('yeni-odeme-tarih');
        var saatInput = document.getElementById('yeni-odeme-saat');
        var pozId = window.crmData && window.crmData.loggedInUserPozId != null ? Number(window.crmData.loggedInUserPozId) : null;
        var canEditOdemeTarih = (pozId === 1071 || pozId === 1080);
        var postData = { odeme_sekli_id: odemeSekliId, gerceklesme: gerceklesme, tutar: tutar, odeme_turu_id: odemeTuruId };
        if (canEditOdemeTarih && tarihInput && tarihInput.value) postData.tarih = tarihInput.value;
        if (canEditOdemeTarih && saatInput && saatInput.value) postData.saat = saatInput.value;
        window._kasaEkleInFlight = true;
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
        $.ajax({ url: '/servisler/' + servisId + '/kasa-hareketi-ekle', method: 'POST', data: postData })
        .done(function(response){
            if (response && response.success && response.yeniHareket) {
                Swal && Swal.fire('Başarılı!', response.message, 'success');
                try {
                    if (!Array.isArray(window.currentServisKasaHareketleri)) {
                        window.currentServisKasaHareketleri = [];
                    }
                    window.currentServisKasaHareketleri.unshift(response.yeniHareket);
                } catch (e) { /* no-op */ }
                if (typeof window.loadServisDetay === 'function') {
                    var _sidReload = window.mevcutServisId || $('#servisDetayModal').data('servis-id');
                    if (_sidReload) { window.loadServisDetay(_sidReload); }
                } else {
                    $('#servisDetayModal').trigger('show.bs.modal');
                }
            } else {
                Swal && Swal.fire('Hata!', (response && response.message) ? response.message : 'Bir hata oluştu.', 'error');
                $btn.prop('disabled', false).html('<i class="feather feather-check"></i>');
            }
        }).fail(function(){
            Swal && Swal.fire('Hata!', 'Ödeme kaydedilirken bir hata oluştu.', 'error');
            $btn.prop('disabled', false).html('<i class="feather feather-check"></i>');
        }).always(function(){ window._kasaEkleInFlight = false; });
    });

    // Silme (patron kontrolü frontend’de yapılmıyor, backend yetkisi esas)
    window.silServisKasaHareketi = function(kasaHareketiId){
        if (window._proposalServisDetayShowHandler) return;
        if (!kasaHareketiId) { Swal && Swal.fire('Hata!', 'Kasa hareketi ID bilgisi bulunamadı.', 'error'); return; }
        Swal && Swal.fire({ title:'Emin misiniz?', text:'Bu kasa hareketi #' + kasaHareketiId + ' silinecek!', icon:'warning', showCancelButton:true, confirmButtonColor:'#d33', cancelButtonColor:'#3085d6', confirmButtonText:'Evet, sil!', cancelButtonText:'İptal' })
        .then(function(result){
            if (result.isConfirmed || result.value) {
                $.ajax({ type:'POST', url:'/genelkasa/' + kasaHareketiId, data:{ _method:'DELETE', _token: $('meta[name="csrf-token"]').attr('content') }, dataType:'json' })
                .done(function(response){
                    if (response && response.success) {
                        Swal && Swal.fire('Silindi!', response.message, 'success');
                        try {
                            // DOM'dan satırı kaldır
                            $('#modalKasaHareketleriBody').find('tr[data-kasa-id="' + kasaHareketiId + '"]').remove();
                            // Cache'i güncelle
                            if (Array.isArray(window.currentServisKasaHareketleri)) {
                                window.currentServisKasaHareketleri = window.currentServisKasaHareketleri.filter(function(h){
                                    return String(h.id) !== String(kasaHareketiId);
                                });
                            }
                            // Tablo boşsa bilgilendir
                            if ($('#modalKasaHareketleriBody').find('tr').length === 0) {
                                $('#modalKasaHareketleriBody').html('<tr><td colspan="6" class="text-center">Bu servise ait kasa hareketi bulunamadı.</td></tr>');
                            }
                        } catch (e) { /* no-op */ }
                    } else {
                        Swal && Swal.fire('Hata!', (response && response.message) ? response.message : 'Kayıt silinemedi.', 'error');
                    }
                }).fail(function(xhr){
                    var errorMsg = 'Kayıt silinirken bir sunucu hatası oluştu.';
                    if (xhr.responseJSON && xhr.responseJSON.message) errorMsg = xhr.responseJSON.message;
                    Swal && Swal.fire('Hata!', errorMsg, 'error');
                });
            }
        });
    };

    // === Global: İşlem logu düzenle/sil/kaydet (özet) ===
    window.duzenleIslemLog = function(logId){
        if (window._proposalServisDetayShowHandler) return;
        $('#duzenleIslemLogModal').modal('show');
        $.ajax({ url: '/islemlog/' + logId, type: 'GET' })
        .done(function(response){
            if (response && response.id) {
                $('#duzenleIslemLogId').val(response.id);
                $('#duzenleIslemLogTarih').val(response.tarih);
                $('#duzenleIslemLogSaat').val(response.saat);
                $('#duzenleIslemLogAciklama').val(response.aciklama);
                $('#duzenleIslemLogDurum').val(response.servis_durum_id);
            } else {
                Swal && Swal.fire('Hata!', 'Log detayları alınamadı.', 'error');
                $('#duzenleIslemLogModal').modal('hide');
            }
        }).fail(function(){
            Swal && Swal.fire('Hata!', 'Log detayları getirilirken bir sorun oluştu.', 'error');
            $('#duzenleIslemLogModal').modal('hide');
        });
    };

    window.silIslemLog = function(logId){
        if (window._proposalServisDetayShowHandler) return;

        function performDelete(){
            $.ajax({
                url: '/islemlog/' + logId,
                type: 'POST',
                data: { _method: 'DELETE', _token: $('meta[name="csrf-token"]').attr('content') }
            })
            .done(function(response){
                if (response && response.success) {
                    if (window.Swal && Swal.fire) { Swal.fire('Silindi!', 'İşlem kaydı başarıyla silindi.', 'success'); }
                    // Detayları yeniden çek ve hem logları hem mevcut durum badge'ini tazele
                    var sid = window.mevcutServisId || $('#servisDetayModal').data('servis-id');
                    if (sid) {
                        $.get('/servisler/' + sid + '/detay', function(data){
                            try {
                                window.currentServisMevcutDurumId = (data.mevcutDurumId !== undefined && data.mevcutDurumId !== null) ? String(data.mevcutDurumId) : '';
                            } catch (e) { /* no-op */ }
                            // Loglar
                            var tbody = $('#modalIslemLoglariBody');
                            var html = '';
                            var canEditLog = (window.PERM && window.PERM.can && window.PERM.can.canEditLogs && window.PERM.can.canEditLogs());
                            var canDeleteLog = (window.PERM && window.PERM.can && window.PERM.can.canDeleteLogs && window.PERM.can.canDeleteLogs());
                            (data.islemloglari || []).forEach(function(l){
                                var tarih = (l.tarih || '-') + ' ' + (l.saat || '');
                                var yapan = l.personel ? l.personel.ad : '-';
                                var ad = l.servis_durum ? l.servis_durum.ad : '-';
                                var aciklama = normalizeLogAciklama(l.aciklama);
                                var actions = '';
                                if (canEditLog) actions += '<button class="btn btn-sm btn-secondary log-duzenle-btn" data-log-id="' + l.id + '" style="padding: 0.1rem 0.3rem;"><i class="feather feather-edit-3"></i></button>';
                                if (canDeleteLog) actions += '<button class="btn btn-sm btn-danger log-sil-btn" data-log-id="' + l.id + '" style="padding: 0.1rem 0.3rem;"><i class="feather feather-trash-2"></i></button>';
                                html += '<tr>'
                                    + '<td class="tdd">' + tarih + '</td>'
                                    + '<td class="tdd">' + yapan + '</td>'
                                    + '<td class="tdd">' + ad + '</td>'
                                    + '<td class="tdd">' + aciklama + '</td>'
                                    + '<td class="tdd text-center">' + (actions ? '<div class="d-flex justify-content-center gap-1">' + actions + '</div>' : '') + '</td>'
                                    + '</tr>';
                            });
                            tbody.html(html || '<tr><td colspan="5" class="text-center">İşlem kaydı bulunamadı.</td></tr>');

                            // Mevcut durum badge
                            var durumId = Number(data.mevcutDurumId);
                            var badgeClass = 'badge';
                            if ([9097, 9103, 9477].includes(durumId)) badgeClass += ' bg-soft-dark text-dark';
                            else if ([9098].includes(durumId)) badgeClass += ' bg-soft-secondary text-secondary';
                            else if ([9113, 9100].includes(durumId)) badgeClass += ' bg-soft-warning text-warning';
                            else if (durumId === 9334) badgeClass += ' bg-soft-primary text-primary';
                            else if ([9115, 9114, 9105, 9099].includes(durumId)) badgeClass += ' bg-soft-success text-success';
                            else badgeClass += ' bg-soft-danger text-danger';
                            $('#modalMevcutDurumWrapper').html(data.mevcutDurumAdi ? '<span class="' + badgeClass + '">' + data.mevcutDurumAdi + '</span>' : '<span class="badge bg-secondary">Bilinmiyor</span>');

                            // Durum seçeneklerini yeniden yükle
                            if (typeof window.populateServisDurumDropdown === 'function') {
                                window.populateServisDurumDropdown(data.mevcutDurumId, data.mevcutDurumAdi, data.tumDurumlar || []);
                            }
                            try {
                                $(document).trigger('servisDurumuGuncellendi', [sid, data]);
                                window.lastServisDurumuGuncellemesi = { servisId: sid, servis: data };
                            } catch (eventError) {
                                console.warn('servisDurumuGuncellendi olayı tetiklenemedi:', eventError);
                            }
                        });
                    }
                } else {
                    if (window.Swal && Swal.fire) { Swal.fire('Hata!', (response && response.message) ? response.message : 'Silme işlemi sırasında bir hata oluştu.', 'error'); }
                }
            }).fail(function(jqXHR){
                if (window.Swal && Swal.fire) { Swal.fire('Hata!', 'Silme işlemi başarısız oldu. ' + ((jqXHR.responseJSON && jqXHR.responseJSON.message) ? jqXHR.responseJSON.message : ''), 'error'); }
            });
        }

        if (window.Swal && Swal.fire) {
            Swal.fire({ title:'Emin misiniz?', text:'Bu işlem kaydı silinecek!', icon:'warning', showCancelButton:true, confirmButtonColor:'#3085d6', cancelButtonColor:'#d33', confirmButtonText:'Evet, sil!', cancelButtonText:'İptal' })
            .then(function(result){ if (result.isConfirmed || result.value) { performDelete(); } });
        } else {
            if (window.confirm('Bu işlem kaydı silinecek. Emin misiniz?')) { performDelete(); }
        }
    };

    window.kaydetIslemLog = function(){
        if (window._proposalServisDetayShowHandler) return;
        var logId = $('#duzenleIslemLogId').val();
        var formData = {
            tarih: $('#duzenleIslemLogTarih').val(),
            saat: $('#duzenleIslemLogSaat').val(),
            aciklama: $('#duzenleIslemLogAciklama').val(),
            servis_durum_id: $('#duzenleIslemLogDurum').val()
        };
        var $kaydetButton = $('#duzenleIslemLogModal .modal-footer button.btn-primary');
        $kaydetButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Kaydediliyor...');
        $.ajax({ url: '/islemlog/' + logId, type: 'PUT', data: formData })
        .done(function(response){
            if (response && response.success) {
                $('#duzenleIslemLogModal').modal('hide');
                Swal && Swal.fire('Başarılı!', 'İşlem kaydı güncellendi.', 'success');
                var sid = window.mevcutServisId || $('#servisDetayModal').data('servis-id');
                if (sid) {
                    $.get('/servisler/' + sid + '/detay', function(data){
                        try {
                            $(document).trigger('servisDurumuGuncellendi', [sid, data]);
                            window.lastServisDurumuGuncellemesi = { servisId: sid, servis: data };
                        } catch (eventError) {
                            console.warn('servisDurumuGuncellendi olayı tetiklenemedi:', eventError);
                        }
                        if (typeof window.loadServisDetay === 'function') {
                            var _sidReload = window.mevcutServisId || $('#servisDetayModal').data('servis-id');
                            if (_sidReload) { window.loadServisDetay(_sidReload); }
                        } else {
                            $('#servisDetayModal').trigger('show.bs.modal');
                        }
                    });
                } else {
                    if (typeof window.loadServisDetay === 'function') {
                        var _sidReload = window.mevcutServisId || $('#servisDetayModal').data('servis-id');
                        if (_sidReload) { window.loadServisDetay(_sidReload); }
                    } else {
                        $('#servisDetayModal').trigger('show.bs.modal');
                    }
                }
            } else {
                Swal && Swal.fire('Hata!', (response && response.message) ? response.message : 'Güncelleme işlemi sırasında bir hata oluştu.', 'error');
            }
        }).fail(function(jqXHR){
            Swal && Swal.fire('Hata!', 'Güncelleme işlemi başarısız oldu. ' + ((jqXHR.responseJSON && jqXHR.responseJSON.message) ? jqXHR.responseJSON.message : ''), 'error');
        }).always(function(){
            $kaydetButton.prop('disabled', false).html('Kaydet');
        });
    };

    // Delegated click handlers for dynamically rendered action buttons
    $(document).on('click', '#modalIslemLoglariBody .log-duzenle-btn', function(e){
        e.preventDefault();
        var id = $(this).data('log-id');
        if (id) { window.duzenleIslemLog(id); }
    });
    $(document).on('click', '#modalIslemLoglariBody .log-sil-btn', function(e){
        e.preventDefault();
        var id = $(this).data('log-id');
        if (id) { window.silIslemLog(id); }
    });

    // === Global: Servis Kaydını Sil (Soft Delete) ===
    window.silServisKaydini = function(){
        if (window._proposalServisDetayShowHandler) return; // /servisler sayfası kendi yönetir
        var servisId = window.mevcutServisId || $('#servisDetayModal').data('servis-id');
        if (!servisId) { Swal && Swal.fire('Hata!', 'Silinecek servis ID bilgisi bulunamadı.', 'error'); return; }

        Swal && Swal.fire({
            title: 'Emin misiniz?',
            html: '<b>#' + servisId + '</b> ID\'li servis kaydı silinecek ve listede görünmeyecektir.<br>Bu işlem geri alınamaz!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Evet, Sil!',
            cancelButtonText: 'İptal'
        }).then(function(result){
            if (result.isConfirmed || result.value) {
                var $silButonu = $('#servisSilBtn');
                var originalButtonText = $silButonu.html();
                $silButonu.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Siliniyor...');

                $.ajax({
                    type: 'POST',
                    url: '/servisler/' + servisId + '/soft-delete',
                    data: { _token: $('meta[name="csrf-token"]').attr('content') },
                    dataType: 'json'
                }).done(function(response){
                    if (response && response.success) {
                        Swal && Swal.fire('Silindi!', response.message, 'success').then(function(){
                            var detailModal = bootstrap.Modal.getInstance(document.getElementById('servisDetayModal'));
                            if (detailModal) { detailModal.hide(); }
                            if (typeof window._servisListReload === 'function') {
                                window._servisListReload();
                            } else {
                                location.reload();
                            }
                        });
                    } else {
                        Swal && Swal.fire('Hata!', (response && response.message) ? response.message : 'Servis silinirken bir sorun oluştu.', 'error');
                    }
                }).fail(function(xhr){
                    var errorMsg = 'Servis silinirken bir sunucu hatası oluştu.';
                    if (xhr.responseJSON && xhr.responseJSON.message) errorMsg = xhr.responseJSON.message;
                    Swal && Swal.fire('Hata!', errorMsg, 'error');
                }).always(function(){
                    $silButonu.prop('disabled', false).html(originalButtonText);
                });
            }
        });
    };

    // === Global: Servis Fişi PDF ve İmza akışları ===
    function scheduleServisDetayRestore() {
        setTimeout(function(){
            var $servisDetayModal = $('#servisDetayModal');
            if ($servisDetayModal.hasClass('show')) return;
            if ($('.modal.show').length === 0) {
                $('body').removeClass('modal-open').css('padding-right', '');
                $('.modal-backdrop').remove();
            }
            $servisDetayModal.modal('show');
        }, 150);
    }

    $(document).on('shown.bs.modal', '#servisFisiModal', function(){
        try {
            document.body.classList.add('servis-fisi-modal-open');
            document.body.style.paddingRight = '0px';
            this.style.paddingRight = '0px';
            var $dialog = $(this).find('.modal-dialog');
            if ($dialog.length) {
                $dialog.css({ maxWidth: '700px', width: 'calc(100% - 1rem)' });
            }
        } catch (e) { /* no-op */ }
    });

    $(document).on('show.bs.modal', '#servisFisiModal', function(){
        if (window._proposalServisDetayShowHandler) return;
        var $servisDetayModal = $('#servisDetayModal');
        if ($servisDetayModal.hasClass('show')) {
            $servisDetayModal.data('restore-after-servis-fisi', true);
            $servisDetayModal.modal('hide');
        }
        var servisId = window.mevcutServisId || $servisDetayModal.data('servis-id');
        if (servisId) {
            $('#servisFisiModalServisId').text(servisId);
            // Önceki fişleri listele
            var listElement = $('#eskiServisFisleriListesi');
            listElement.html('<p class="text-muted text-center">Fişler yükleniyor...</p>');
            $.ajax({ url: '/servisler/' + servisId + '/fisleri-listele', type: 'GET', dataType: 'json' })
            .done(function(fisler){
                var parsedFisler = fisler;
                if (parsedFisler && Array.isArray(parsedFisler.data)) {
                    parsedFisler = parsedFisler.data;
                }
                if (!Array.isArray(parsedFisler)) {
                    parsedFisler = [];
                }
                if (parsedFisler.length > 0) {
                    var fislerHtml = '';
                    parsedFisler.forEach(function(fis){
                        var olusturmaTarihi = new Date(fis.tarih + 'T' + fis.saat).toLocaleString('tr-TR', { day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit' });
                        var dosyaAdi = fis.pdf ? fis.pdf.split('/').pop() : 'Fiş Adı Yok';
                        var pdfUrl = fis.pdf_url || (fis.pdf ? (window.location.origin + '/storage/' + fis.pdf) : null);
                        if (!pdfUrl) { return; }
                        fislerHtml += '<a href="' + pdfUrl + '" target="_blank" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">';
                        fislerHtml += '<span><i class="feather feather-file-text me-2"></i>' + dosyaAdi + '</span>';
                        fislerHtml += '<small class="text-muted">' + olusturmaTarihi + '</small>';
                        fislerHtml += '</a>';
                    });
                    listElement.html(fislerHtml);
                } else {
                    listElement.html('<p class="text-muted text-center">Bu servise ait daha önce oluşturulmuş fiş bulunmamaktadır.</p>');
                }
            }).fail(function(){
                listElement.html('<p class="text-danger text-center">Daha önceki fişler yüklenirken bir hata oluştu.</p>');
            });
        } else {
            $('#servisFisiModalServisId').text('N/A');
            $('#eskiServisFisleriListesi').html('<p class="text-danger text-center">Servis ID bulunamadığı için fişler yüklenemedi.</p>');
        }
    });

    $(document).on('hidden.bs.modal', '#servisFisiModal', function(){
        if (window._proposalServisDetayShowHandler) return;
        try {
            document.body.classList.remove('servis-fisi-modal-open');
            document.body.style.paddingRight = '';
            this.style.paddingRight = '';
        } catch (e) { /* no-op */ }
        var $servisDetayModal = $('#servisDetayModal');
        if ($servisDetayModal.data('restore-after-servis-fisi')) {
            $servisDetayModal.data('restore-after-servis-fisi', false);
            scheduleServisDetayRestore();
        }
    });

    $(document).on('click', '#yeniServisFisiOlusturBtn', function(){
        if (window._proposalServisDetayShowHandler) return;
        var servisId = window.mevcutServisId || $('#servisDetayModal').data('servis-id');
        if (!servisId) { Swal && Swal.fire('Hata!', 'Servis ID bulunamadı.', 'error'); return; }
        $('#imzaModalServisId').text(servisId);
        var imzaModal = new bootstrap.Modal(document.getElementById('imzaModal'));
        // İmza pedlerini modal göründükten sonra ilklendir
        $('#imzaModal').off('shown.bs.modal').on('shown.bs.modal', function(){
            var musteriCanvas = document.getElementById('musteriImzaAlani');
            var teknisyenCanvas = document.getElementById('teknisyenImzaAlani');
            if (!musteriCanvas || !teknisyenCanvas) return;

            // Mobil cihaz kontrolü
            var isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) || window.innerWidth <= 768;
            var isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;

            function resizeCanvas(canvas){
                var wrapper = canvas.parentElement;
                var rect = wrapper.getBoundingClientRect();

                // Mobil cihazlarda basit yaklaşım - device pixel ratio kullanma
                var ratio = 1; // Her zaman 1 kullan (koordinat sorunlarını önler)

                // Tüm cihazlarda ortak boyut sınırları kullan (koordinat sorunlarını önler)
                var width = Math.min(rect.width, isMobile ? 480 : 600); // Mobil için max 480px, masaüstü için max 600px
                var height = isMobile ? Math.max(rect.height - 8, 180) : rect.height;

                // Canvas'ın gerçek boyutunu ayarla (ratio olmadan)
                canvas.width = Math.floor(width);
                canvas.height = Math.floor(height);

                var ctx = canvas.getContext('2d');
                // ctx.scale(ratio, ratio); // Ratio kullanma
                ctx.imageSmoothingEnabled = true;
                ctx.imageSmoothingQuality = 'high';

                // Görsel boyutu ayarla
                canvas.style.width = width + 'px';
                canvas.style.height = height + 'px';

                // Canvas'ın konumunu sakla (touch koordinatları için)
                canvas._canvasRect = rect;
                canvas._scaleRatio = ratio;

                return { width: width, height: height, ratio: ratio };
            }

            // Canvas'ları yeniden boyutlandır
            var musteriSize = resizeCanvas(musteriCanvas);
            var teknisyenSize = resizeCanvas(teknisyenCanvas);

            // SignaturePad seçenekleri - mobil için optimize edilmiş
            var signaturePadOptions = {
                backgroundColor: 'rgb(255,255,255)',
                penColor: 'rgb(0,0,0)',
                velocityFilterWeight: isMobile ? 0.0 : 0.7, // Mobil'de velocity filter'ı kapat
                minWidth: isMobile ? 1 : 0.5, // Mobil'de daha ince çizgi
                maxWidth: isMobile ? 2 : 2.5, // Mobil'de daha ince çizgi
                throttle: isMobile ? 0 : 16, // Mobil'de throttle'ı kapat
                minDistance: isMobile ? 1 : 5, // Mobil'de daha küçük minimum mesafe
                dotSize: isMobile ? 1.5 : 1.5, // Aynı kalınlıkta nokta
                penPressure: false // Mobil'de basınç sensing'i kapat
            };

            // iOS için özel seçenekler
            if (isIOS) {
                signaturePadOptions.velocityFilterWeight = 0.0;
                signaturePadOptions.throttle = 0;
                signaturePadOptions.minDistance = 0.5;
                signaturePadOptions.minWidth = 0.8;
                signaturePadOptions.maxWidth = 1.5;
            }

            // Önceki instance'ları temizle
            if (window.musteriSignaturePad) {
                window.musteriSignaturePad.off();
                window.musteriSignaturePad.clear();
            }
            if (window.teknisyenSignaturePad) {
                window.teknisyenSignaturePad.off();
                window.teknisyenSignaturePad.clear();
            }

            function patchSignaturePadForInvalidEvents(signaturePad) {
                if (!signaturePad) return;
                ['_handlePointerStart', '_handlePointerMove', '_handlePointerEnd'].forEach(function(methodName){
                    var original = signaturePad[methodName];
                    if (typeof original !== 'function') return;
                    signaturePad[methodName] = function(event){
                        if (!event || typeof event.preventDefault !== 'function') {
                            return;
                        }
                        return original.call(this, event);
                    };
                });
            }

            // Yeni SignaturePad instance'ları oluştur
            window.musteriSignaturePad = new SignaturePad(musteriCanvas, signaturePadOptions);
            window.teknisyenSignaturePad = new SignaturePad(teknisyenCanvas, signaturePadOptions);
            patchSignaturePadForInvalidEvents(window.musteriSignaturePad);
            patchSignaturePadForInvalidEvents(window.teknisyenSignaturePad);

            // Mobil cihazlarda SignaturePad'in kendi event'leri yeterli
            if (isMobile) {
                // Gerekirse burada mobil özel iyileştirmeler eklenebilir.
            }

            console.log('İmza canvas\'ları yeniden boyutlandırıldı:', {
                isMobile: isMobile,
                isIOS: isIOS,
                musteriSize: musteriSize,
                teknisyenSize: teknisyenSize,
                options: signaturePadOptions
            });
        });
        imzaModal.show();
    });

    $(document).on('click', '#musteriImzaTemizleBtn', function(){
        if (window.musteriSignaturePad) {
            window.musteriSignaturePad.clear();
            console.log('Müşteri imza alanı temizlendi');
        }
    });

    $(document).on('click', '#teknisyenImzaTemizleBtn', function(){
        if (window.teknisyenSignaturePad) {
            window.teknisyenSignaturePad.clear();
            console.log('Teknisyen imza alanı temizlendi');
        }
    });

    // Modal kapandığında SignaturePad instance'larını temizle
    $(document).on('hidden.bs.modal', '#imzaModal', function(){
        console.log('İmza modal kapatıldı, instance\'lar korunuyor');
        var $servisDetayModal = $('#servisDetayModal');
        if (window._restoreServisDetayAfterPdf && $servisDetayModal.data('restore-after-servis-fisi')) {
            window._restoreServisDetayAfterPdf = false;
            scheduleServisDetayRestore();
        }
    });

    // Pencere boyutu değiştiğinde canvas'ları yeniden boyutlandır
    var resizeTimeout;
    $(window).on('resize', function(){
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function(){
            if ($('#imzaModal').hasClass('show')) {
                console.log('Pencere boyutu değişti, imza canvas\'ları yeniden boyutlandırılıyor');
                $('#imzaModal').trigger('shown.bs.modal');
            }
        }, 250);
    });

    // Modal yeniden konumlandırıldığında canvas rect'ini güncelle
    $(document).on('shown.bs.modal', '#imzaModal', function(){
        // Kısa bir gecikme ile canvas rect'ini güncelle
        setTimeout(function(){
            var musteriCanvas = document.getElementById('musteriImzaAlani');
            var teknisyenCanvas = document.getElementById('teknisyenImzaAlani');

            if (musteriCanvas) {
                musteriCanvas._canvasRect = musteriCanvas.getBoundingClientRect();
            }
            if (teknisyenCanvas) {
                teknisyenCanvas._canvasRect = teknisyenCanvas.getBoundingClientRect();
            }
        }, 100);
    });

    function extractFilename(contentDisposition){
        if (!contentDisposition) return null; var filename = null;
        var filenameRegex = /filename[^;=\n]*=((['"])(?:\\.|(?!\2).)*\2|[^;\n]*)/i; var matches = filenameRegex.exec(contentDisposition);
        if (matches != null && matches[1]) filename = matches[1].replace(/['"]/g, ''); return filename;
    }

    function generateAndOpenPdf(servisId, musteriImzaBase64, teknisyenImzaBase64){
        $.ajax({
            url: '/servisler/' + servisId + '/fis/olustur-ve-goster',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                musteri_imza_data: musteriImzaBase64,
                teknisyen_imza_data: teknisyenImzaBase64
            },
            xhrFields: { responseType: 'blob' }
        }).done(function(blob, status, xhr){
            window._restoreServisDetayAfterPdf = true;
            $('#imzaModal').modal('hide'); $('#servisFisiModal').modal('hide');
            var filename = extractFilename(xhr.getResponseHeader('Content-Disposition')) || ('servis_fisi_' + servisId + '.pdf');
            var link = document.createElement('a'); link.href = window.URL.createObjectURL(blob); link.download = filename; document.body.appendChild(link); link.click(); document.body.removeChild(link); window.URL.revokeObjectURL(link.href);
            Swal && Swal.fire('Başarılı', 'Servis fişi oluşturuldu ve indiriliyor.', 'success');
            var pdfUrl = xhr.getResponseHeader('X-Pdf-Url');
            openWhatsappModal(servisId, pdfUrl);
        }).fail(function(xhr){
            Swal && Swal.fire('Hata!', 'Servis fişi oluşturulurken bir sorun oluştu. ' + ((xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : (xhr.responseText || '')), 'error');
        });
    }

    function normalizePhoneForWhatsApp(phone) {
        if (!phone) return '';
        var digits = String(phone).replace(/\D/g, '');
        if (!digits) return '';
        if (digits.startsWith('0')) {
            digits = '90' + digits.substring(1);
        } else if (!digits.startsWith('90')) {
            digits = '90' + digits;
        }
        return digits;
    }

    function openWhatsappModal(servisId, directPdfUrl) {
        var tel1 = window.currentServisMusteriTel1 || '';
        var tel2 = window.currentServisMusteriTel2 || '';
        var whatsappTel = normalizePhoneForWhatsApp(tel1) || normalizePhoneForWhatsApp(tel2);

        $('#servisFisiWhatsappLink').attr('href', '#').addClass('disabled');
        $('#servisFisiWhatsappNumber').text(whatsappTel ? '+' + whatsappTel : 'Telefon bulunamadı');
        $('#servisFisiWhatsappPdfLink').text('PDF hazırlanıyor...');
        $('#servisFisiWhatsappModal').modal('show');

        if (directPdfUrl) {
            $('#servisFisiWhatsappPdfLink').text(directPdfUrl);
            if (whatsappTel) {
                var mesaj = 'Servis formu: ' + directPdfUrl;
                var waUrl = 'https://wa.me/' + whatsappTel + '?text=' + encodeURIComponent(mesaj);
                $('#servisFisiWhatsappLink').attr('href', waUrl).removeClass('disabled');
            }
            return;
        }

        $.get('/servisler/' + servisId + '/fisleri-listele', function(list){
            var parsedList = list;
            if (typeof parsedList === 'string') {
                try { parsedList = JSON.parse(parsedList); } catch (e) { parsedList = null; }
            }
            if (parsedList && Array.isArray(parsedList.data)) {
                parsedList = parsedList.data;
            }
            if (!Array.isArray(parsedList)) {
                parsedList = [];
            }
            var fis = parsedList.length ? parsedList[0] : null;
            if (!fis || !fis.id) {
                $('#servisFisiWhatsappPdfLink').text('PDF bulunamadı');
                return;
            }
            var pdfUrl = fis.pdf_url || (fis.pdf ? (window.location.origin + '/storage/' + fis.pdf) : null);
            if (!pdfUrl) {
                $('#servisFisiWhatsappPdfLink').text('PDF bulunamadı');
                return;
            }
            $('#servisFisiWhatsappPdfLink').text(pdfUrl);
            if (whatsappTel) {
                var mesaj = 'Servis formu: ' + pdfUrl;
                var waUrl = 'https://wa.me/' + whatsappTel + '?text=' + encodeURIComponent(mesaj);
                $('#servisFisiWhatsappLink').attr('href', waUrl).removeClass('disabled');
            }
        }).fail(function(){
            $('#servisFisiWhatsappPdfLink').text('PDF bulunamadı');
        });
    }

    $(document).on('click', '#servisFormuImzasizVerBtn', function(){
        if (window._proposalServisDetayShowHandler) return;
        var servisId = window.mevcutServisId || $('#servisDetayModal').data('servis-id');
        if (!servisId) return; $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> İşleniyor...');
        generateAndOpenPdf(servisId, null, null);
        $(this).prop('disabled', false).html('<i class="feather-file-minus me-1"></i> Servis Formunu İmzasız Ver');
    });

    $(document).on('click', '#imzalariKaydetVeFormuVerBtn', function(){
        if (window._proposalServisDetayShowHandler) return;
        var servisId = window.mevcutServisId || $('#servisDetayModal').data('servis-id');
        if (!servisId) return; var musteriImzaData = (window.musteriSignaturePad && !window.musteriSignaturePad.isEmpty()) ? window.musteriSignaturePad.toDataURL('image/png') : null; var teknisyenImzaData = (window.teknisyenSignaturePad && !window.teknisyenSignaturePad.isEmpty()) ? window.teknisyenSignaturePad.toDataURL('image/png') : null;
        $('#imzaHataMesaji').addClass('d-none'); $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> İşleniyor...');
        generateAndOpenPdf(servisId, musteriImzaData, teknisyenImzaData);
        $('#imzalariKaydetVeFormuVerBtn').prop('disabled', false).html('<i class="feather-check-circle me-1"></i> İmzaları Kaydet & Servis Formunu Ver');
    });

})(window, window.jQuery);



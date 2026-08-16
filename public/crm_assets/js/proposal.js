const tumServisDurumlari = window.crmData.servisDurumlar;
const tumServisDurumSorular = window.crmData.servisDurumSorular;
const tumPersoneller = window.crmData.personeller;
const markalar = window.crmData.markalar; // Değişken ismi Blade'deki @foreach ile tutarlı olsun
const cihazTurleri = window.crmData.cihazTurleri; // Değişken ismi Blade'deki @foreach ile tutarlı olsun
let mevcutServisId = null;

let activeFilterType = null; // 'bolge', 'operator' veya null (ilk yükleme/filtresiz)
const canBulkServisDurum = (window.crmData && [1071, 1080].includes(Number(window.crmData.loggedInUserPozId)));

$(document).ready(function() {
    var servisListDataTable = null;
    let servisDurumGuncellemeBekleniyor = false;
    let selectedServisIds = new Set();

    $(document).on('servisDurumuGuncellendi', function() {
        servisDurumGuncellemeBekleniyor = true;
    });

    $(document).on('hidden.bs.modal', '#servisDetayModal', function() {
        if (!servisDurumGuncellemeBekleniyor) {
            return;
        }
        servisDurumGuncellemeBekleniyor = false;
        if (servisListDataTable && servisListDataTable.ajax) {
            servisListDataTable.ajax.reload(null, false);
        }
    });

    // Tüm AJAX istekleri için CSRF token'ını global olarak ayarla (DOM hazır olduğunda)
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });


    // PERM: Sayfa üstündeki buton/dropdown görünürlükleri
    try {
        if (window.PERM && window.PERM.can) {
            // Yeni Servis butonu (Bugünkü İptaller sayfasında gizli)
            if (
                !(window.crmData && window.crmData.todayCancellationsOnly) &&
                window.PERM.can.canCreateServis &&
                window.PERM.can.canCreateServis()
            ) {
                $('#yeniServisAcBtn').show();
            } else {
                $('#yeniServisAcBtn, #yeniServisAcBtnMobile').hide();
            }
            // Bölge filtresi (teknisyen görmez; masaüstü + mobil)
            if (window.PERM.can.canViewBolgeFilter && window.PERM.can.canViewBolgeFilter()) {
                var isHariciOperator = window.crmData && String(window.crmData.loggedInUserPozId) === '1076';
                if (isHariciOperator) {
                    $('#bolgeServisleriDropdown, #bolgeServisleriDropdownMobile').closest('.dropdown').hide();
                } else {
                    $('#bolgeServisleriDropdown, #bolgeServisleriDropdownMobile').closest('.dropdown').show();
                }
            } else {
                $('#bolgeServisleriDropdown, #bolgeServisleriDropdownMobile').closest('.dropdown').hide();
            }
            // Operatör filtresi
            if (window.PERM.can.canViewOperatorFilter && window.PERM.can.canViewOperatorFilter()) {
                $('#operatorServisleriDropdown').closest('.dropdown').show();
            } else {
                $('#operatorServisleriDropdown').closest('.dropdown').hide();
            }
            // Teknisyen filtresi
            if (window.PERM.can.canViewTeknisyenFilter && window.PERM.can.canViewTeknisyenFilter()) {
                $('#teknisyenServisleriDropdown').closest('.dropdown').show();
            } else {
                $('#teknisyenServisleriDropdown').closest('.dropdown').hide();
            }
            // Servis durum filtresi (teknisyen hariç)
            if (
                window.PERM.roles &&
                window.PERM.roles.TEKNISYEN_TSRN &&
                (!window.crmData || window.crmData.loggedInUserPozId !== window.PERM.roles.TEKNISYEN_TSRN)
            ) {
                var isHariciOperator = window.crmData && String(window.crmData.loggedInUserPozId) === '1076';
                if (isHariciOperator) {
                    $('#servisDurumDropdown').closest('.dropdown').hide();
                } else {
                    $('#servisDurumDropdown').closest('.dropdown').show();
                }
            } else {
                $('#servisDurumDropdown').closest('.dropdown').hide();
            }
        }
    } catch(e) { /* no-op */ }

    $.fn.dataTable.ext.search.push(
        function( settings, data, dataIndex ) {
            if (!settings || !settings.nTable || settings.nTable.id !== 'servisListTable') {
                return true;
            }
            if (settings.oFeatures && settings.oFeatures.bServerSide) {
                return true;
            }
            var rawSearchTerm = $('#servisListTable_filter input').val();
            var searchTerm = normalizeTurkishChars(rawSearchTerm);
            var numericSearch = rawSearchTerm ? rawSearchTerm.replace(/\D/g, '') : '';
            if (searchTerm === "") return true;
            for (var i = 0; i < data.length; i++) {
                if (data[i] && normalizeTurkishChars(data[i].toString()).indexOf(searchTerm) !== -1) {
                    return true;
                }
                if (numericSearch && data[i]) {
                    var numericValue = data[i].toString().replace(/\D/g, '');
                    if (numericValue.indexOf(numericSearch) !== -1) {
                        return true;
                    }
                }
            }

            if (numericSearch) {
                var api = new $.fn.dataTable.Api(settings);
                var rowData = api.row(dataIndex).data();
                if (rowData && rowData.musteri) {
                    var tel1 = rowData.musteri.tel1 ? String(rowData.musteri.tel1).replace(/\D/g, '') : '';
                    var tel2 = rowData.musteri.tel2 ? String(rowData.musteri.tel2).replace(/\D/g, '') : '';
                    if ((tel1 && tel1.indexOf(numericSearch) !== -1) || (tel2 && tel2.indexOf(numericSearch) !== -1)) {
                        return true;
                    }
                }
            }

            return false;
        }
    );

    // Masaüstü alanlar d-none ile gizlense de .val() dolu kalır (tarihler varsayılan bugün).
    // Mobilde önce Mobile alanları okunmalı; aksi halde Antalya + geçmiş tarih masaüstü bugünüyle ezilir.
    function isMobileFilterViewport() {
        return window.matchMedia && window.matchMedia('(max-width: 575.98px)').matches;
    }

    function getVisibleFilterValue(desktopSelector, mobileSelector) {
        var $desktop = $(desktopSelector);
        var $mobile = $(mobileSelector);
        var preferMobile = isMobileFilterViewport();
        var $primary = preferMobile ? $mobile : $desktop;
        var $secondary = preferMobile ? $desktop : $mobile;

        if ($primary.length) {
            return $primary.val();
        }
        if ($secondary.length) {
            return $secondary.val();
        }
        return '';
    }

    let servisColumns = [
        { 
            "data": "id",
            "render": function (data, type, row) {

                // ---- ÖNCEKİ AJAX YAPISINA DÖNÜYORUZ ----
                return '<span class="fw-normal">' + row.id + '</span>';
            }
        },
        {
            "data": "created_at",
            "render": function (data, type, row) {
                if (!row.created_at) return '-';
                try {
                    const tarihObj = new Date(row.created_at);
                    const tarih = tarihObj.toLocaleDateString('tr-TR', { day: '2-digit', month: '2-digit', year: 'numeric' });
                    const saat = tarihObj.toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' });
                    return '<div class="fs-11">' + tarih + '</div><div class="fs-11 text-muted">' + saat + '</div>';
                } catch (e) { return row.created_at; }
            }
        },
        {
            "data": "musteri",
            "render": function (data, type, row) {
                if (!row.musteri) return 'N/A';
                const musteriAd = row.musteri.ad ? ucwordsJs(row.musteri.ad.toLowerCase()) : 'N/A';
                const tel1 = row.musteri.tel1 || '';
                const tel2 = row.musteri.tel2 || '';
                const ilce = row.musteri.ilce && row.musteri.ilce.ad ? ucwordsJs(row.musteri.ilce.ad.toLowerCase()) : '';
                const il = row.musteri.il && row.musteri.il.ad ? ucwordsJs(row.musteri.il.ad.toLowerCase()) : '';
                const adres = row.musteri.adres ? ucfirstJs(String(row.musteri.adres).toLowerCase()) : '';
                const adresParca = [ilce, il].filter(Boolean).join(' / ');

                if (type === 'filter') {
                    const digits = (tel1 + ' ' + tel2).replace(/\D/g, '');
                    return [musteriAd, tel1, tel2, digits, adres, adresParca].join(' ');
                }

                let musteriHtml = '<a href="#" class="hstack gap-3">';
                musteriHtml += '<div><span class="fw-bold">' + musteriAd + '</span>';
                musteriHtml += '<small class="fs-11 fw-normal text-muted d-block">' + tel1;
                if (tel2) musteriHtml += ' / ' + tel2;
                musteriHtml += '</small>';
                if (adres || adresParca) {
                    let adresText = adres;
                    if (adresParca) {
                        adresText += (adres ? ' - ' : '') + adresParca;
                    }
                    musteriHtml += '<small class="fs-11 fw-normal text-muted d-block">' + adresText + '</small>';
                }
                musteriHtml += '</div></a>';
                return musteriHtml;
            }
        },
        {
            "data": null, 
            "render": function (data, type, row) {
                let markaAd = row.marka ? row.marka.ad : 'N/A';
                let cihazTuruAd = row.cihaz_turu ? row.cihaz_turu.ad : 'N/A';
                let cihazArizasi = row.cihaz_arizasi ? ucfirstJs(row.cihaz_arizasi.toLowerCase()) : '';
                return '<span class="fw-bold text-dark">' + markaAd + ' / ' + cihazTuruAd + '</span><br><small class="fs-12 fw-normal text-muted fst-italic cihaz-ariza-text">' + cihazArizasi + '</small>';
            }
        },
        {
            "data": "servis_durum",
            "render": function (data, type, row) {
                const durumAd = row.servis_durum ? row.servis_durum.ad : 'Bilinmiyor';
                const assignedPersonnelId = row.assignedPersonnelId;
                const assignedPersonnelName = (row.assignedPersonnelName && row.assignedPersonnelName !== 'Belirlenmedi')
                    ? row.assignedPersonnelName
                    : '';
                const hasAssignedTechnician = !!assignedPersonnelId && !!assignedPersonnelName;
                const logAd = row.lastLogAciklama || '';
                const gidisTarihi = row.tarih
                    ? new Date(row.tarih).toLocaleDateString('tr-TR', { day: '2-digit', month: '2-digit', year: 'numeric' })
                    : '-';

                const durumAdNormalized = String(durumAd || '').toLocaleLowerCase('tr-TR');
                const logOnlyStatuses = [
                    'haber verecek',
                    'müşteri iptal etti',
                    'müşteriye ulaşılamadı',
                    'fiyatta anlaşılamadı',
                    'yerinde bakım yapıldı',
                    'atölyeye alındı',
                    'yarın gidilecek'
                ];
                const showLastLog = logOnlyStatuses.includes(durumAdNormalized);
                let lastLogText = '-';
                if (logAd) {
                    lastLogText = String(logAd)
                        .replace(/<br\s*\/?>/gi, ' ')
                        .replace(/<[^>]*>/g, '')
                        .replace(/^\s*-?\s*açıklama\s*:\s*/i, '')
                        .trim() || '-';
                }

                let teknisyenLine = '';
                if (hasAssignedTechnician) {
                    const personnelHtml = '<a href="/personeller/' + assignedPersonnelId + '/profil" class="teknisyen-profile-link text-decoration-none">' + assignedPersonnelName + '</a>';
                    teknisyenLine = '<small class="fs-11 fw-normal text-muted d-block durum-teknisyen">Teknisyen : ' + personnelHtml + '</small>';
                }

                if (showLastLog) {
                    return '<div class="fw-bold">' + durumAd + '</div>' +
                           '<small class="fs-11 fw-normal text-muted d-block durum-log">' + lastLogText + '</small>';
                }
                return '<div class="fw-bold">' + durumAd + '</div>' +
                       teknisyenLine +
                       '<small class="fs-11 fw-normal text-muted d-block">Gidiş Tarihi : ' + gidisTarihi + '</small>';
            }
        }
    ];
    if (canBulkServisDurum) {
        servisColumns.push({
            "data": null,
            "render": function (data, type, row) {
                return '<input type="checkbox" class="servis-select-checkbox" value="' + row.id + '">';
            }
        });
    }

    servisListDataTable = $('#servisListTable').DataTable({
        "processing": true, 
        "serverSide": true,
        "ajax": { 
            "url": "/servisler", 
            "type": "GET",
            "data": function (d) {
                delete d.il_id;
                delete d.ilce_id;
                delete d.personel_id;
                delete d.baslangic_tarih;
                delete d.bitis_tarih;
                delete d.servis_durum_id;
                delete d.personel_filter_type;
                delete d.marka_id;
                delete d.cihaz_tur_id;

                if (window.crmData && window.crmData.pendingOnly) {
                    d.pending_only = 1;
                }
                if (window.crmData && window.crmData.todayCancellationsOnly) {
                    d.today_cancellations_only = 1;
                }
                if (activeFilterType === 'bolge') {
                    d.il_id = getVisibleFilterValue('#bolgeSehir', '#bolgeSehirMobile') || '';
                    var bolgeBas = getVisibleFilterValue('#bolgeBaslangicTarih', '#bolgeBaslangicTarihMobile');
                    var bolgeBit = getVisibleFilterValue('#bolgeBitisTarih', '#bolgeBitisTarihMobile');
                    if (bolgeBas) { d.baslangic_tarih = bolgeBas; }
                    if (bolgeBit) { d.bitis_tarih = bolgeBit; }
                } else if (activeFilterType === 'operator') {
                    d.personel_id = getVisibleFilterValue('#operatorPersonel', '#operatorPersonelMobile') || '';
                    d.personel_filter_type = 'operator';
                    var opBas = getVisibleFilterValue('#operatorBaslangicTarih', '#operatorBaslangicTarihMobile');
                    var opBit = getVisibleFilterValue('#operatorBitisTarih', '#operatorBitisTarihMobile');
                    if (opBas) { d.baslangic_tarih = opBas; }
                    if (opBit) { d.bitis_tarih = opBit; }
                } else if (activeFilterType === 'teknisyen') {
                    d.personel_id = getVisibleFilterValue('#teknisyenPersonel', '#teknisyenPersonelMobile') || '';
                    d.personel_filter_type = 'teknisyen';
                    d.marka_id = getVisibleFilterValue('#teknisyenMarka', '#teknisyenMarkaMobile') || '';
                    d.cihaz_tur_id = getVisibleFilterValue('#teknisyenCihaz', '#teknisyenCihazMobile') || '';
                    d.il_id = getVisibleFilterValue('#teknisyenSehir', '#teknisyenSehirMobile') || '';
                    d.ilce_id = getVisibleFilterValue('#teknisyenIlce', '#teknisyenIlceMobile') || '';
                    var tekBas = getVisibleFilterValue('#teknisyenBaslangicTarih', '#teknisyenBaslangicTarihMobile');
                    var tekBit = getVisibleFilterValue('#teknisyenBitisTarih', '#teknisyenBitisTarihMobile');
                    if (tekBas) { d.baslangic_tarih = tekBas; }
                    if (tekBit) { d.bitis_tarih = tekBit; }
                } else if (activeFilterType === 'servisDurum') {
                    d.personel_id = getVisibleFilterValue('#servisDurumTeknisyen', '#servisDurumTeknisyenMobile') || '';
                    d.personel_filter_type = 'teknisyen';
                    d.servis_durum_id = getVisibleFilterValue('#servisDurumSelect', '#servisDurumSelectMobile') || '';
                    var sdBas = getVisibleFilterValue('#servisDurumBaslangicTarih', '#servisDurumBaslangicTarihMobile');
                    var sdBit = getVisibleFilterValue('#servisDurumBitisTarih', '#servisDurumBitisTarihMobile');
                    if (sdBas) { d.baslangic_tarih = sdBas; }
                    if (sdBit) { d.bitis_tarih = sdBit; }
                }
            },
            "dataSrc": "data" 
        },
        "columns": servisColumns,
        "paging": true, // Sayfalama DataTables tarafından yapılacak
        "pageLength": 50,
        "lengthChange": false,
        "info": true,
        "searching": true, // DataTables'ın kendi arama kutusu aktif
        "ordering": false, // Sütun başlıklarından sıralamayı kapat
        "language": {
            "url": "/crm_assets/i18n/tr.json", // Türkçe dil dosyası (lokal)
             "search": "Genel Arama:",
            "lengthMenu": "_MENU_ kayıt göster",
            "info": "_TOTAL_ kayıttan _START_ - _END_ arası gösteriliyor",
            "paginate": { "previous": "<", "next": ">" }
        },
        "autoWidth": false,
        "responsive": true,
        "dom":
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-12 col-md-5 ps-2'i>>" +
            "<'row'<'col-sm-12 d-flex justify-content-center'p>>" +
            "<'row'<'col-sm-12 d-flex justify-content-end mt-2'B>>",
        "drawCallback": function(settings){
            var api = this.api();
            var info = api.page.info();
            var $paginate = $('#servisListTable_wrapper').find('.dataTables_paginate');
            if (info.pages <= 1) { $paginate.hide(); } else { $paginate.show(); }
            if (canBulkServisDurum) {
                selectedServisIds.clear();
                $('#selectAllServisler').prop('checked', false);
                $('.bulk-servis-actions').addClass('d-none');
            }
        },
        "buttons": [
            {
                extend: 'print',
                text: '<i class="feather-printer me-1"></i> LİSTEYİ YAZDIR', // Metin Türkçe büyük harflerle güncellendi
                titleAttr: 'Listeyi Yazdır',
                className: 'btn btn-primary', // Daha belirgin bir buton stili
                exportOptions: {
                    columns: ':visible:not(.no-print)',
                    format: {
                        body: function (data, row, column, node) {
                            if (column === 4 && node) {
                                var durumText = $(node).find('div.fw-bold').first().text().trim();
                                return durumText || $(node).text().trim();
                            }
                            return data;
                        }
                    }
                },
                customize: function (win) {
                    // Print çıktısına özel CSS: font/padding küçült => tek sayfaya daha fazla kayıt
                    var $body = $(win.document.body);
                    $body.css('font-size', '10px');

                    // DataTables print tablosu
                    var $table = $body.find('table');
                    $table.css('font-size', '10px');
                    $table.find('th, td').css({
                        'font-size': '9px',
                        'padding': '2px 4px',
                        'line-height': '1.2'
                    });
                },
                init: function(api, node) {
                    if (window.matchMedia && window.matchMedia('(max-width: 575.98px)').matches) {
                        $(node).hide();
                    }
                }
            }
            // Diğer butonlar kaldırıldı
        ],
        "createdRow": function( row, data, dataIndex ) {
            // Her satıra tıklanabilirlik ve data-* attribute'larını ekle (modal için)
            $(row).addClass('clickable-row single-item').css('cursor', 'pointer');
            $(row).attr('data-servis-id', data.id);
            // Diğer data-* attribute'ları da eklenebilir, ancak modal zaten AJAX ile dolacak.
            // Şimdilik sadece servis-id yeterli.
        }
    });

    $('#servisGenelArama, #servisGenelAramaMobile').on('input', function () {
        if (!servisListDataTable) return;
        var val = $(this).val() || '';
        var isHariciOperator = window.crmData && String(window.crmData.loggedInUserPozId) === '1076';
        if (isHariciOperator && val.trim().length > 0 && val.trim().length < 7) {
            return;
        }
        servisListDataTable.search(val).draw();
    });


    // Yeni "Bölge Servis Ara" butonu için click listener
    $('#bolgeServisAraBtn, #bolgeServisAraBtnMobile').on('click', function() {
        activeFilterType = 'bolge';
        // Filtre değişince 1. sayfaya dön; aksi halde start yüksek kalıp boş liste görünür
        servisListDataTable.ajax.reload(null, true);
    });

    // YENİ: "Operatör Servis Ara" butonu için click listener
    $('#operatorServisAraBtn, #operatorServisAraBtnMobile').on('click', function() {
        activeFilterType = 'operator';
        servisListDataTable.ajax.reload(null, true);
    });

    $(document).on('click', '#operatorComparisonLink, #operatorComparisonLinkMobile', function(e) {
        e.preventDefault();
        var isMobile = this.id === 'operatorComparisonLinkMobile';
        var baseHref = $(this).data('base-href') || $(this).attr('href');
        var dateFrom = isMobile ? $('#operatorBaslangicTarihMobile').val() : $('#operatorBaslangicTarih').val();
        var dateTo = isMobile ? $('#operatorBitisTarihMobile').val() : $('#operatorBitisTarih').val();

        sessionStorage.setItem('operatorComparisonDateFrom', dateFrom || '');
        sessionStorage.setItem('operatorComparisonDateTo', dateTo || '');

        window.location.href = baseHref;
    });

    // YENİ: "Teknisyen Servis Ara" butonu için click listener
    $('#teknisyenServisAraBtn, #teknisyenServisAraBtnMobile').on('click', function() {
        activeFilterType = 'teknisyen';
        servisListDataTable.ajax.reload(null, true);
    });

    // Teknisyen filtresi: Şehir → İlçe yükleme (/ilceler/{il_id})
    function loadTeknisyenIlceler($sehirSelect, $ilceSelect) {
        var selectedIlId = $sehirSelect.val();
        $ilceSelect.prop('disabled', true).html('<option selected value="">Yükleniyor...</option>');
        if (!selectedIlId) {
            $ilceSelect.html('<option selected value="">Önce Şehir Seçiniz</option>');
            return;
        }
        $.ajax({
            url: '/ilceler/' + selectedIlId,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                $ilceSelect.empty().append('<option selected value="">Tüm İlçeler</option>');
                if (data && data.length > 0) {
                    $.each(data, function(key, ilce) {
                        $ilceSelect.append('<option value="' + ilce.id + '">' + ilce.ad + '</option>');
                    });
                    $ilceSelect.prop('disabled', false);
                } else {
                    $ilceSelect.append('<option value="" disabled>İlçe bulunamadı</option>');
                }
            },
            error: function() {
                $ilceSelect.empty().append('<option selected value="">İlçe yüklenemedi</option>');
            }
        });
    }

    $('#teknisyenSehir').on('change', function() {
        loadTeknisyenIlceler($(this), $('#teknisyenIlce'));
    });
    $('#teknisyenSehirMobile').on('change', function() {
        loadTeknisyenIlceler($(this), $('#teknisyenIlceMobile'));
    });

    function runServisDurumFilter(){
        activeFilterType = 'servisDurum';
        if (servisListDataTable && servisListDataTable.ajax) {
            servisListDataTable.ajax.reload(null, true);
        } else if ($.fn.DataTable && $.fn.DataTable.isDataTable && $.fn.DataTable.isDataTable('#servisListTable')) {
            $('#servisListTable').DataTable().ajax.reload(null, true);
        }
    }
    window._servisDurumAra = runServisDurumFilter;
    // YENİ: "Servis Durum Ara" butonu için click listener (delegated)
    $(document).on('click', '#servisDurumAraBtn, #servisDurumAraBtnMobile', function(e) {
        e.preventDefault();
        e.stopPropagation();
        runServisDurumFilter();
    });


    // Satır tıklama olayı (Bu genel olarak kalabilir, modalı açar)
    $('#servisListTable tbody').on('click', 'tr.clickable-row', function(e) {
        if ($(e.target).closest('.teknisyen-profile-link').length) {
            return;
        }
        if ($(e.target).closest('.servis-select-checkbox').length) {
            return;
        }
        mevcutServisId = $(this).data('servis-id');
        console.log('Satır tıklandı, Servis ID:', mevcutServisId);

        // Tıklanan satırı aktif yap
        $('tr.clickable-row').removeClass('active');
        $(this).addClass('active');

        // Detay Modal'ını aç (sadece global API ile)
        if (window.openServisDetay) {
            window.openServisDetay(mevcutServisId);
        }
    });

    if (canBulkServisDurum) {
        function updateBulkServisActions() {
            var hasSelection = selectedServisIds.size > 0;
            $('.bulk-servis-actions').toggleClass('d-none', !hasSelection);
        }

        $(document).on('change', '#selectAllServisler', function() {
            var isChecked = $(this).prop('checked');
            $('#servisListTable tbody .servis-select-checkbox').each(function() {
                $(this).prop('checked', isChecked).trigger('change');
            });
        });

        $(document).on('change', '#servisListTable tbody .servis-select-checkbox', function() {
            var id = $(this).val();
            if ($(this).prop('checked')) {
                selectedServisIds.add(id);
            } else {
                selectedServisIds.delete(id);
                $('#selectAllServisler').prop('checked', false);
            }
            updateBulkServisActions();
        });

        $(document).on('change', '.bulk-servis-durum-select', function() {
            var val = $(this).val();
            $('.bulk-servis-durum-select').val(val);
        });

        $(document).on('click', '.bulk-servis-durum-btn', function() {
            var durumId = $('.bulk-servis-durum-select').first().val();
            if (!durumId) {
                Swal && Swal.fire('Uyarı!', 'Lütfen bir servis durumu seçin.', 'warning');
                return;
            }
            if (!selectedServisIds.size) {
                Swal && Swal.fire('Uyarı!', 'Lütfen en az bir kayıt seçin.', 'warning');
                return;
            }
            var ids = Array.from(selectedServisIds).map(function(id){ return parseInt(id, 10); }).filter(Boolean);
            if (durumId === '__delete__') {
                Swal && Swal.fire({
                    title: 'Emin misiniz?',
                    text: ids.length + ' kayıt silinecek.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Evet, sil',
                    cancelButtonText: 'Vazgeç'
                }).then(function(result){
                    if (!(result.isConfirmed || result.value)) return;
                    $.ajax({
                        url: '/servisler/bulk-soft-delete',
                        type: 'POST',
                        data: { _token: $('meta[name="csrf-token"]').attr('content'), servis_ids: ids },
                        dataType: 'json'
                    }).done(function(response){
                        if (response && response.success) {
                            Swal && Swal.fire('Başarılı', response.message || 'Silindi.', 'success');
                            selectedServisIds.clear();
                            $('#selectAllServisler').prop('checked', false);
                            $('.bulk-servis-actions').addClass('d-none');
                            if (servisListDataTable && servisListDataTable.ajax) {
                                servisListDataTable.ajax.reload(null, false);
                            }
                        } else {
                            Swal && Swal.fire('Hata', response.message || 'Silme başarısız.', 'error');
                        }
                    }).fail(function(xhr){
                        var msg = 'Silme sırasında hata oluştu.';
                        if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                        Swal && Swal.fire('Hata', msg, 'error');
                    });
                });
                return;
            }
            var requestData = {
                _token: $('meta[name="csrf-token"]').attr('content'),
                servis_ids: ids,
                servis_durum_id: durumId
            };
            Swal && Swal.fire({
                title: 'Emin misiniz?',
                text: ids.length + ' kaydın durumu güncellenecek.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Evet, güncelle',
                cancelButtonText: 'Vazgeç'
            }).then(function(result){
                if (!(result.isConfirmed || result.value)) return;
                $.ajax({
                    url: '/servisler/bulk-durum',
                    type: 'POST',
                    data: requestData,
                    dataType: 'json'
                }).done(function(response){
                    if (response && response.success) {
                        Swal && Swal.fire('Başarılı', response.message || 'Güncellendi.', 'success');
                        selectedServisIds.clear();
                        $('#selectAllServisler').prop('checked', false);
                        $('.bulk-servis-actions').addClass('d-none');
                        if (servisListDataTable && servisListDataTable.ajax) {
                            servisListDataTable.ajax.reload(null, false);
                        }
                    } else {
                        Swal && Swal.fire('Hata', response.message || 'Güncelleme başarısız.', 'error');
                    }
                }).fail(function(xhr){
                    var msg = 'Güncelleme sırasında hata oluştu.';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    Swal && Swal.fire('Hata', msg, 'error');
                });
            });
        });
    }

    // Teknisyen adına tıklanınca modal açılmasın
    $(document).on('click', '.teknisyen-profile-link', function(e) {
        e.stopPropagation();
    });


    let duzenlemeModu = false;



    // --- Yeni Servis Modalı Değişkenleri ---
    const yeniServisModalElement = document.getElementById('yeniServisModal');
    const yeniServisModal = yeniServisModalElement ? new bootstrap.Modal(yeniServisModalElement) : null;
    var yeniServisKeepAliveIntervalId = null;
    var yeniServisOturumUyariTimeoutId = null;
    var YENI_SERVIS_KEEPALIVE_INTERVAL_MS = 5 * 60 * 1000; // 5 dakikada bir oturum yenile
    var YENI_SERVIS_UYARI_DK_ONCE = 15; // Oturum bitiminden 15 dk önce uyarı göster
    const yeniServisForm = $('#yeniServisForm');
    const yeniServisHataMesajlariDiv = $('#yeniServisHataMesajlari');
    const musteriSecimRadios = $('#yeniServisModal input[name="musteri_secim_modu"]');
    const varolanMusteriAlani = $('#varolanMusteriAlani');
    const varolanMusteriSelect = $('#modal_musteri_id'); // gizli input (seçilen müşteri id)
    const varolanMusteriArama = $('#modal_musteri_arama');
    const varolanMusteriSonuclar = $('#modal_musteri_sonuclar');
    const varolanMusteriSecili = $('#modal_musteri_secili');
    const yeniMusteriAlanlariDiv = $('#yeniMusteriAlanlari');
    const yeniMusteriRequiredInputs = $('#yeni_ad, #yeni_tel1, #yeni_il_id, #yeni_ilce_id, #yeni_adres, #yeni_kayit_tarihi, #yeni_kayit_saati');
    const yeniMusteriTipiRadios = $('#yeniMusteriAlanlari input[name="yeni_musteri_tip"]');
    const yeniVergiAlanlariDiv = $('#yeniMusteriAlanlari .yeniVergiAlanlari');
    const yeniVergiDairesiInput = $('#yeni_vdaire');
    const yeniVergiNoInput = $('#yeni_vno');
    const yeniIlSelect = $('#yeni_il_id');
    const yeniIlceSelect = $('#yeni_ilce_id');
    let musteriAramaTimer = null;
    let musteriAramaXhr = null;

    function resetVarolanMusteriSecimi() {
        varolanMusteriSelect.val('');
        varolanMusteriArama.val('').prop('required', false).removeClass('is-invalid');
        varolanMusteriSonuclar.addClass('d-none').empty();
        varolanMusteriSecili.addClass('d-none').text('');
    }

    function setVarolanMusteriSecimi(id, label) {
        varolanMusteriSelect.val(id);
        varolanMusteriArama.val(label).removeClass('is-invalid');
        varolanMusteriSecili.removeClass('d-none').text('Seçildi: ' + label);
        varolanMusteriSonuclar.addClass('d-none').empty();
    }

    function renderMusteriSonuclari(results) {
        varolanMusteriSonuclar.empty();
        if (!results || !results.length) {
            varolanMusteriSonuclar.append(
                '<div class="list-group-item text-muted">Sonuç bulunamadı</div>'
            );
            varolanMusteriSonuclar.removeClass('d-none');
            return;
        }
        results.forEach(function (item) {
            var btn = $('<button type="button" class="list-group-item list-group-item-action"></button>');
            btn.text(item.label || ((item.ad || '') + (item.tel ? ' (' + item.tel + ')' : '')));
            btn.attr('data-id', item.id);
            btn.attr('data-label', item.label || item.ad || '');
            varolanMusteriSonuclar.append(btn);
        });
        varolanMusteriSonuclar.removeClass('d-none');
    }

    function araVarolanMusteri(q) {
        var url = varolanMusteriAlani.data('musteri-search-url');
        if (!url) {
            return;
        }
        if (musteriAramaXhr && musteriAramaXhr.readyState !== 4) {
            musteriAramaXhr.abort();
        }
        musteriAramaXhr = $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            data: { q: q, limit: 20 },
            success: function (data) {
                renderMusteriSonuclari((data && data.results) ? data.results : []);
            },
            error: function (jqXHR, textStatus) {
                if (textStatus === 'abort') {
                    return;
                }
                varolanMusteriSonuclar
                    .empty()
                    .append('<div class="list-group-item text-danger">Arama sırasında hata oluştu</div>')
                    .removeClass('d-none');
            }
        });
    }

    if (varolanMusteriArama.length) {
        varolanMusteriArama.on('input', function () {
            varolanMusteriSelect.val('');
            varolanMusteriSecili.addClass('d-none').text('');
            varolanMusteriArama.removeClass('is-invalid');
            var q = $.trim($(this).val());
            if (musteriAramaTimer) {
                clearTimeout(musteriAramaTimer);
            }
            if (q.length < 2) {
                varolanMusteriSonuclar.addClass('d-none').empty();
                return;
            }
            musteriAramaTimer = setTimeout(function () {
                araVarolanMusteri(q);
            }, 300);
        });

        varolanMusteriSonuclar.on('click', 'button.list-group-item-action', function () {
            setVarolanMusteriSecimi($(this).data('id'), $(this).data('label'));
        });

        $(document).on('click', function (e) {
            if (!$(e.target).closest('#varolanMusteriAlani').length) {
                varolanMusteriSonuclar.addClass('d-none');
            }
        });
    }

    // === Yeni Servis Modalı: Müşteri Seçim Modu Değişikliği ===
    if(musteriSecimRadios.length) {
        musteriSecimRadios.on('change', function() {
            if ($(this).val() === 'yeni') {
                varolanMusteriAlani.slideUp();
                varolanMusteriSelect.prop('required', false);
                varolanMusteriArama.prop('required', false);
                resetVarolanMusteriSecimi();
                yeniMusteriAlanlariDiv.slideDown();
                yeniMusteriRequiredInputs.prop('required', true);
                toggleYeniMusteriVergiAlanlari(); 
            } else { // varolan
                yeniMusteriAlanlariDiv.slideUp();
                yeniMusteriRequiredInputs.prop('required', false);
                varolanMusteriAlani.slideDown();
                varolanMusteriSelect.prop('required', true);
                varolanMusteriArama.prop('required', true);
                yeniVergiAlanlariDiv.hide(); 
                yeniVergiDairesiInput.prop('required', false);
                yeniVergiNoInput.prop('required', false);
            }
        });
    }

    // === Yeni Servis Modalı: Yeni Müşteri Tipi Seçimi ===
    function toggleYeniMusteriVergiAlanlari() {
        if ($('#yeniMusteriAlanlari input[name="yeni_musteri_tip"]:checked').val() === '1') {
            yeniVergiAlanlariDiv.slideDown();
            yeniVergiDairesiInput.prop('required', true);
            yeniVergiNoInput.prop('required', true);
        } else {
            yeniVergiAlanlariDiv.slideUp();
            yeniVergiDairesiInput.prop('required', false);
            yeniVergiNoInput.prop('required', false);
        }
    }
    if(yeniMusteriTipiRadios.length) {
        yeniMusteriTipiRadios.on('change', toggleYeniMusteriVergiAlanlari);
    }

     // === Yeni Servis Modalı: İl Seçimine Göre İlçeleri Getir ===
    if(yeniIlSelect.length) {
        yeniIlSelect.on('change', function() {
            const selectedIlId = $(this).val();
            yeniIlceSelect.prop('disabled', true).html('<option value="" selected disabled>Yükleniyor...</option>');
            if (selectedIlId) {
                $.ajax({
                    url: '/ilceler/' + selectedIlId, 
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        yeniIlceSelect.empty().append('<option value="" selected disabled>İlçe Seçiniz...</option>');
                        if (data && data.length > 0) {
                            $.each(data, function(key, ilce) {
                                yeniIlceSelect.append('<option value="' + ilce.id + '">' + ilce.ad + '</option>');
                            });
                            yeniIlceSelect.prop('disabled', false);
                            console.log("Yeni servis modalı - İlçeler yüklendi.");
                        } else {
                            yeniIlceSelect.append('<option value="" disabled>İlçe bulunamadı</option>');
                            console.warn("Yeni servis modalı - İlçe bulunamadı veya gelmedi.");
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error("Yeni Servis Modal - İlçe getirme hatası:", jqXHR.status, jqXHR.responseText, errorThrown);
                        yeniIlceSelect.empty().append('<option value="" selected disabled>Hata oluştu!</option>');
                    }
                });
            } else {
                yeniIlceSelect.empty().append('<option value="" selected disabled>Önce İl Seçiniz...</option>');
            }
        });
    }
    
    // === Yeni Servis Modalı: Telefon Formatlama ===
    $('#yeni_tel1, #yeni_tel2').on('input', function() {
        formatPhoneNumber(this); 
    });

    // === Yeni Servis Modalı: Oturum canlı tutma (keep-alive) ve süre uyarısı ===
    function yeniServisKeepAliveTick() {
        var url = yeniServisModalElement.getAttribute('data-keepalive-url');
        if (!url) return;
        fetch(url, { method: 'GET', credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
            .then(function (r) { if (!r.ok) { console.warn('Session keepalive failed:', r.status); } })
            .catch(function () {});
    }
    function yeniServisOturumUyarisiGoster() {
        var el = document.getElementById('yeniServisOturumUyarisi');
        if (el) {
            el.classList.remove('d-none');
        }
    }
    function yeniServisOturumUyarisiTemizle() {
        if (yeniServisKeepAliveIntervalId !== null) {
            clearInterval(yeniServisKeepAliveIntervalId);
            yeniServisKeepAliveIntervalId = null;
        }
        if (yeniServisOturumUyariTimeoutId !== null) {
            clearTimeout(yeniServisOturumUyariTimeoutId);
            yeniServisOturumUyariTimeoutId = null;
        }
        var el = document.getElementById('yeniServisOturumUyarisi');
        if (el) {
            el.classList.add('d-none');
        }
    }

    // === Yeni Servis Modalı Açıldığında Formu Sıfırla + Keep-alive ve uyarı başlat ===
    if(yeniServisModalElement) {
        $(yeniServisModalElement).on('show.bs.modal', function () {
            console.log("Yeni servis modalı açılıyor, form sıfırlanıyor."); // Debug
            yeniServisForm[0].reset(); 
            $('#modal_operator_not').val(''); // Eklendi: Operatör Notu alanını sıfırla
            
            $('#secimYeni').prop('checked', true); // Varsayılan olarak YENİ MÜŞTERİ seçili
            resetVarolanMusteriSecimi();
            varolanMusteriAlani.hide();
            varolanMusteriSelect.prop('required', false);
            varolanMusteriArama.prop('required', false);
            yeniMusteriAlanlariDiv.show();
            yeniMusteriRequiredInputs.prop('required', true);
            toggleYeniMusteriVergiAlanlari(); // Yeni müşteri tipine göre vergi alanlarını ayarla

            yeniIlceSelect.html('<option value="" selected disabled>Önce İl Seçiniz...</option>').prop('disabled', true);
            yeniServisHataMesajlariDiv.addClass('d-none').html(''); 
            yeniServisForm.find('.is-invalid').removeClass('is-invalid');

            yeniServisOturumUyarisiTemizle();
            var sessionMinutes = parseInt(yeniServisModalElement.getAttribute('data-session-lifetime-minutes') || '120', 10);
            yeniServisKeepAliveIntervalId = setInterval(yeniServisKeepAliveTick, YENI_SERVIS_KEEPALIVE_INTERVAL_MS);
            var uyariDelayMs = Math.max(60000, (sessionMinutes - YENI_SERVIS_UYARI_DK_ONCE) * 60 * 1000);
            yeniServisOturumUyariTimeoutId = setTimeout(yeniServisOturumUyarisiGoster, uyariDelayMs);
        });
        $(yeniServisModalElement).on('hidden.bs.modal', function () {
            yeniServisOturumUyarisiTemizle();
        });
    }

    // === Yeni Servis Formunu AJAX ile Gönderme ===
    if(yeniServisForm.length) {
        yeniServisForm.on('submit', function(e) {
            e.preventDefault();
            yeniServisHataMesajlariDiv.addClass('d-none').html('');
            yeniServisForm.find('.is-invalid').removeClass('is-invalid');
            
            // Hangi modun seçili olduğunu belirle ve form verisine ekle (gizli input yerine)
            var musteriModu = $('input[name="musteri_secim_modu"]:checked').val();
            if (musteriModu === 'varolan' && !$.trim(varolanMusteriSelect.val())) {
                varolanMusteriArama.addClass('is-invalid').focus();
                yeniServisHataMesajlariDiv
                    .html('<ul><li>Lütfen listeden bir müşteri seçin.</li></ul>')
                    .removeClass('d-none');
                return;
            }
            var formData = $(this).serializeArray(); // Dizi olarak al
            formData.push({ name: "musteri_modu", value: musteriModu }); // Modu ekle

            var submitButton = $(this).find('button[type="submit"]');
            var originalButtonText = submitButton.html();

            submitButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Kaydediliyor...');

            $.ajax({
                url: $(this).attr('action'), 
                method: $(this).attr('method'), 
                data: $.param(formData), // Diziyi query string'e çevir
                success: function(response) {
                    yeniServisModal.hide();
                    Swal.fire('Başarılı!', response.message || 'Servis başarıyla kaydedildi.', 'success')
                    .then(() => {
                        location.reload(); 
                    });
                },
                error: function(jqXHR) {
                    let errorMsg = 'Servis kaydedilirken bir sunucu hatası oluştu.'; // Genel hata
                     if (jqXHR.status === 422) { // Validasyon hatası
                        var errors = jqXHR.responseJSON.errors;
                        var errorHtml = '<ul>';
                        $.each(errors, function(key, value) {
                            errorHtml += '<li>' + value[0] + '</li>';
                            // Hatalı alanı işaretle (Hem normal hem yeni müşteri alanları için)
                            if (musteriModu === 'varolan' && key === 'musteri_id') {
                                $('#modal_musteri_arama', yeniServisForm).addClass('is-invalid');
                                return;
                            }
                            let inputName = key;
                             if (musteriModu === 'yeni' && !key.startsWith('yeni_') && ['musteri_tip', 'ad', 'tel1', 'tel2', 'il_id', 'ilce_id', 'adres', 'vdaire', 'vno'].includes(key)) {
                                // Backend validasyonu yeni müşteri alan adlarını kullanmıyorsa, prefix ekle
                                //inputName = 'yeni_' + key; // Bu yaklaşım backend'e bağlı
                             }
                             // Servis alanları için modal prefix'i gerekebilir (örn: #modal_mark-id)
                             let selector = `[name="${inputName}"]`;
                             if (!$(selector, yeniServisForm).length) { // Eğer direkt name ile bulunamazsa, modal ID'si ile dene
                                  selector = `#${inputName.startsWith('yeni_') ? '' : 'modal_'}${key}`;
                             }
                            $(selector, yeniServisForm).addClass('is-invalid');
                        });
                        errorHtml += '</ul>';
                        errorMsg = errorHtml; // Hata mesajını validasyon listesi yap
                        yeniServisHataMesajlariDiv.html(errorMsg).removeClass('d-none');
                    } else {
                        // Diğer sunucu hataları
                        console.error("Yeni Servis Form gönderme hatası:", jqXHR.responseText);
                         if(jqXHR.responseJSON && jqXHR.responseJSON.message){
                             errorMsg = jqXHR.responseJSON.message;
                         }
                         Swal.fire('Hata!', errorMsg, 'error');
                         yeniServisHataMesajlariDiv.html('<li>' + errorMsg + '</li>').removeClass('d-none');
                    }
                },
                complete: function() {
                     submitButton.prop('disabled', false).html(originalButtonText);
                }
            });
        });
    }

    // Servis listesi için global reload (filtreleri korur)
    window._servisListReload = function() {
        if (servisListDataTable && servisListDataTable.ajax) {
            servisListDataTable.ajax.reload(null, false);
        }
    };


    function resizeCanvas(canvas) {
        const wrapper = canvas.parentElement;
        const ratio =  Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = wrapper.offsetWidth * ratio;
        canvas.height = wrapper.offsetHeight * ratio;
        canvas.getContext("2d").scale(ratio, ratio);
    }



    function loadPreviousServiceSlips(servisId) {
        const listElement = $('#eskiServisFisleriListesi');
        listElement.html('<p class="text-muted text-center">Fişler yükleniyor...</p>');

        $.ajax({
            url: `/servisler/${servisId}/fisleri-listele`,
            type: 'GET',
            dataType: 'json',
            success: function(fisler) {
                if (fisler && fisler.length > 0) {
                    let fislerHtml = '';
                    fisler.forEach(function(fis) {
                        // created_at olmadığı için tarih ve saat sütunlarını kullan
                        const olusturmaTarihi = new Date(fis.tarih + 'T' + fis.saat).toLocaleString('tr-TR', { day: '2-digit', month: '2-digit', year: 'numeric', hour:'2-digit', minute:'2-digit' });
                        const dosyaAdi = fis.pdf ? fis.pdf.split('/').pop() : 'Fiş Adı Yok'; // fis.pdf_dosya_yolu -> fis.pdf
                        fislerHtml += `<a href="/servisler/fis/${fis.id}/goster" target="_blank" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">`;
                        fislerHtml += `<span><i class="feather feather-file-text me-2"></i>${dosyaAdi}</span>`;
                        fislerHtml += `<small class="text-muted">${olusturmaTarihi}</small>`;
                        fislerHtml += `</a>`;
                    });
                    listElement.html(fislerHtml);
                } else {
                    listElement.html('<p class="text-muted text-center">Bu servise ait daha önce oluşturulmuş fiş bulunmamaktadır.</p>');
                }
            },
            error: function() {
                listElement.html('<p class="text-danger text-center">Daha önceki fişler yüklenirken bir hata oluştu.</p>');
            }
        });
    }



}); // $(document).ready sonu



let tumOdemeSekilleriCache = []; // AJAX'tan gelen ödeme şekillerini saklamak için
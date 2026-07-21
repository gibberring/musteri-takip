// === Helper Fonksiyonlar ===
// Bu fonksiyonlar artık helpers.js dosyasından yüklenecek.
/*
function ucwordsJs(str) { ... }
function ucfirstJs(str) { ... }
function in_array_js(needle, haystack) { ... }
*/
// === Helper Fonksiyonlar Sonu ===

$(document).ready(function() {
    // === Genel Ayarlar ve Değişkenler ===
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    const yeniMusteriModalEl = document.getElementById('yeniMusteriModal');
    const yeniMusteriModal = (window.bootstrap && yeniMusteriModalEl)
        ? new bootstrap.Modal(yeniMusteriModalEl)
        : null;
    const yeniMusteriForm = $('#yeniMusteriForm');
    const modalIlSelect = $('#modal_il_id');
    const modalIlceSelect = $('#modal_ilce_id');
    const modalMusteriTipiRadios = $('input[name="musteri_tip"]'); // Modal içindeki radio butonlar
    const modalVergiAlanlariDiv = $('#yeniMusteriModal #vergiAlanlari'); // Modal içindeki vergi alanı
    const modalVergiDairesiInput = $('#modal_vdaire');
    const modalVergiNoInput = $('#modal_vno');
    const hataMesajlariDiv = $('#musteriHataMesajlari');
    const fallbackUcfirst = function(text) {
        if (!text) return '';
        return text.charAt(0).toUpperCase() + text.slice(1);
    };

    let musteriListTable = null;
    function initMusteriTable() {
        if (musteriListTable || !$.fn.DataTable) return;
        const $table = $('#musteriListTable');
        if (!$table.length) return;

        musteriListTable = $table.DataTable({
             "processing": true,
             "serverSide": true,
             "ajax": {
                 "url": "/musteriler",
                 "type": "GET",
                 "data": function(d) {
                     d.search = d.search || {};
                     d.search.value = $('#musteriSearchInput').val() || '';
                     d.search.regex = false;
                     d.q = $('#musteriSearchInput').val() || '';
                 },
                 "dataSrc": "data"
             },
             "columns": [
                 {
                     "data": "id",
                     "render": function (data, type, row) {
                         return '<a href="#" class="fw-bold">#' + row.id + '</a>';
                     }
                 },
                 {
                     "data": "ad",
                     "render": function (data, type, row) {
                         return row.ad || 'N/A';
                     }
                 },
                 {
                     "data": null,
                     "render": function (data, type, row) {
                         const tel1 = row.tel1 || '';
                         const tel2 = row.tel2 ? ' / ' + row.tel2 : '';
                         return tel1 + tel2;
                     }
                 },
                 {
                     "data": null,
                     "render": function (data, type, row) {
                         const toUcfirst = (typeof ucfirstJs === 'function') ? ucfirstJs : fallbackUcfirst;
                         const adres = row.adres ? (toUcfirst(row.adres)) : '';
                         let detay = '';
                         if (row.ilce && row.ilce.ad) {
                             const ilAd = row.ilce.il ? row.ilce.il.ad : '';
                             detay = '<small class="d-block text-muted">' + row.ilce.ad + ' / ' + ilAd + '</small>';
                         }
                         return adres + detay;
                     }
                 },
                 {
                     "data": "musteri_tip",
                     "render": function (data, type, row) {
                         if (String(row.musteri_tip) === '1') {
                             return '<span class="badge bg-info">Kurumsal</span>';
                         }
                         return '<span class="badge bg-secondary">Bireysel</span>';
                     }
                 },
                 {
                     "data": "created_at",
                     "render": function (data, type, row) {
                         if (!row.created_at) return '-';
                         try {
                             const tarihObj = new Date(row.created_at);
                             return tarihObj.toLocaleDateString('tr-TR', { day: '2-digit', month: '2-digit', year: 'numeric' });
                         } catch (e) {
                             return row.created_at;
                         }
                     }
                 },
                 {
                     "data": null,
                     "orderable": false,
                     "render": function () {
                         return '<div class="hstack gap-2 justify-content-end">' +
                             '<a href="#" class="avatar-text avatar-md musteri-detay-goruntule-btn">' +
                             '<i class="feather feather-eye"></i></a></div>';
                     }
                 }
             ],
             "paging": true,
             "pageLength": 20,
             "lengthChange": false,
             "info": false,
             "searching": true,
            "language": {
               "url": "/crm_assets/i18n/tr.json",
                "search": "Ara:",
                "lengthMenu": "_MENU_ kayıt göster",
                "info": "_TOTAL_ kayıttan _START_ - _END_ arası gösteriliyor",
                "paginate": { "previous": "Önceki Sayfa", "next": "Sonraki Sayfa" }
             },
             "autoWidth": false,
             "responsive": true,
             "dom": 'rt<"row"<"col-12 d-flex justify-content-center"p>>',
             "createdRow": function(row, data) {
                $(row)
                  .addClass('single-item clickable-row')
                  .attr('data-musteri-id', data.id)
                  .attr('data-bs-toggle', 'modal')
                  .attr('data-bs-target', '#musteriDetayModal')
                  .css('cursor', 'pointer');
             },
             "drawCallback": function(settings){
                const api = this.api();
                const info = api.page.info();
                const $paginate = $('#musteriListTable_wrapper').find('.dataTables_paginate');
                if (info.pages <= 1) { $paginate.hide(); } else { $paginate.show(); }
             }
        });
    }

    initMusteriTable();

    $(document).on('input', '#musteriSearchInput', function() {
        clearTimeout(window.__musteriSearchTimer);
        window.__musteriSearchTimer = setTimeout(function() {
            if (!musteriListTable) {
                initMusteriTable();
            }
            if (musteriListTable && musteriListTable.ajax) {
                musteriListTable.ajax.reload(null, false);
            }
        }, 300);
    });

    let mevcutMusteriId = null; // Detay modalı için müşteri ID'sini tutacak değişken
    let musteriDuzenlemeModu = false; // Müşteri düzenleme modu için flag
    let originalMusteriData = {}; // Orijinal veriyi saklamak için

    // === Modal Açıldığında Formu Sıfırla ===
    $('#yeniMusteriModal').on('show.bs.modal', function () {
        yeniMusteriForm[0].reset(); // Formu sıfırla
        modalIlceSelect.html('<option value="" selected disabled>Önce İl Seçiniz...</option>').prop('disabled', true);
        modalVergiAlanlariDiv.hide(); // Vergi alanlarını gizle
        modalVergiDairesiInput.prop('required', false);
        modalVergiNoInput.prop('required', false);
        $('#modalTipBireysel').prop('checked', true); // Bireysel'i varsayılan yap
        hataMesajlariDiv.addClass('d-none').html(''); // Hata mesajlarını temizle
        yeniMusteriForm.find('.is-invalid').removeClass('is-invalid'); // Geçersiz alan işaretlerini kaldır
        $('#modal_kayit_tarihi').val(new Date().toISOString().split('T')[0]); // Tarihi bugüne ayarla
        $('#modal_kayit_saati').val(new Date().toTimeString().split(' ')[0].substring(0, 5)); // Saati şu ana ayarla
    });

    // === Müşteri Tipi Seçimine Göre Vergi Alanları ===
    function toggleModalVergiAlanlari() {
        if ($('#yeniMusteriModal input[name="musteri_tip"]:checked').val() === '1') { 
            modalVergiAlanlariDiv.slideDown(); 
            modalVergiDairesiInput.prop('required', true);
            modalVergiNoInput.prop('required', true);
        } else { 
            modalVergiAlanlariDiv.slideUp(); 
            modalVergiDairesiInput.prop('required', false);
            modalVergiNoInput.prop('required', false);
        }
    }
    // Radio buton değiştiğinde kontrol et (Modal içindekiler)
    $('#yeniMusteriModal input[name="musteri_tip"]').on('change', toggleModalVergiAlanlari);

    // === İl Seçimine Göre İlçeleri Getir (Modal içinde) ===
    modalIlSelect.on('change', function() {
        const selectedIlId = $(this).val();
        modalIlceSelect.prop('disabled', true).html('<option value="" selected disabled>Yükleniyor...</option>');

        if (selectedIlId) {
            $.ajax({
                url: '/ilceler/' + selectedIlId,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    modalIlceSelect.empty().append('<option value="" selected disabled>İlçe Seçiniz...</option>');
                    if (data && data.length > 0) {
                        $.each(data, function(key, ilce) {
                            modalIlceSelect.append('<option value="' + ilce.id + '">' + ilce.ad + '</option>');
                        });
                        modalIlceSelect.prop('disabled', false);
                    } else {
                        modalIlceSelect.append('<option value="" disabled>İlçe bulunamadı</option>');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error("Modal İlçe getirme hatası:", textStatus, errorThrown);
                    modalIlceSelect.empty().append('<option value="" selected disabled>Hata oluştu</option>');
                }
            });
        } else {
            modalIlceSelect.empty().append('<option value="" selected disabled>Önce İl Seçiniz...</option>');
        }
    });

    // === Müşteri Satırına Tıklama ===
    $('#musteriListTable tbody').on('click', 'tr.clickable-row', function(event) {
        // Tıklanan satırdan musteri-id'yi alıp mevcutMusteriId'ye ata.
        // show.bs.modal olayı bazen relatedTarget'ı doğru yakalayamayabilir,
        // bu yüzden burada ID'yi yakalamak daha güvenli olabilir.
        mevcutMusteriId = $(this).data('musteri-id');
        console.log('Müşteri satırı tıklandı (ID güncellendi):', mevcutMusteriId);
        
        // Modalın Bootstrap data-* özellikleri tarafından açılmasına izin veriyoruz.
        // Bu yüzden aşağıdaki satırı yoruma alıyoruz veya siliyoruz:
        // const musteriDetayModalInstance = new bootstrap.Modal(document.getElementById('musteriDetayModal'));
        // musteriDetayModalInstance.show(); 
    });
    
    // === Müşteri Detay Modalı Açıldığında Verileri Yükle ===
    $('#musteriDetayModal').on('show.bs.modal', function(event) {
        musteriDuzenlemeModu = false;
        $('#modalFooterNormalMusteri').show(); // ID güncellendi
        $('#modalFooterEditMusteri').hide(); 
        
        const modal = $(this);
        const modalBody = modal.find('#musteriDetayContent');
        const modalTitleId = modal.find('#detayMusteriId');
        modalBody.html('<p class="text-center">Yükleniyor...</p>');
        modalTitleId.text('...');

        const button = $(event.relatedTarget); 
        let musteriId = button.data('musteri-id'); 
        if (!musteriId && mevcutMusteriId) {
            musteriId = mevcutMusteriId;
        }
        
        console.log("Müşteri detay modalı açılıyor, ID:", musteriId);

        if (!musteriId) {
            console.error('Müşteri ID bulunamadı!');
            modalBody.html('<p class="text-center text-danger">Müşteri ID bulunamadı!</p>');
            return;
        }
        mevcutMusteriId = musteriId; // Güncel ID'yi sakla
        modalTitleId.text(musteriId);

        $.ajax({
            url: '/musteriler/' + musteriId + '/detay',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                console.log("Müşteri detayları geldi:", data);
                originalMusteriData = data; 
                
                let leftColumnHtml = '';
                let rightColumnHtml = '';

                leftColumnHtml += '<div class="card mb-3">';
                leftColumnHtml += '    <div class="card-header bg-light py-2"><h6 class="mb-0">Müşteri Bilgileri</h6></div>';
                leftColumnHtml += '    <div class="card-body p-2">';
                leftColumnHtml += '        <p class="mb-1"><strong>Ad / Firma:</strong> <span id="detayMusteriAd">' + ucwordsJs(data.ad || '-') + '</span></p>';
                leftColumnHtml += '        <p class="mb-1"><strong>Telefon 1:</strong> <span id="detayMusteriTel1">' + (data.tel1 || '-') + '</span></p>';
                leftColumnHtml += '        <p class="mb-1"><strong>Telefon 2:</strong> <span id="detayMusteriTel2">' + (data.tel2 || '-') + '</span></p>';
                let adres = data.adres || '-';
                let ilceAd = data.ilce ? data.ilce.ad : '';
                let ilAd = (data.ilce && data.ilce.il) ? data.ilce.il.ad : '';
                let adresDetay = '';
                if (ilceAd) adresDetay = '<br><small class="text-muted">' + ilceAd + ' / ' + ilAd + '</small>';
                leftColumnHtml += '        <p class="mb-1"><strong>Adres:</strong> <span id="detayMusteriAdres">' + ucwordsJs(adres) + '</span><span id="detayMusteriAdresDetay">' + adresDetay + '</span></p>';
                leftColumnHtml += '        <p class="mb-0"><strong>Müşteri Tipi:</strong> <span id="detayMusteriTip">' + (data.musteri_tip == 1 ? '<span class="badge bg-info">Kurumsal</span>' : '<span class="badge bg-secondary">Bireysel</span>') + '</span></p>';
                leftColumnHtml += '    </div>';
                leftColumnHtml += '</div>'; 
                
                if (String(data.musteri_tip) === '1') {
                    leftColumnHtml += '<div class="card mb-3">';
                    leftColumnHtml += '    <div class="card-header bg-light py-2"><h6 class="mb-0">Vergi Bilgileri</h6></div>';
                    leftColumnHtml += '    <div class="card-body p-2">';
                    leftColumnHtml += '        <p class="mb-1"><strong>V. Dairesi:</strong> <span id="detayMusteriVdaire">' + (data.vdaire || '-') + '</span></p>';
                    leftColumnHtml += '        <p class="mb-0"><strong>V. No:</strong> <span id="detayMusteriVno">' + (data.vno || '-') + '</span></p>';
                    leftColumnHtml += '    </div>';
                    leftColumnHtml += '</div>'; 
                }

                rightColumnHtml += '<div class="card">'; 
                rightColumnHtml += '    <div class="card-header bg-light py-2"><h6 class="mb-0">Geçmiş Servis Kayıtları</h6></div>';
                rightColumnHtml += '    <div class="card-body p-2">';
                if (data.servisler && data.servisler.length > 0) {
                    rightColumnHtml += '        <ul class="list-group list-group-flush">'; 
                    data.servisler.forEach(servis => {
                        let durumBadge = '<span class="badge bg-secondary">Bilinmiyor</span>';
                        if (servis.servis_durum) {
                            let durumId = parseInt(servis.servis_durum.id);
                            let badgeClass = 'badge';
                            if (in_array_js(durumId, [9097, 9103, 9477])) badgeClass += ' bg-soft-dark text-dark';
                            else if (in_array_js(durumId, [9098, 9113, 9100])) badgeClass += ' bg-soft-warning text-warning';
                            else if (durumId === 9334) badgeClass += ' bg-soft-primary text-primary';
                            else if (in_array_js(durumId, [9115, 9114, 9105, 9099])) badgeClass += ' bg-soft-success text-success';
                            else badgeClass += ' bg-soft-danger text-danger';
                            durumBadge = '<span class="' + badgeClass + '">' + servis.servis_durum.ad + '</span>';
                        }
                        let kayitTarihi = servis.created_at ? new Date(servis.created_at).toLocaleDateString('tr-TR') : '-';
                        rightColumnHtml += '          <li class="list-group-item d-flex justify-content-between align-items-center p-1 px-0">';
                        rightColumnHtml += `            <small>Servis #${servis.id} (${kayitTarihi})</small>`;
                        rightColumnHtml += `            ${durumBadge}`;
                        rightColumnHtml += '          </li>';
                    });
                    rightColumnHtml += '        </ul>';
                } else {
                    rightColumnHtml += '        <p class="mb-0"><small>Bu müşteriye ait servis kaydı bulunamadı.</small></p>';
                }
                rightColumnHtml += '    </div>';
                rightColumnHtml += '</div>'; 

                const finalHtml = '<div class="row"><div class="col-md-6">' + leftColumnHtml + '</div><div class="col-md-6">' + rightColumnHtml + '</div></div>';
                modalBody.html(finalHtml);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("Müşteri detay getirme hatası:", jqXHR.responseText);
                let errorMsg = 'Müşteri detayları getirilirken bir hata oluştu.';
                if (jqXHR.responseJSON && jqXHR.responseJSON.error) {
                    errorMsg = jqXHR.responseJSON.error;
                }
                modalBody.html('<p class="text-center text-danger">' + errorMsg + '</p>');
            }
        });
    });

    // === Müşteri Detay Modalı Kapandığında Backdrop Temizleme ===
    $('#musteriDetayModal').on('hidden.bs.modal', function () {
        setTimeout(function() {
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
            $('body').css('overflow', '');
            $('body').css('padding-right', '');
        }, 100); 
    });

    // Yeni Müşteri Modalı Kapandığında Backdrop Temizleme
    $('#yeniMusteriModal').on('hidden.bs.modal', function () {
        setTimeout(function() {
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
            $('body').css('overflow', '');
            $('body').css('padding-right', '');
        }, 100);
    });

    // === Yeni Müşteri Formunu AJAX ile Gönderme ===
    yeniMusteriForm.on('submit', function(e) {
        e.preventDefault(); 
        hataMesajlariDiv.addClass('d-none').html(''); 
        yeniMusteriForm.find('.is-invalid').removeClass('is-invalid');
        
        var formData = $(this).serialize(); 
        var submitButton = $(this).find('button[type="submit"]');
        var originalButtonText = submitButton.html();

        submitButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Kaydediliyor...');

        $.ajax({
            url: $(this).attr('action'),
            method: $(this).attr('method'),
            data: formData,
            dataType: 'json',
            success: function(response) {
                yeniMusteriModal.hide();
                Swal.fire(
                    'Başarılı!',
                    response.message || 'Müşteri başarıyla kaydedildi.',
                    'success'
                );
                location.reload(); 
            },
            error: function(jqXHR, textStatus, errorThrown) {
                if (jqXHR.status === 422) { 
                    var errors = jqXHR.responseJSON.errors;
                    var errorHtml = '<ul>';
                    $.each(errors, function(key, value) {
                        errorHtml += '<li>' + value[0] + '</li>';
                        $('[name="' + key + '"]', yeniMusteriForm).addClass('is-invalid');
                    });
                    errorHtml += '</ul>';
                    hataMesajlariDiv.html(errorHtml).removeClass('d-none');
                } else {
                    console.error("Form gönderme hatası:", jqXHR.responseText);
                     Swal.fire(
                        'Hata!',
                        'Müşteri kaydedilirken bir sunucu hatası oluştu. Lütfen tekrar deneyin.',
                        'error'
                    );
                    hataMesajlariDiv.html('<li>Sunucu hatası oluştu. Lütfen tekrar deneyin.</li>').removeClass('d-none');
                }
            },
            complete: function() {
                submitButton.prop('disabled', false).html(originalButtonText);
            }
        });
    });

    $('#modal_tel1, #modal_tel2').on('input', function() {
        formatPhoneNumber(this);
    });

    function musteriBilgileriniInputYap(editMode) {
        if (editMode) {
            console.log("Müşteri Düzenleme Modu: Aktif");
            $('#detayMusteriAd').html('<input type="text" class="form-control form-control-sm modal-input-compact" id="editMusteriAd" name="ad" value="' + ucwordsJs(originalMusteriData.ad || '') + '" required>');
            $('#detayMusteriTel1').html('<input type="text" class="form-control form-control-sm modal-input-compact" id="editMusteriTel1" name="tel1" value="' + (originalMusteriData.tel1 || '') + '" required>');
            $('#detayMusteriTel2').html('<input type="text" class="form-control form-control-sm modal-input-compact" id="editMusteriTel2" name="tel2" value="' + (originalMusteriData.tel2 || '') + '">');
            $('#detayMusteriAdres').html('<textarea class="form-control form-control-sm" id="editMusteriAdres" name="adres" rows="3" required>' + ucwordsJs(originalMusteriData.adres || '') + '</textarea>');
            $('#detayMusteriAdresDetay').empty(); 
            
            $('#musteriDetayContent').on('input', '#editMusteriTel1, #editMusteriTel2', function() {
                formatPhoneNumber(this);
            });
            formatPhoneNumber(document.getElementById('editMusteriTel1'));
            formatPhoneNumber(document.getElementById('editMusteriTel2'));

            let tipHtml = '<div class="d-inline-block">';
            tipHtml += '<div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="musteri_tip" id="editTipBireysel" value="0" ' + (originalMusteriData.musteri_tip != 1 ? 'checked' : '') + '><label class="form-check-label small" for="editTipBireysel">Bireysel</label></div>';
            tipHtml += '<div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="musteri_tip" id="editTipKurumsal" value="1" ' + (originalMusteriData.musteri_tip == 1 ? 'checked' : '') + '><label class="form-check-label small" for="editTipKurumsal">Kurumsal</label></div>';
            tipHtml += '</div>';
            $('#detayMusteriTip').html(tipHtml);
            $('#detayMusteriTip input[name="musteri_tip"]').on('change', toggleEditVergiAlanlari);

            $('#detayMusteriAdres').closest('p').after(
                '<div class="row g-2 mt-1"><div class="col-md-6"><label class="form-label small mb-0">İl</label><select class="form-select form-select-sm modal-input-compact" id="editMusteriIl" name="il_id" required></select></div><div class="col-md-6"><label class="form-label small mb-0">İlçe</label><select class="form-select form-select-sm modal-input-compact" id="editMusteriIlce" name="ilce_id" required disabled><option value="">Önce İl Seçiniz...</option></select></div></div>'
            );
            const editIlSelect = $('#editMusteriIl');
            const editIlceSelect = $('#editMusteriIlce');
            editIlSelect.append('<option value="">Yükleniyor...</option>').prop('disabled', true);
            editIlceSelect.html('<option value="">Önce İl Seçiniz...</option>').prop('disabled', true);

            $.ajax({
                url: '/iller',
                type: 'GET',
                dataType: 'json',
                success: function(illerData) {
                    editIlSelect.empty().append('<option value="">Seçiniz...</option>');
                    if (illerData && illerData.length > 0) {
                        illerData.forEach(il => {
                             editIlSelect.append('<option value="' + il.id + '" ' + (originalMusteriData.il_id == il.id ? 'selected' : '') + '>' + il.ad + '</option>');
                        });
                        editIlSelect.prop('disabled', false);
                        if(originalMusteriData.il_id) {
                             editIlSelect.trigger('change');
                        }
                    } else {
                         editIlSelect.append('<option value="" disabled>İl bulunamadı</option>');
                         console.warn("/iller endpoint'inden il verisi gelmedi veya boş.");
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error("Edit Mod - İl getirme hatası:", jqXHR.status, jqXHR.statusText, jqXHR.responseText, errorThrown);
                    editIlSelect.empty().append('<option value="" disabled>İller yüklenemedi! (Hata)</option>').prop('disabled', true);
                }
            });

            $('#detayMusteriTip').closest('p').after(
                 '<p style="display: none;" class="mb-1 edit-vergi-alani"><strong>V. Dairesi:</strong> <span id="detayMusteriVdaire"><input type="text" class="form-control form-control-sm modal-input-compact" id="editMusteriVdaire" name="vdaire" value="' + (originalMusteriData.vdaire || '') + '"></span></p>' +
                 '<p style="display: none;" class="mb-1 edit-vergi-alani"><strong>V. No:</strong> <span id="detayMusteriVno"><input type="text" class="form-control form-control-sm modal-input-compact" id="editMusteriVno" name="vno" value="' + (originalMusteriData.vno || '') + '"></span></p>'
            );
             
             toggleEditVergiAlanlari();
             
        } else { 
            console.log("Müşteri Düzenleme Modu: İptal");
            $('#musteriDetayModal').trigger('show.bs.modal'); 
        }
    }
    
    function toggleEditVergiAlanlari() {
        const vdaireP = $('#editMusteriVdaire').closest('p.edit-vergi-alani');
        const vnoP = $('#editMusteriVno').closest('p.edit-vergi-alani');

        if ($('#detayMusteriTip input[name="musteri_tip"]:checked').val() === '1') {
            vdaireP.slideDown(); 
            vnoP.slideDown(); 
            $('#editMusteriVdaire').prop('required', true);
            $('#editMusteriVno').prop('required', true);
        } else {
            vdaireP.slideUp();
            vnoP.slideUp();
            $('#editMusteriVdaire').prop('required', false);
            $('#editMusteriVno').prop('required', false);
        }
    }
    
     $('body').on('change', '#editMusteriIl', function() {
        const selectedIlId = $(this).val();
        const editIlceSelect = $('#editMusteriIlce'); 
        editIlceSelect.prop('disabled', true).html('<option value="">Yükleniyor...</option>');
        if (selectedIlId) {
            $.get('/ilceler/' + selectedIlId, function(data) {
                 editIlceSelect.empty().append('<option value="">Seçiniz...</option>');
                 if(data && data.length > 0) {
                    data.forEach(ilce => {
                       editIlceSelect.append('<option value="' + ilce.id + '" ' + (originalMusteriData.ilce_id == ilce.id ? 'selected' : '') + '>' + ilce.ad + '</option>');
                    });
                    editIlceSelect.prop('disabled', false);
                     if(originalMusteriData.il_id == selectedIlId && originalMusteriData.ilce_id) {
                          editIlceSelect.val(originalMusteriData.ilce_id);
                     }
                 } else {
                      editIlceSelect.append('<option value="" disabled>İlçe bulunamadı</option>');
                 }
            }).fail(function() {
                 console.error("Edit - İlçe getirme hatası");
                 editIlceSelect.empty().append('<option value="" disabled>Hata!</option>');
            });
        } else {
            editIlceSelect.empty().append('<option value="">Önce İl Seçiniz...</option>');
        }
    });

    $('#musteriDuzenleBtn').on('click', function() {
        $('#modalFooterNormalMusteri').hide(); // ID güncellendi
        $('#modalFooterEditMusteri').show();
        musteriBilgileriniInputYap(true);
    });

    $('#musteriDuzenleVazgecBtn').on('click', function() {
        $('#musteriDetayModal').trigger('show.bs.modal'); 
    });

    $('#modalMusteriKaydetBtn').on('click', function() {
        console.log("Müşteri Kaydet Butonuna tıklandı.");
        const musteriId = mevcutMusteriId; // mevcutMusteriId kullanıldı
        if (!musteriId) {
            Swal.fire('Hata!', 'Müşteri ID bulunamadı!', 'error');
            return;
        }

        const formData = {
            _method: 'PUT',
            ad: $('#editMusteriAd').val(),
            tel1: $('#editMusteriTel1').val(),
            tel2: $('#editMusteriTel2').val(),
            musteri_tip: $('#detayMusteriTip input[name="musteri_tip"]:checked').val(),
            il_id: $('#editMusteriIl').val(),
            ilce_id: $('#editMusteriIlce').val(),
            adres: $('#editMusteriAdres').val(),
            vdaire: $('#editMusteriVdaire').val(), 
            vno: $('#editMusteriVno').val()
        };
        console.log("Gönderilecek Müşteri Verisi:", formData);

        const submitButton = $(this);
        const originalButtonText = submitButton.html();
        submitButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Kaydediliyor...');

        $.ajax({
            url: '/musteriler/' + musteriId, 
            method: 'POST', 
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire('Başarılı!', response.message, 'success').then(() => {
                         $('#musteriDetayModal').trigger('show.bs.modal'); // Yeniden yükle
                         // Alternatif: Tabloyu yenilemek için
                         // if (typeof musteriListTable !== 'undefined') { musteriListTable.ajax.reload(); }
                         // Veya daha basitçe:
                         // location.reload();
                    });
                } else {
                    Swal.fire('Hata!', response.message || 'Bir hata oluştu.', 'error');
                }
            },
            error: function(jqXHR) {
                console.error("Müşteri Güncelleme AJAX Hatası:", jqXHR);
                let errorMsg = 'Müşteri güncellenirken bir hata oluştu.';
                if (jqXHR.status === 422) { 
                     errorMsg = 'Lütfen formdaki hataları düzeltin.<br>';
                     $.each(jqXHR.responseJSON.errors, function(key, value) {
                         errorMsg += `- ${value[0]}<br>`; 
                         $('[name="' + key + '"]', '#musteriDetayContent').addClass('is-invalid');
                     });
                } else if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                     errorMsg = jqXHR.responseJSON.message;
                }
                Swal.fire('Hata!', errorMsg, 'error');
            },
            complete: function() {
                submitButton.prop('disabled', false).html(originalButtonText);
            }
        });
    });

}); 
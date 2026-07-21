$(document).ready(function() {
    const ilSelect = $('#il_id');
    const ilceSelect = $('#ilce_id');
    const musteriTipiRadios = $('input[name="musteri_tip"]');
    const vergiAlanlariDiv = $('#vergiAlanlari');
    const vergiDairesiInput = $('#vdaire');
    const vergiNoInput = $('#vno');

    // --- Müşteri Tipi Seçimine Göre Vergi Alanlarını Göster/Gizle --- 
    function toggleVergiAlanlari() {
        if ($('input[name="musteri_tip"]:checked').val() === '1') { // Kurumsal seçili ise
            vergiAlanlariDiv.slideDown(); // Animasyonlu göster
            vergiDairesiInput.prop('required', true); // Zorunlu yap
            vergiNoInput.prop('required', true); // Zorunlu yap
        } else { // Bireysel veya başka bir durum
            vergiAlanlariDiv.slideUp(); // Animasyonlu gizle
            vergiDairesiInput.prop('required', false); // Zorunlu değil
            vergiNoInput.prop('required', false); // Zorunlu değil
            // Gizlerken içindeki değerleri de temizleyebiliriz (isteğe bağlı)
            // vergiDairesiInput.val('');
            // vergiNoInput.val('');
        }
    }

    // Başlangıçta kontrol et (validasyon hatasıyla geri dönüldüğünde eski seçimi korumak için)
    toggleVergiAlanlari(); 

    // Radio buton değiştiğinde kontrol et
    musteriTipiRadios.on('change', toggleVergiAlanlari);

    // --- İl Seçimine Göre İlçeleri Getir --- 
    ilSelect.on('change', function() {
        const selectedIlId = $(this).val();
        ilceSelect.prop('disabled', true).html('<option value="" selected disabled>Yükleniyor...</option>');

        if (selectedIlId) {
            $.ajax({
                url: '/ilceler/' + selectedIlId, // Route tanımına uygun endpoint
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    ilceSelect.empty().append('<option value="" selected disabled>İlçe Seçiniz...</option>');
                    if (data && data.length > 0) {
                        $.each(data, function(key, ilce) {
                            ilceSelect.append('<option value="' + ilce.id + '">' + ilce.ad + '</option>');
                        });
                        ilceSelect.prop('disabled', false);
                        
                        // Eğer validasyon hatası sonrası eski ilçe seçilmişse, onu tekrar seç
                        if (window.selectedIlceId && window.selectedIlId == selectedIlId) {
                           ilceSelect.val(window.selectedIlceId);
                        }
                    } else {
                        ilceSelect.append('<option value="" disabled>İlçe bulunamadı</option>');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error("İlçe getirme hatası:", textStatus, errorThrown);
                    ilceSelect.empty().append('<option value="" selected disabled>Hata oluştu</option>');
                }
            });
        } else {
            ilceSelect.empty().append('<option value="" selected disabled>Önce İl Seçiniz...</option>');
        }
    });

    // Sayfa yüklendiğinde, eğer validasyon hatasıyla geri dönülmüşse ve bir il seçiliyse, ilçeleri yükle
    if (window.selectedIlId) {
        ilSelect.trigger('change');
    }

    // Varsayılan Tarih ve Saat (Blade içinde ayarlandı ama JS ile de yapılabilirdi)
    // const now = new Date();
    // const today = now.toISOString().split('T')[0];
    // const currentTime = now.toTimeString().split(' ')[0].substring(0, 5);
    // if (!$('#kayit_tarihi').val()) { // Eğer değer yoksa ata
    //     $('#kayit_tarihi').val(today);
    // }
    // if (!$('#kayit_saati').val()) { // Eğer değer yoksa ata
    //     $('#kayit_saati').val(currentTime);
    // }

}); 
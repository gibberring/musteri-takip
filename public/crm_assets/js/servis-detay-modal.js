// Global Servis Detay Modal Köprüsü
(function(window, $) {
    'use strict';

    if (!window.ServisDetayModal) {
        window.ServisDetayModal = {};
    }

    // Modalı servisId ile açar. Eğer servisId verilmezse mevcut davranışı sürdürür.
    window.ServisDetayModal.open = function(servisId) {
        try {
            var modalEl = document.getElementById('servisDetayModal');
            if (!modalEl) return;

            var hasId = (typeof servisId !== 'undefined' && servisId !== null && servisId !== '');
            if (hasId) {
                // Modal elementine data olarak aktar
                var $modalEl = $('#servisDetayModal');
                if ($modalEl && $modalEl.length) {
                    $modalEl.data('servis-id', servisId);
                }
                try { window.mevcutServisId = servisId; } catch (e) {}

                // Modal zaten açıksa Bootstrap show() no-op olur; detayı doğrudan yükle.
                // Kapalıysa da önce yükle; show.bs.modal çift yüklemeyi skip flag ile engeller.
                if (typeof window.loadServisDetay === 'function') {
                    var alreadyOpen = modalEl.classList.contains('show');
                    if (!alreadyOpen) {
                        window._servisDetaySkipNextShowLoad = true;
                    }
                    window.loadServisDetay(servisId);
                }
            }

            var instance = bootstrap.Modal.getOrCreateInstance(modalEl);
            instance.show();
        } catch (e) {
            console.error('ServisDetayModal.open hata:', e);
        }
    };

    // Kısa yol alias
    window.openServisDetay = window.ServisDetayModal.open;

})(window, window.jQuery);

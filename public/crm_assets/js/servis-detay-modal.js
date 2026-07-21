// Global Servis Detay Modal Köprüsü
(function(window, $) {
    'use strict';

    if (!window.ServisDetayModal) {
        window.ServisDetayModal = {};
    }

    // Modalı servisId ile açar. Eğer servisId verilmezse mevcut davranışı sürdürür.
    window.ServisDetayModal.open = function(servisId) {
        try {
            if (typeof servisId !== 'undefined' && servisId !== null) {
                // Modal elementine data olarak aktar (show handler bu değeri okuyabilir)
                var $modalEl = $('#servisDetayModal');
                if ($modalEl && $modalEl.length) {
                    $modalEl.data('servis-id', servisId);
                }
                // Global değişkeni de güncelle (varsa)
                try { window.mevcutServisId = servisId; } catch (e) {}
            }
            var modalEl = document.getElementById('servisDetayModal');
            if (!modalEl) return;

            var instance = bootstrap.Modal.getOrCreateInstance(modalEl);
            instance.show();
        } catch (e) {
            console.error('ServisDetayModal.open hata:', e);
        }
    };

    // Kısa yol alias
    window.openServisDetay = window.ServisDetayModal.open;

})(window, window.jQuery);



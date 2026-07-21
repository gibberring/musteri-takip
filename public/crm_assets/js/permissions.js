;(function(window){
    'use strict';

    var pozId = (window.crmData && window.crmData.loggedInUserPozId) || null;
    var overrides = (window.crmData && window.crmData.permOverrides) || {}; // { roleId: { ability: boolean } }

    // Basit bir izin matrisi
    var ROLE_IDS = {
        PATRON: 1071,
        OPERATOR: 1073,
        IDARI_ISLER: 1076,
        TEKNISYEN_TSRN: 1077,
        MUHASEBE: 1080
    };

    function isOneOf(arr){ return Array.isArray(arr) && arr.indexOf(pozId) !== -1; }
    function isAllowedByOverride(ability){
        if (!pozId || !overrides || !overrides[pozId]) return null; // null => override yok
        if (typeof overrides[pozId][ability] === 'undefined') return null;
        return !!overrides[pozId][ability];
    }

    var abilities = {
        // Servis
        canViewServisler: function(){ return isOneOf([ROLE_IDS.PATRON, ROLE_IDS.MUHASEBE, ROLE_IDS.IDARI_ISLER, ROLE_IDS.OPERATOR, ROLE_IDS.TEKNISYEN_TSRN]); },
        canCreateServis: function(){ var o=isAllowedByOverride('canCreateServis'); return o!==null?o:isOneOf([ROLE_IDS.PATRON, ROLE_IDS.MUHASEBE, ROLE_IDS.IDARI_ISLER, ROLE_IDS.OPERATOR]); },
        canUpdateServis: function(){ var o=isAllowedByOverride('canUpdateServis'); return o!==null?o:isOneOf([ROLE_IDS.PATRON, ROLE_IDS.MUHASEBE, ROLE_IDS.IDARI_ISLER, ROLE_IDS.OPERATOR]); },
        canDeleteServis: function(){ var o=isAllowedByOverride('canDeleteServis'); return o!==null?o:isOneOf([ROLE_IDS.PATRON, ROLE_IDS.MUHASEBE]); },

        // Filtre butonları
        canViewBolgeFilter: function(){ var o=isAllowedByOverride('canViewBolgeFilter'); return o!==null?o:isOneOf([ROLE_IDS.PATRON, ROLE_IDS.MUHASEBE, ROLE_IDS.IDARI_ISLER, ROLE_IDS.OPERATOR]); },
        canViewOperatorFilter: function(){ var o=isAllowedByOverride('canViewOperatorFilter'); return o!==null?o:isOneOf([ROLE_IDS.PATRON, ROLE_IDS.MUHASEBE, ROLE_IDS.IDARI_ISLER, ROLE_IDS.OPERATOR]); },
        canViewTeknisyenFilter: function(){ var o=isAllowedByOverride('canViewTeknisyenFilter'); return o!==null?o:isOneOf([ROLE_IDS.PATRON, ROLE_IDS.MUHASEBE, ROLE_IDS.IDARI_ISLER, ROLE_IDS.OPERATOR]); },

        // İşlem logları
        canEditLogs: function(){ var o=isAllowedByOverride('canEditLogs'); return o!==null?o:isOneOf([ROLE_IDS.PATRON, ROLE_IDS.MUHASEBE]); },
        canDeleteLogs: function(){
            if (pozId === ROLE_IDS.OPERATOR) return true;
            var o=isAllowedByOverride('canDeleteLogs');
            return o!==null?o:isOneOf([ROLE_IDS.PATRON, ROLE_IDS.MUHASEBE, ROLE_IDS.IDARI_ISLER]);
        },

        // Müşteriler
        canViewMusteriler: function(){ var o=isAllowedByOverride('canViewMusteriler'); return o!==null?o:isOneOf([ROLE_IDS.PATRON, ROLE_IDS.MUHASEBE, ROLE_IDS.IDARI_ISLER, ROLE_IDS.OPERATOR]); },
        canEditMusteri: function(){ var o=isAllowedByOverride('canEditMusteri'); return o!==null?o:isOneOf([ROLE_IDS.PATRON, ROLE_IDS.MUHASEBE, ROLE_IDS.IDARI_ISLER]); },
        canDeleteMusteri: function(){ var o=isAllowedByOverride('canDeleteMusteri'); return o!==null?o:isOneOf([ROLE_IDS.PATRON, ROLE_IDS.MUHASEBE, ROLE_IDS.IDARI_ISLER]); },

        // Personeller
        canViewPersoneller: function(){ var o=isAllowedByOverride('canViewPersoneller'); return o!==null?o:isOneOf([ROLE_IDS.PATRON, ROLE_IDS.MUHASEBE]); },
        canManagePersoneller: function(){ var o=isAllowedByOverride('canManagePersoneller'); return o!==null?o:isOneOf([ROLE_IDS.PATRON, ROLE_IDS.MUHASEBE]); },

        // Kasa
        canViewKasa: function(){ var o=isAllowedByOverride('canViewKasa'); return o!==null?o:isOneOf([ROLE_IDS.PATRON, ROLE_IDS.MUHASEBE, ROLE_IDS.TEKNISYEN_TSRN]); },
        // Ödeme ekleme yetkisini, düzenleme yetkisinden ayrı tanımlıyoruz
        canAddKasa: function(){ var o=isAllowedByOverride('canAddKasa'); return o!==null?o:isOneOf([ROLE_IDS.PATRON, ROLE_IDS.MUHASEBE, ROLE_IDS.OPERATOR, ROLE_IDS.TEKNISYEN_TSRN]); },
        canEditKasa: function(){ var o=isAllowedByOverride('canEditKasa'); return o!==null?o:isOneOf([ROLE_IDS.PATRON, ROLE_IDS.MUHASEBE]); },
        canDeleteKasa: function(){ var o=isAllowedByOverride('canDeleteKasa'); return o!==null?o:isOneOf([ROLE_IDS.PATRON, ROLE_IDS.MUHASEBE]); },
        canViewKasaOnlyOwn: function(){ return pozId === ROLE_IDS.TEKNISYEN_TSRN; },

        // Ayarlar
        canViewAyarlar: function(){ var o=isAllowedByOverride('canViewAyarlar'); return o!==null?o:(pozId === ROLE_IDS.PATRON); },

        // Profiller ve panel
        canViewTeknisyenProfil: function(){ var o=isAllowedByOverride('canViewTeknisyenProfil'); return o!==null?o:isOneOf([ROLE_IDS.PATRON, ROLE_IDS.MUHASEBE, ROLE_IDS.IDARI_ISLER, ROLE_IDS.OPERATOR]); },
        canViewPanel: function(){ var o=isAllowedByOverride('canViewPanel'); return o!==null?o:isOneOf([ROLE_IDS.PATRON, ROLE_IDS.MUHASEBE]); },

        // Servis görünürlük kapsamı
        canViewOwnServis: function(){ var o=isAllowedByOverride('canViewOwnServis'); return o!==null?o:(pozId === ROLE_IDS.TEKNISYEN_TSRN); },

        // Dosya/Resim ve PDF
        canAddResim: function(){ var o=isAllowedByOverride('canAddResim'); return o!==null?o:isOneOf([ROLE_IDS.PATRON, ROLE_IDS.MUHASEBE, ROLE_IDS.TEKNISYEN_TSRN]); },
        canViewPdfFis: function(){ var o=isAllowedByOverride('canViewPdfFis'); return o!==null?o:isOneOf([ROLE_IDS.PATRON, ROLE_IDS.MUHASEBE, ROLE_IDS.IDARI_ISLER, ROLE_IDS.OPERATOR, ROLE_IDS.TEKNISYEN_TSRN]); }
    };

    window.PERM = {
        roles: ROLE_IDS,
        can: abilities,
        setPozisyonId: function(id){ pozId = id; }
    };

})(window);
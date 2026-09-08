    <style>
        #servisDetayModal .tdd {
            white-space: normal;
            word-break: break-word;
            overflow-wrap: anywhere;
        }
    </style>
    <div class="modal fade notranslate" id="servisDetayModal" tabindex="-1" aria-labelledby="servisDetayModalLabel" aria-hidden="true" translate="no">
        {{-- <div class="modal-dialog modal-lg modal-dialog-centered"> --}}
        <div class="modal-dialog modal-lg modal-dialog-centered"> {{-- modal-xl'den modal-lg'ye değiştirildi --}}
            <div class="modal-content">
                {{-- Örnekteki Üst Başlık Alanı - Renk Değiştirildi --}}
                <div style="background-color: #3454d1; padding: 10px; font-weight: bold; height: auto; min-height: 40px; font-size: 15px; border-bottom: 1px solid #d1d1d1; color: #ffffff;" class="d-flex justify-content-between align-items-center">
                    <div> <!-- Sol Taraf Gruplaması -->
                    <span id="modalGosterBaslik">SERVİS (#<span id="modalServisId"></span>)</span>
                    <small class="ms-2 fw-normal" style="font-size: 0.75em;">Kayıt: <span id="modalServisKayitTarihiBaslik">-</span></small>
                    </div>
                    <div class="d-flex align-items-center"> <!-- Sağ Taraf Gruplaması -->
                        <small class="me-2 fw-normal" style="font-size: 0.75em;">Operatör: <span id="modalServisOperatoruBaslik">-</span></small>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>

                <div class="modal-body">
                    <div class="row">
                        {{-- Müşteri Bilgileri (Sol Taraf) --}}
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-light py-2">
                                    <h6 class="mb-0 fw-semibold text-primary">
                                        <i class="feather feather-user me-2"></i>Müşteri Bilgileri
                                    </h6>
                                </div>
                                <div class="card-body p-3">
                                    <div class="row g-2">
                                        <div class="col-12 d-flex align-items-center">
                                            <label class="form-label small text-muted mb-0 me-2" style="min-width: 80px;">Müşteri Adı:</label>
                                            <div id="modalMusteriAd" class="fw-medium"></div>
                                        </div>
                                        <div class="col-12 d-flex align-items-center">
                                            <label class="form-label small text-muted mb-0 me-2" style="min-width: 80px;">Telefon:</label>
                                            <div id="modalMusteriTel" class="fw-medium"></div>
                                        </div>
                                        <div class="col-12 d-flex align-items-center">
                                            <label class="form-label small text-muted mb-0 me-2" style="min-width: 80px;">Adres:</label>
                                            <span id="modalMusteriAdres" class="fw-medium"></span>
                                            <a id="modalMusteriAdresLink" href="#" target="_blank" rel="noopener" class="ms-2 modal-adres-link d-none" aria-label="Yandex Navigasyon">
                                                <i class="feather feather-navigation"></i>
                                            </a>
                                        </div>
                                        <div id="modalMusteriIlRow" class="col-12 d-flex align-items-center d-none">
                                            <label class="form-label small text-muted mb-0 me-2" style="min-width: 80px;">İl:</label>
                                            <div id="modalMusteriIl" class="fw-medium"></div>
                                        </div>
                                        <div id="modalMusteriIlceRow" class="col-12 d-flex align-items-center d-none">
                                            <label class="form-label small text-muted mb-0 me-2" style="min-width: 80px;">İlçe:</label>
                                            <div id="modalMusteriIlce" class="fw-medium"></div>
                                        </div>
                                        <div class="col-12 d-flex align-items-center">
                                            <label class="form-label small text-muted mb-0 me-2" style="min-width: 80px;">Vergi Dairesi:</label>
                                            <div id="modalVergiDairesi" class="fw-medium"></div>
                                        </div>
                                        <div class="col-12 d-flex align-items-center">
                                            <label class="form-label small text-muted mb-0 me-2" style="min-width: 80px;">Vergi No:</label>
                                            <div id="modalVergiNo" class="fw-medium"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- Mevcut Durum Gösterimi --}}
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-light py-2">
                                    <h6 class="mb-0 fw-semibold text-primary">
                                        <i class="feather feather-user me-2"></i>Servis Bilgileri
                                    </h6>
                                </div>
                                <div class="card-body p-3">
                                    <div class="row g-2">
                                       
                                        <div class="col-12 mb-2"> <!-- Operatör Notu Eklendi -->
                                            <label class="form-label small text-muted mb-0 me-2" style="min-width: 80px;">Operatör Notu:</label>
                                            <div id="modalOperatorNotu" class="fw-medium d-inline-block" style="white-space: pre-wrap; word-break: break-word;"></div>
                                        </div> <!-- Operatör Notu Sonu -->

                                        <div class="d-flex align-items-center"> 
                                            <b class="me-2">Mevcut Durum:</b> 
                                            <div id="modalMevcutDurumWrapper" class="d-inline-block"></div>
                                        </div> <!-- Eklendi -->

                                        <div class="d-flex align-items-center mt-2" id="modalTeknisyenGorulduRow">
                                            <i class="feather feather-user-check me-2" id="modalTeknisyenGorulduIcon" style="font-size:1rem;"></i>
                                            <span id="modalTeknisyenGorulduText" class="small text-muted">—</span>
                                        </div>
                                                                             
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3">
                               
                            </div>
                        </div>

                        {{-- Cihaz/Servis Bilgileri (Sağ Taraf) --}}
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-light py-2">
                                    <h6 class="mb-0 fw-semibold text-primary">
                                        <i class="feather feather-settings me-2"></i>Cihaz Bilgileri
                                    </h6>
                                </div>
                                <div class="card-body p-3">
                                    <div class="row g-2">
                                        <div class="col-12 d-flex align-items-center">
                                            <label class="form-label small text-muted mb-0 me-2" style="min-width: 100px;">Marka:</label>
                                            <div id="modalMarkaAd" class="fw-medium flex-grow-1"></div> <!-- flex-grow-1 eklendi -->
                                        </div>
                                        <div class="col-12 d-flex align-items-center">
                                            <label class="form-label small text-muted mb-0 me-2" style="min-width: 100px;">Cihaz Türü:</label>
                                            <div id="modalCihazTuruAd" class="fw-medium flex-grow-1"></div> <!-- flex-grow-1 eklendi -->
                                        </div>
                                        <div class="col-12 d-flex align-items-center">
                                            <label class="form-label small text-muted mb-0 me-2" style="min-width: 100px;">Model:</label>
                                            <div id="modalCihazModel" class="fw-medium flex-grow-1"></div> <!-- flex-grow-1 eklendi -->
                                        </div>
                                        <div class="col-12 d-flex align-items-center">
                                            <label class="form-label small text-muted mb-0 me-2" style="min-width: 100px;">Seri No:</label>
                                            <div id="modalSeriNo" class="fw-medium flex-grow-1"></div> <!-- flex-grow-1 eklendi -->
                                        </div>
                                        <div class="col-12 d-flex align-items-center">
                                            <label class="form-label small text-muted mb-0 me-2" style="min-width: 100px;">Cihaz Arızası:</label>
                                            <div id="modalCihazAriza" class="fw-medium flex-grow-1"></div> <!-- flex-grow-1 eklendi -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- Durum Güncelleme Alanı --}}
                            <hr>
                            <div class="mt-3">
                                <h6>Durumu Güncelle</h6>
                                <div class="input-group mt-2">
                                    <select class="form-select form-select-sm" id="modalDurumGuncelleSelect">
                                        {{-- Seçenekler JS ile doldurulacak --}}
                                    </select>
                                </div>
                                {{-- Dinamik Form Alanı --}}
                                <div id="modalDinamikFormAlani" class="mt-3"></div>
                                {{-- Kaydet Butonu (başlangıçta gizli) --}}
                                <button class="btn btn-sm btn-success mt-3" type="button" id="modalDurumKaydetBtn" style="display: none;">
                                    <i class="feather feather-save me-1"></i>Kaydet
                                </button> <!-- ID GÜNCELLENDİ -->
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Servis Durumu, Yapılan İşlemler, Kasa gibi diğer bölümler buraya eklenebilir --}}
                {{-- <hr> --}} {{-- Bu satır kaldırıldı/yorum yapıldı --}}
                {{-- Yapılan İşlemler Bölümü --}}
                <div class="card mb-3"> <!-- Card Başlangıcı -->
                    <div class="card-header bg-light py-2"> <!-- Card Header -->
                        <h6 class="mb-0">Serviste Yapılan İşlemler</h6>
                    </div>
                    <div class="card-body p-2"> <!-- Card Body -->
                        <div id="servisYapilanIslemlerDv" class="table-responsive" style="max-height: 250px; overflow-y: auto;"> <!-- mt-2 kaldırıldı -->
                            <table class="table table-sm table-bordered table-striped">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th style="width: 80px;">TAR&#304;H</th>
                                        <th>&#304;&#350;LEM&#304; YAPAN</th>
                                        <th>İşlem Adı</th>
                                        <th>Açıklama</th>
                                        @php
                                            $loggedInUser = Auth::user();
                                            $patronPozisyonId = 1071;
                                        @endphp
                                        @if ($loggedInUser && $loggedInUser->poz_id == $patronPozisyonId)
                                        <th style="width: 80px;">AKS&#304;YON</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody id="modalIslemLoglariBody">
                                    {{-- AJAX ile doldurulacak --}}
                                    <tr><td colspan="5" class="text-center">Yükleniyor...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div> <!-- Card Sonu -->

                {{-- Para Hareketleri Bölümü --}}
                <div class="card"> <!-- Card Başlangıcı, mt-4 buraya taşındı -->
                     <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center"> <!-- Card Header -->
                        <h6 class="mb-0">Para Hareketleri</h6>
                        <button class="btn btn-sm btn-success" id="odemeEkleBtn" type="button" style="display: none;">
                            <i class="feather feather-plus me-1"></i>Ödeme Ekle
                        </button>
                    </div>
                    <div class="card-body p-2"> <!-- Card Body -->
                         <div id="kasaGosterDiv" class="table-responsive" style="max-height: 200px; overflow-y: auto;"> <!-- mt-2 kaldırıldı -->
                            <table class="table table-sm table-bordered table-striped" style="font-size: 10px;">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th style="width: 80px;">TAR&#304;H</th>
                                        <th>&#304;&#350;LEM&#304; YAPAN</th>
                                        <th>TAHS&#304;L EDEN</th>
                                        <th>&#214;DEME &#350;EKL&#304;</th>
                                        <th>Ödeme Durumu</th>
                                        <th>Tutar</th>
                                        <th style="width: 60px;">AKS&#304;YON</th>
                                    </tr>
                                </thead>
                                <tbody id="modalKasaHareketleriBody">
                                    {{-- AJAX ile doldurulacak --}}
                                    <tr><td colspan="7" class="text-center">Yükleniyor...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div> <!-- Card Sonu -->
                
                @php
                    $loggedInUser = Auth::user();
                    // permissions.js canAddResim varsayılanları: Patron, Muhasebe, TŞRN Teknisyen
                    $canAddResim = $loggedInUser && \App\Models\RoleAbility::isAllowed(
                        (int) $loggedInUser->poz_id,
                        'canAddResim',
                        [1071, 1080, 1077]
                    );
                @endphp

                @if ($canAddResim)
                {{-- Resim Bilgileri Bölümü (Personel ayarları → canAddResim) --}}
                <div class="card mt-3"> <!-- Card Başlangıcı -->
                    <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center"> <!-- Card Header -->
                        <h6 class="mb-0">Resim Bilgileri</h6>
                        <button class="btn btn-sm btn-warning" id="resimEkleBtn" type="button" data-bs-toggle="modal" data-bs-target="#resimYukleModal">
                            <i class="feather feather-plus-circle me-1"></i>RESİM EKLE
                        </button>
                    </div>
                    <div class="card-body p-2"> <!-- Card Body -->
                        <div id="servisResimleriContainer" class="d-flex flex-wrap gap-2"> <!-- Resimleri göstermek için -->
                            <p class="text-muted w-100 text-center" id="noImageText">Henüz resim bulunmuyor.</p>
                        </div>
                    </div>
                </div> <!-- Card Sonu -->
                @endif

                <!-- Modal Footer -->
                <div class="modal-footer" id="modalFooterNormal"> <!-- justify-content-end kaldırıldı -->
                    @php
                        $loggedInUser = Auth::user();
                        $tsrnTeknisyenPozisyonId = 1077; // TŞRN Teknisyen pozisyon ID'si
                        $operatorPozisyonId = 1073; // Operatör pozisyon ID'si
                        // permissions.js canViewPdfFis varsayılanları: Patron, Muhasebe, İdari, Operatör, TŞRN Teknisyen
                        $canViewPdfFis = $loggedInUser && \App\Models\RoleAbility::isAllowed(
                            (int) $loggedInUser->poz_id,
                            'canViewPdfFis',
                            [1071, 1080, 1076, 1073, 1077]
                        );
                    @endphp

                    @if ($loggedInUser && $loggedInUser->poz_id != $tsrnTeknisyenPozisyonId && $loggedInUser->poz_id != $operatorPozisyonId)
                    <button type="button" class="btn btn-sm btn-danger me-auto shadow-sm" id="servisSilBtn" onclick="silServisKaydini()">
                        <i class="feather feather-trash-2 me-1"></i>SERVİSİ SİL
                    </button>
                    @endif
                    @if ($canViewPdfFis)
                    <button type="button" class="btn btn-sm btn-info shadow-sm" id="servisFisiPdfBtn" data-bs-toggle="modal" data-bs-target="#servisFisiModal">
                        <i class="feather feather-file-text me-1"></i>PDF FİŞ
                    </button>
                    @endif
                    <button type="button" class="btn btn-sm btn-secondary shadow-sm" data-bs-dismiss="modal">
                        <i class="feather feather-x-circle me-1"></i>Kapat
                    </button>
                    @if ($loggedInUser && $loggedInUser->poz_id != $tsrnTeknisyenPozisyonId)
                    <button type="button" class="btn btn-sm btn-primary shadow-sm" id="servisGuncelleBtn">
                        <i class="feather feather-edit me-1"></i>SERVİSİ GÜNCELLE
                    </button>
                    @endif
                </div>
                <div class="modal-footer" id="modalFooterEdit" style="display: none;"> <!-- justify-content-end kaldırıldı -->
                    <button type="button" class="btn btn-sm btn-secondary shadow-sm" id="vazgecBtn">
                        <i class="feather feather-x me-1"></i>Vazgeç
                    </button>
                    <button type="button" class="btn btn-sm btn-success shadow-sm" id="modalCihazKaydetBtn">
                        <i class="feather feather-save me-1"></i>DEĞİŞİKLİKLERİ KAYDET
                    </button> <!-- ID GÜNCELLENDİ -->
                </div>
            </div>
        </div>
    </div> 

    {{-- Resim Yükleme Modalı --}}
    <div class="modal fade" id="resimYukleModal" tabindex="-1" aria-labelledby="resimYukleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="resimYukleModalLabel">Servise Resim Ekle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="resimUploadForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Resim Dosyası</label>
                            <div id="resimInputContainer">
                                <input class="form-control" type="file" id="resimDosyasi" name="resim" accept="image/*" required>
                            </div>
                            <div class="mt-2">
                                <small class="text-muted d-block">
                                    <i class="feather feather-info me-1"></i>
                                    Mobil cihazlarda hem kamera hem galeri seçeneği mevcuttur.
                                </small>
                                <small class="text-warning d-block">
                                    <i class="feather feather-alert-triangle me-1"></i>
                                    <strong>Önemli:</strong> Kamera kullanmak için HTTPS bağlantısı ve kamera izni gereklidir.
                                </small>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="resimAciklama" class="form-label">Açıklama (İsteğe Bağlı)</label>
                            <textarea class="form-control" id="resimAciklama" name="aciklama" rows="3"></textarea>
                        </div>
                        <input type="hidden" name="servis_id" id="resimUploadServisId">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                        <button type="submit" class="btn btn-primary" id="resimYukleSubmitBtn">Yükle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


<script>
document.addEventListener('DOMContentLoaded', function () {
    /**
     * Durum güncelleme dropdown'ını, mevcut duruma ve hiyerarşiye göre doldurur.
     * @param {number|string|null} currentServiceStatusId Mevcut servisin durum ID'si.
     * @param {string} currentServiceStatusName Mevcut servisin durum adı.
     * @param {Array} allPossibleStatuses Tüm servis durumlarının listesi 
     *        (her biri {id, ad, hangi_asamalarda_gorunur} içermeli).
     */
    function populateStatusDropdown(currentServiceStatusId, currentServiceStatusName, allPossibleStatuses) {
        const dropdown = document.getElementById('modalDurumGuncelleSelect'); // jQuery yerine vanilla JS
        const mevcutDurumWrapper = document.getElementById('modalMevcutDurumWrapper');

        if (!dropdown || !mevcutDurumWrapper) {
            console.error('Dropdown veya mevcut durum wrapper elementi bulunamadı!');
            return;
        }

        dropdown.innerHTML = ''; // Önceki seçenekleri temizle

        // İlk seçenek olarak "Seçiniz..." ekle
        let placeholderOption = new Option('Seçiniz...', '');
        dropdown.add(placeholderOption);


        // Mevcut durumu göster
        if (currentServiceStatusName) {
            // Servis listesindeki renklendirme mantığını buraya taşıyalım
            let badgeClass = 'badge';
            const durumId = Number(currentServiceStatusId); // ID'yi sayıya çevirelim

            if ([9097, 9103, 9477].includes(durumId)) {
                badgeClass += ' bg-soft-dark text-dark';
            } else if ([9098].includes(durumId)) {
                badgeClass += ' bg-soft-secondary text-secondary';
            } else if ([9113, 9100].includes(durumId)) {
                badgeClass += ' bg-soft-warning text-warning';
            } else if (durumId === 9334) {
                badgeClass += ' bg-soft-primary text-primary';
            } else if ([9115, 9114, 9105, 9099].includes(durumId)) {
                badgeClass += ' bg-soft-success text-success';
            } else {
                badgeClass += ' bg-soft-danger text-danger';
            }
            mevcutDurumWrapper.innerHTML = `<span class="${badgeClass}">${currentServiceStatusName}</span>`;
        } else {
            mevcutDurumWrapper.innerHTML = `<span class="badge bg-secondary">Belirlenmemiş</span>`;
        }

        let hasOptions = false;

        // Rol: Modal açılmadan önce window.loggedInUserPozId boş olabilir; crmData yedek
        const patronId = 1071;
        const muhasebeId = 1080;
        const teknisyenId = 1077;
        const operatorId = 1073;
        const hariciOperatorId = 1076;
        const parcaGidecekId = 9103;
        const atolyeAlindiId = 9100;
        const servisSonlandirildiId = 9099;
        const restrictedStatuses = [9098, 9477, 9334, 9116]; // Teknisyen yönlendirildi, Yarın Gidilecek, Teyid Araması, Tekrar Servis
        const rawPoz = (typeof window.loggedInUserPozId !== 'undefined' && window.loggedInUserPozId !== null && window.loggedInUserPozId !== '')
            ? window.loggedInUserPozId
            : (window.crmData && window.crmData.loggedInUserPozId != null ? window.crmData.loggedInUserPozId : null);
        const loggedInPozId = rawPoz !== null ? Number(rawPoz) : null;
        // Patron/Muhasebe: hiyerarşi kısıtı olmadan tüm durum seçenekleri
        const isYonetici = [patronId, muhasebeId].includes(loggedInPozId);

        allPossibleStatuses.forEach(status => {
            let shouldShow = false;

            if (isYonetici) {
                // Yönetici: hangi_asamalarda_gorunur filtresi atlanır
                shouldShow = true;
            } else {
                // API bazen sayı/null dönebilir; trim güvenli olsun
                const hiyerarsiListesi = (status.hangi_asamalarda_gorunur === null || status.hangi_asamalarda_gorunur === undefined)
                    ? ''
                    : String(status.hangi_asamalarda_gorunur);

                if (hiyerarsiListesi === '0') {
                    if (currentServiceStatusId === null || currentServiceStatusId === undefined) {
                        shouldShow = true;
                    }
                } else if (!hiyerarsiListesi || hiyerarsiListesi.trim() === '') {
                    shouldShow = true; // Kısıtlama yoksa göster
                } else if (currentServiceStatusId !== null && currentServiceStatusId !== undefined) {
                    const allowedPreviousIds = hiyerarsiListesi.split(',')
                                                 .map(id => id.trim())
                                                 .filter(id => id !== '');
                    if (allowedPreviousIds.includes(String(currentServiceStatusId))) {
                        shouldShow = true;
                    }
                }
            }

            // Mevcut durumun kendisini listede göstermeyelim
            if (status.id == currentServiceStatusId) {
                shouldShow = false;
            }

            // 9098 kendi hiyerarşisinde yok; mevcut durum 9098 iken yeniden yönlendirme için tekrar göster.
            // Teknisyen / Harici Operatör aşağıda yine gizler.
            if (Number(currentServiceStatusId) === 9098 && Number(status.id) === 9098) {
                shouldShow = true;
            }

            // Rol bazlı ek kısıtlar / force-show (Parça Gidecek restricted vb.)
            try {
                // Teknisyen + Harici Operatör: hedef 9098 (Teknisyen Yönlendirildi) her zaman gizli
                if ([hariciOperatorId, teknisyenId].includes(Number(loggedInPozId)) && Number(status.id) === 9098) {
                    shouldShow = false;
                }
                // Parça Gidecek / Sonlandırıldı: restricted durumlar sadece Patron/Muhasebe (+ Operatör→9116)
                if ([parcaGidecekId, servisSonlandirildiId].includes(Number(currentServiceStatusId)) && !isYonetici) {
                    if (restrictedStatuses.includes(Number(status.id))) {
                        if (!(Number(status.id) === 9116 && Number(loggedInPozId) === operatorId)) {
                            shouldShow = false;
                        }
                    }
                }
                if (Number(loggedInPozId) === teknisyenId && Number(currentServiceStatusId) === 9116) {
                    if ([9100, 9105].includes(Number(status.id))) {
                        shouldShow = true;
                    }
                }
                if (Number(loggedInPozId) === teknisyenId && Number(currentServiceStatusId) === 9106) {
                    if (Number(status.id) === 9334) {
                        shouldShow = false;
                    }
                }
                if (Number(loggedInPozId) === teknisyenId && Number(currentServiceStatusId) === 9098) {
                    if (Number(status.id) === 9103) {
                        shouldShow = true;
                    }
                }
                if (Number(status.id) === servisSonlandirildiId && Number(loggedInPozId) === teknisyenId) {
                    shouldShow = false;
                }
                // Atölyeye Alındı (9100): yönetici zaten tüm listeyi görür;
                // teknisyen/operatör vb. için pratik çıkışları force-show (hiyerarşi eksikse boş kalmasın)
                if (!isYonetici && Number(currentServiceStatusId) === atolyeAlindiId) {
                    if ([9115, 9103, 9105, 9104].includes(Number(status.id))) {
                        shouldShow = true;
                    }
                }
            } catch (e) { /* no-op */ }

            if (shouldShow) {
                let option = new Option(status.ad, status.id);
                dropdown.add(option);
                hasOptions = true;
            }
        });

        const modalDurumKaydetBtn = document.getElementById('modalDurumKaydetBtn');
        if (!hasOptions) {
            let option = new Option('Seçilebilecek durum yok', '');
            dropdown.add(option);
            if(modalDurumKaydetBtn) modalDurumKaydetBtn.style.display = 'none';
        } else {
            if(modalDurumKaydetBtn) modalDurumKaydetBtn.style.display = 'block'; // Veya inline, inline-block vb.
        }
    }

    // ÖNEMLİ: Bu populateStatusDropdown fonksiyonunu, AJAX ile servis detaylarınızı 
    // ve tüm durumları (`allPossibleStatuses`) aldıktan sonra çağırmanız gerekir.
    // Örneğin:
    // window.triggerServisDetayModal = function(servisId) {
    //     // AJAX çağrınızı burada yapın (örneğin fetch veya jQuery.ajax ile)
    //     // $.ajax({
    //     //     url: `/api/servis-detaylari/${servisId}`, // Controller metodunuzun rotası
    //     //     method: 'GET',
    //     //     success: function(response) {
    //     //         if (response.success) {
    //     //             // Modal'daki diğer alanları doldur... 
    //     //             // Örneğin: document.getElementById('modalServisId').textContent = response.servis.id;
    //     //             populateStatusDropdown(response.mevcutDurumId, response.mevcutDurumAdi, response.tumDurumlar);
    //     //             var modalInstance = new bootstrap.Modal(document.getElementById('servisDetayModal'));
    //     //             modalInstance.show();
    //     //         } else {
    //     //             alert(response.message || 'Bir hata oluştu.');
    //     //         }
    //     //     },
    //     //     error: function() {
    //     //         alert('Servis detayları yüklenemedi.');
    //     //     }
    //     // });
    // };

    // Eğer global bir fonksiyona atamak isterseniz:
    window.populateServisDurumDropdown = populateStatusDropdown;
});
</script> 
<div class="modal fade" id="whatsappApiModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Whatsapp API Ayarları</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info py-2 px-3 small mb-3" role="note">
                    <strong>Otomatik gönderim için API zorunlu.</strong>
                    Teknisyen yönlendirildiğinde mesaj yalnızca WhatsApp API (ör. Green-API) ile sessiz gider; <code>wa.me</code> / manuel açma yoktur.
                </div>
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label small mb-1">API URL</label>
                        <input type="text" class="form-control form-control-sm" id="waApiUrl" placeholder="https://api.green-api.com/waInstanceXXXX">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small mb-1">API Key / Token</label>
                        <input type="text" class="form-control form-control-sm" id="waApiKey" placeholder="instance token">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small mb-1">Default Numara (ülke kodlu)</label>
                        <input type="text" class="form-control form-control-sm" id="waDefaultNumber" placeholder="905xxxxxxxxx">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small mb-1">Gönderici Adı</label>
                        <input type="text" class="form-control form-control-sm" id="waSenderName" placeholder="Firma Adı">
                    </div>
                </div>

                <hr class="my-3">

                <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-1">
                    <label class="form-label small mb-0" for="waTeknisyenSablon">Teknisyen yönlendirme mesaj şablonu</label>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="waSablonVarsayilanBtn">Varsayılana dön</button>
                </div>
                <textarea class="form-control form-control-sm font-monospace" id="waTeknisyenSablon" rows="8" spellcheck="false" placeholder="Mesaj şablonu..."></textarea>
                <div class="form-text small mt-1">
                    Köşeli parantezli etiketler gönderimde otomatik doldurulur. Boş alanlar <code>-</code> olur.
                </div>
                <div class="mt-2" id="waPlaceholderHelp">
                    <div class="small text-muted mb-1">Kullanılabilir etiketler (tıklayınca eklenir):</div>
                    <div class="d-flex flex-wrap gap-1" id="waPlaceholderTags"></div>
                </div>

                <div class="mt-3 text-end">
                    <button class="btn btn-sm btn-primary" id="waAyarKaydetBtn">Kaydet</button>
                </div>
                <hr>
                <div class="mt-2">
                    <label class="form-label small mb-1">Test Mesajı Gönder</label>
                    <div class="row g-2">
                        <div class="col-md-4"><input type="text" class="form-control form-control-sm" id="waTestTo" placeholder="905xxxxxxxxx"></div>
                        <div class="col-md-6"><input type="text" class="form-control form-control-sm" id="waTestMsg" placeholder="Mesaj"></div>
                        <div class="col-md-2"><button class="btn btn-sm btn-success w-100" id="waTestBtn">Gönder</button></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

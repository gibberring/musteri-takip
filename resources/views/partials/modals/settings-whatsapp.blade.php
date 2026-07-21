<div class="modal fade" id="whatsappApiModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Whatsapp API Ayarları</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label small mb-1">API URL</label>
                        <input type="text" class="form-control form-control-sm" id="waApiUrl" placeholder="https://...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small mb-1">API Key / Token</label>
                        <input type="text" class="form-control form-control-sm" id="waApiKey" placeholder="token">
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


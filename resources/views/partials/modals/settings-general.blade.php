<div class="modal fade" id="genelAyarModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Genel Ayarlar - Firma Bilgileri</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="firmaBilgileriForm">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Firma Adı</label>
                            <input type="text" class="form-control form-control-sm" name="firma_adi" placeholder="Firma Adı">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Vergi Dairesi</label>
                            <input type="text" class="form-control form-control-sm" name="vergi_dairesi" placeholder="Vergi Dairesi">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Vergi No</label>
                            <input type="text" class="form-control form-control-sm" name="vergi_no" placeholder="Vergi No">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Telefon</label>
                            <input type="text" class="form-control form-control-sm" name="telefon" placeholder="Telefon">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Adres</label>
                            <textarea class="form-control form-control-sm" rows="2" name="adres" placeholder="Adres"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">E-Posta</label>
                            <input type="email" class="form-control form-control-sm" name="eposta" placeholder="ornek@firma.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Web Sitesi</label>
                            <input type="text" class="form-control form-control-sm" name="web" placeholder="https://...">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Kapat</button>
                <button type="button" id="firmaBilgileriKaydetBtn" class="btn btn-success"><i class="feather-save me-2"></i>Kaydet</button>
            </div>
        </div>
    </div>
</div>

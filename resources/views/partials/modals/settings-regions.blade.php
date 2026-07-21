<div class="modal fade" id="bolgeAyarModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Bölge Ayarları</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h6 class="mb-0">Bölgeler</h6>
          <div class="text-muted small">Soldan il seçin, sağdan ilçelerini işaretleyin. İl seçimi otomatik yapılır.</div>
        </div>
        <div class="row g-3">
          <div class="col-md-4">
            <div class="list-group" id="ilForIlceList" style="max-height: 420px; overflow:auto;"></div>
          </div>
          <div class="col-md-8">
            <div class="d-flex justify-content-between mb-2">
              <div class="text-muted small">Aktif ilçeleri işaretleyin.</div>
              <div>
                <button class="btn btn-sm btn-outline-secondary" id="ilcelerSelectAll">Tümünü Seç</button>
                <button class="btn btn-sm btn-outline-secondary" id="ilcelerClearAll">Temizle</button>
              </div>
            </div>
            <div class="row" id="ilcelerList"></div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Kapat</button>
        <button type="button" class="btn btn-sm btn-primary" id="bolgeAyarKaydetBtn">Kaydet</button>
      </div>
    </div>
  </div>
</div>


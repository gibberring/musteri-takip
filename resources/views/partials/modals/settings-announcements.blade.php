<div class="modal fade" id="duyuruAyarModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Duyurular</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3 p-2 border rounded bg-light">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label small mb-1">Başlık</label>
                            <input type="text" class="form-control form-control-sm ayar-mini-input" id="duyuruYeniBaslik">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small mb-1">Hedef Rol</label>
                            <select class="form-select form-select-sm ayar-mini-input" id="duyuruYeniHedefRol">
                                <option value="TEKNISYEN">TEKNİSYEN</option>
                                <option value="OPERATOR">OPERATÖR</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small mb-1">Aktif</label>
                            <select class="form-select form-select-sm ayar-mini-input" id="duyuruYeniAktif">
                                <option value="1">Evet</option>
                                <option value="0">Hayır</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small mb-1">Yayın Tarihi</label>
                            <input type="datetime-local" class="form-control form-control-sm ayar-mini-input" id="duyuruYeniYayin">
                        </div>
                        <div class="col-12">
                            <label class="form-label small mb-1">İçerik</label>
                            <textarea class="form-control form-control-sm" id="duyuruYeniIcerik" rows="2" style="min-height: 48px;"></textarea>
                        </div>
                        <div class="col-12 text-end">
                            <button class="btn btn-sm btn-primary" id="duyuruEkleBtn"><i class="feather-plus me-1"></i>Ekle</button>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle" id="duyurularTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 28%">Başlık</th>
                                <th style="width: 18%">Hedef Rol</th>
                                <th style="width: 10%">Aktif</th>
                                <th style="width: 20%">Yayın Tarihi</th>
                                <th>İçerik</th>
                                <th style="width: 80px">#</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="6" class="text-center">Yükleniyor...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Kapat</button>
            </div>
        </div>
    </div>
    </div>



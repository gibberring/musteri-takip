<div class="modal fade" id="cihazTurModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cihaz Türleri</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="input-group input-group-sm mb-2" style="max-width: 520px;">
                    <input type="text" id="yeniCihazTurAd" class="form-control ayar-mini-input" placeholder="Yeni cihaz türü adı...">
                    <button class="btn btn-success btn-sm" id="yeniCihazTurEkleBtn"><i class="feather-plus me-1"></i>Ekle</button>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="table-responsive">
                            <table class="table table-sm align-middle" id="cihazTurTableLeft">
                                <thead>
                                    <tr>
                                        <th style="width:70%">Tür Adı</th>
                                        <th class="text-end" style="width:30%">İşlem</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="table-responsive">
                            <table class="table table-sm align-middle" id="cihazTurTableRight">
                                <thead>
                                    <tr>
                                        <th style="width:70%">Tür Adı</th>
                                        <th class="text-end" style="width:30%">İşlem</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



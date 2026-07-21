<style>
/* Mobil cihazlarda imza alanları için özel stiller */
@media (max-width: 768px) {
    #imzaModal .modal-dialog {
        margin: 0.5rem;
        max-width: calc(100vw - 1rem);
    }

    #imzaModal .modal-content {
        border-radius: 0.5rem;
    }

    #musteriImzaWrapper, #teknisyenImzaWrapper {
        min-height: 180px !important;
        max-height: 250px;
        touch-action: none;
        -webkit-touch-callout: none;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
    }

    #musteriImzaAlani, #teknisyenImzaAlani {
        touch-action: none;
        cursor: crosshair;
    }

    /* Mobil cihazlarda imza alanlarının daha iyi görünmesi için */
    #imzaModal .col-md-6 {
        margin-bottom: 1rem;
    }

    #imzaModal .modal-body {
        padding: 1rem;
    }
}

/* Tüm cihazlarda imza wrapper'ları için genel stiller */
#musteriImzaWrapper, #teknisyenImzaWrapper {
    position: relative;
    border: 2px solid #ced4da;
    border-radius: 0.375rem;
    background: #fff;
    overflow: hidden;
    box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);
}

#musteriImzaAlani, #teknisyenImzaAlani {
    display: block;
    position: relative;
    z-index: 1;
}

/* İmza alanında çizim sırasında görsel feedback */
.imza-alani:active {
    cursor: crosshair;
}

/* Dokunmatik cihazlarda daha iyi deneyim için */
@media (hover: none) and (pointer: coarse) {
    #musteriImzaWrapper:hover, #teknisyenImzaWrapper:hover {
        border-color: #adb5bd;
    }
}
</style>

<div class="modal fade" id="imzaModal" tabindex="-1" aria-labelledby="imzaModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imzaModalLabel">Servis Fişi İçin İmzalar (Servis No: <span id="imzaModalServisId"></span>)</h5>
            </div>
            <div class="modal-body">
                <p class="text-muted small">Lütfen aşağıdaki alanlara müşteri ve teknisyen imzalarını alınız. İmzalar servis fişine eklenecektir.</p>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="musteriImzaAlani" class="form-label fw-bold">Müşteri İmzası:</label>
                        <div id="musteriImzaWrapper" style="border: 1px solid #ced4da; border-radius: .25rem; min-height: 200px; cursor: crosshair;">
                            <canvas id="musteriImzaAlani" class="imza-alani"></canvas>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="musteriImzaTemizleBtn">Temizle</button>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="teknisyenImzaAlani" class="form-label fw-bold">Teknisyen İmzası:</label>
                        <div id="teknisyenImzaWrapper" style="border: 1px solid #ced4da; border-radius: .25rem; min-height: 200px; cursor: crosshair;">
                            <canvas id="teknisyenImzaAlani" class="imza-alani"></canvas>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="teknisyenImzaTemizleBtn">Temizle</button>
                    </div>
                </div>
                <div id="imzaHataMesaji" class="alert alert-danger d-none mt-2"></div>
            </div>
            <div class="modal-footer justify-content-between">
                <div>
                    <button type="button" class="btn btn-light" id="imzaModalVazgecBtn" data-bs-dismiss="modal">Vazgeç</button>
                </div>
                <div>
                    <button type="button" class="btn btn-warning me-2" id="servisFormuImzasizVerBtn">
                        <i class="feather-file-minus me-1"></i> Servis Formunu İmzasız Ver
                    </button>
                    <button type="button" class="btn btn-success" id="imzalariKaydetVeFormuVerBtn">
                        <i class="feather-check-circle me-1"></i> İmzaları Kaydet & Servis Formunu Ver
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>


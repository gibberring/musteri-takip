<div class="modal fade" id="servisFisiModal" tabindex="-1" aria-labelledby="servisFisiModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="servisFisiModalLabel">Servis Fişi İşlemleri (Servis No: <span id="servisFisiModalServisId"></span>)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-grid gap-2 mb-3">
                    <button class="btn btn-success" type="button" id="yeniServisFisiOlusturBtn">
                        <i class="feather feather-plus-circle me-1"></i> YENİ SERVİS FİŞİ OLUŞTUR
                    </button>
                </div>
                <h6>Daha Önce Oluşturulan Fişler:</h6>
                <div id="eskiServisFisleriListesi" class="list-group" style="max-height: 300px; overflow-y: auto;">
                    <p class="text-muted text-center">Bu servise ait daha önce oluşturulmuş fiş bulunmamaktadır.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
            </div>
        </div>
    </div>
</div>


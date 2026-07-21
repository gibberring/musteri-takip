<div class="modal fade islem-log-modal" id="duzenleIslemLogModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title">İşlem Kaydını Düzenle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="duzenleIslemLogId">
                <div class="mb-1">
                    <label class="form-label small">Tarih</label>
                    <input type="date" class="form-control form-control-sm" id="duzenleIslemLogTarih">
                </div>
                <div class="mb-1">
                    <label class="form-label small">Saat</label>
                    <input type="time" class="form-control form-control-sm" id="duzenleIslemLogSaat">
                </div>
                <div class="mb-1">
                    <label class="form-label small">Durum</label>
                    <select class="form-select form-select-sm" id="duzenleIslemLogDurum">
                        @if(isset($servisDurumlar))
                            @foreach($servisDurumlar as $durum)
                                <option value="{{ $durum->id }}">{{ $durum->ad }}</option>
                            @endforeach
                        @else
                            <option disabled>Durumlar yüklenemedi</option>
                        @endif
                    </select>
                </div>
                <div class="mb-1">
                    <label class="form-label small">Açıklama</label>
                    <textarea class="form-control form-control-sm" id="duzenleIslemLogAciklama" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer py-1">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">İptal</button>
                <button type="button" class="btn btn-sm btn-primary" onclick="kaydetIslemLog()">Kaydet</button>
            </div>
        </div>
    </div>
</div>


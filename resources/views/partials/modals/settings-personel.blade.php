<div class="modal fade" id="personelAyarModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Personel Yetkileri</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @php
                    $roleIds = [1071,1073,1076,1077,1080];
                    $gruplar = [
                        'SERVİS' => [
                            'canViewServisler' => 'SERVİSLERİ GÖREBİLİR',
                            'canViewOwnServis' => 'KENDİ SERVİSLERİNİ GÖREBİLİR',
                            'canCreateServis' => 'YENİ SERVİS OLUŞTURABİLİR',
                            'canUpdateServis' => 'SERVİS DÜZENLEYEBİLİR',
                            'canDeleteServis' => 'SERVİS SİLEBİLİR',
                            'canEditLogs' => 'SERVİS HAREKETİ DÜZENLEYEBİLİR',
                            'canDeleteLogs' => 'SERVİS HAREKETİ SİLEBİLİR',
                        ],
                        'PERSONEL' => [
                            'canViewPersoneller' => 'PERSONELLERİ GÖREBİLİR',
                        ],
                        'KASA' => [
                            'canViewKasa' => 'KASAYI GÖREBİLİR',
                            'canEditKasa' => 'KASA KAYDI DÜZENLEYEBİLİR',
                            'canDeleteKasa' => 'KASA KAYDI SİLEBİLİR',
                        ],
                        'GENEL' => [
                            'canViewPanel' => 'AYARLARI GÖREBİLİR',
                            'canViewBolgeFilter' => 'BÖLGE FİLTRELEYEBİLİR',
                            'canViewOperatorFilter' => 'OPERATÖR FİLTRELEYEBİLİR',
                            'canViewTeknisyenFilter' => 'TEKNİSYEN FİLTRELEYEBİLİR',
                            'canAddResim' => 'RESİM EKLEYEBİLİR',
                            'canViewPdfFis' => 'PDF FİŞ ÇIKARABİLİR',
                        ],
                    ];
                @endphp

                @foreach($gruplar as $grupAdi => $abilitiesLabels)
                <div class="mb-4">
                    <h6 class="fw-bold mb-2">{{ $grupAdi }}</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>İZİN</th>
                                    @foreach($roleIds as $role)
                                        <th class="text-center">{{ $roleNames[$role] ?? $role }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($abilitiesLabels as $abKey => $abLabel)
                                <tr>
                                    <td class="fw-semibold">{{ $abLabel }}</td>
                                    @foreach($roleIds as $role)
                                        @php
                                            $val = optional(optional($abilities)[$role])->firstWhere('ability', $abKey);
                                            $checked = $val ? (bool)$val->allowed : false;
                                        @endphp
                                        <td class="text-center">
                                            <div class="form-check d-inline-flex justify-content-center align-items-center izin-check" title="{{ $abLabel }} - {{ $roleNames[$role] ?? $role }}">
                                                <input type="checkbox" class="form-check-input izin-checkbox" data-role="{{ $role }}" data-ability="{{ $abKey }}" {{ $checked ? 'checked' : '' }}>
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Kapat</button>
                <button type="button" id="izinleriKaydetBtn" class="btn btn-success"><i class="feather-save me-2"></i>Kaydet</button>
            </div>
        </div>
    </div>
</div>

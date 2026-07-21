<div class="p-2">
    @php
        $gunGosterim = $gun && preg_match('/^\d{4}-\d{2}-\d{2}$/', $gun)
            ? \Carbon\Carbon::parse($gun)->format('d.m.Y')
            : $gun;
        $dateFromGosterim = isset($dateFrom) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)
            ? \Carbon\Carbon::parse($dateFrom)->format('d.m.Y')
            : $gunGosterim;
        $dateToGosterim = isset($dateTo) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)
            ? \Carbon\Carbon::parse($dateTo)->format('d.m.Y')
            : $gunGosterim;
        $tarihAralikGosterim = ($dateFromGosterim !== $dateToGosterim)
            ? $dateFromGosterim . ' - ' . $dateToGosterim
            : $dateFromGosterim;
    @endphp
    <div class="small text-muted mb-2">
        {{ $tarihAralikGosterim }} - {{ $teknisyen->ad }} detayları
    </div>

    @if ($adetli)
        <div class="table-responsive">
            <table class="table table-sm table-bordered mb-0 kasa-detay-table">
                <thead class="table-light">
                    <tr>
                        <th>TAR&#304;H</th>
                        <th>M&#220;&#350;TER&#304; ADI</th>
                        <th>MARKA/C&#304;HAZ/AR&#304;ZA</th>
                        <th>SERV&#304;S DURUMU</th>
                        <th class="text-end">TUTAR</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($servisler as $servis)
                        @php
                            $servisTarih = $servis->tarih ? $servis->tarih : '';
                            $servisSaat = $servis->saat ? $servis->saat : '';
                            $servisTarihSaat = trim($servisTarih . ' ' . $servisSaat);
                            $servisTarihGoster = $servisTarihSaat
                                ? \Carbon\Carbon::parse($servisTarihSaat)->format('d.m.Y H:i')
                                : '-';
                        @endphp
                        <tr>
                            <td>{{ $servisTarihGoster }}</td>
                            <td>
                                @if ($servis->musteri)
                                    @if (isset($canOpenServisDetay) && !$canOpenServisDetay)
                                        {{ $servis->musteri->ad }}
                                    @else
                                        <a href="#" class="servis-detay-link" data-servis-id="{{ $servis->id }}">
                                            {{ $servis->musteri->ad }}
                                        </a>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                {{ $servis->marka->ad ?? '-' }}
                                / {{ $servis->cihazTuru->ad ?? '-' }}
                                / {{ $servis->cihaz_model }}
                                / {{ isset($servis->cihaz_arizasi) && $servis->cihaz_arizasi !== '' ? (mb_strtoupper(mb_substr($servis->cihaz_arizasi, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($servis->cihaz_arizasi, 1, null, 'UTF-8')) : '-' }}
                            </td>
                            <td>{{ $servis->servisDurum->ad ?? '-' }}</td>
                            <td class="text-end">Adetli</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Kayıt bulunamadı.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if (isset($kasaHareketleri) && $kasaHareketleri->isNotEmpty())
            <div class="small text-muted mt-2 mb-1">Ödeme hareketleri</div>
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0 kasa-detay-table">
                    <thead class="table-light">
                        <tr>
                            <th>TAR&#304;H</th>
                            <th>M&#220;&#350;TER&#304; ADI</th>
                            <th>MARKA/C&#304;HAZ/ARIZA</th>
                            <th>SERV&#304;S DURUMU</th>
                            <th class="text-end">TUTAR</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($kasaHareketleri as $hareket)
                            @php
                                $servis = $hareket->servis;
                                $hareketTarih = $hareket->tarih ? $hareket->tarih : '';
                                $hareketSaat = $hareket->saat ? $hareket->saat : '';
                                $hareketTarihSaat = trim($hareketTarih . ' ' . $hareketSaat);
                                $hareketTarihGoster = $hareketTarihSaat
                                    ? \Carbon\Carbon::parse($hareketTarihSaat)->format('d.m.Y H:i')
                                    : '-';
                                $isGider = ((int) ($hareket->odeme_yonu ?? 0) === -1);
                                $isBekleyen = ((int) ($hareket->gerceklesme ?? 1) === 0);
                                $aciklamaHam = $hareket->aciklama ?? '';
                                $aciklamaTemiz = trim((string) $aciklamaHam);
                                $aciklamaGoster = $aciklamaTemiz !== ''
                                    ? (mb_strtoupper(mb_substr($aciklamaTemiz, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($aciklamaTemiz, 1, null, 'UTF-8'))
                                    : '-';
                                $servisDurumGoster = $isGider
                                    ? $aciklamaGoster
                                    : ($servis && $servis->servisDurum ? $servis->servisDurum->ad : '-');
                                $tutarGoster = $hareket->tutar ?? 0;
                            @endphp
                            <tr class="{{ $isGider ? 'kasa-detay-gider' : '' }} {{ $isBekleyen ? 'table-danger' : '' }}">
                                <td>{{ $hareketTarihGoster }}</td>
                                <td>
                                    @if ($servis && $servis->musteri)
@if (isset($canOpenServisDetay) && !$canOpenServisDetay)
                                        {{ $servis->musteri->ad }}
                                    @else
                                        <a href="#" class="servis-detay-link" data-servis-id="{{ $servis->id }}">
                                            {{ $servis->musteri->ad }}
                                        </a>
                                    @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if ($servis)
                                        {{ $servis->marka->ad ?? '-' }}
                                        / {{ $servis->cihazTuru->ad ?? '-' }}
                                        / {{ isset($servis->cihaz_arizasi) && $servis->cihaz_arizasi !== '' ? (mb_strtoupper(mb_substr($servis->cihaz_arizasi, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($servis->cihaz_arizasi, 1, null, 'UTF-8')) : '-' }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $servisDurumGoster }}@if($isBekleyen) <span class="badge bg-danger ms-1">Beklemede</span>@endif</td>
                                <td class="text-end">
                                    @if($isGider)
                                        -{{ number_format((float)$tutarGoster, 2, ',', '.') }} TL
                                    @else
                                        {{ number_format((float)$tutarGoster, 2, ',', '.') }} TL
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    @else
        <div class="table-responsive">
            <table class="table table-sm table-bordered mb-0 kasa-detay-table">
                <thead class="table-light">
                    <tr>
                        <th>TAR&#304;H</th>
                        <th>M&#220;&#350;TER&#304; ADI</th>
                        <th>MARKA/C&#304;HAZ/ARIZA</th>
                        <th>SERV&#304;S DURUMU</th>
                        <th class="text-end">TUTAR</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($kasaHareketleri as $hareket)
                        @php
                            $servis = $hareket->servis;
                            $hareketTarih = $hareket->tarih ? $hareket->tarih : '';
                            $hareketSaat = $hareket->saat ? $hareket->saat : '';
                            $hareketTarihSaat = trim($hareketTarih . ' ' . $hareketSaat);
                            $hareketTarihGoster = $hareketTarihSaat
                                ? \Carbon\Carbon::parse($hareketTarihSaat)->format('d.m.Y H:i')
                                : '-';
                        @endphp
                        @php
                            $isGider = ((int) ($hareket->odeme_yonu ?? 0) === -1);
                            $isBekleyen = ((int) ($hareket->gerceklesme ?? 1) === 0);
                            $aciklamaHam = $hareket->aciklama ?? '';
                            $aciklamaTemiz = trim((string) $aciklamaHam);
                            $aciklamaGoster = $aciklamaTemiz !== ''
                                ? (mb_strtoupper(mb_substr($aciklamaTemiz, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($aciklamaTemiz, 1, null, 'UTF-8'))
                                : '-';
                            $servisDurumGoster = $isGider
                                ? $aciklamaGoster
                                : ($servis && $servis->servisDurum ? $servis->servisDurum->ad : '-');
                        @endphp
                        <tr class="{{ $isGider ? 'kasa-detay-gider' : '' }} {{ $isBekleyen ? 'table-danger' : '' }}">
                            <td>{{ $hareketTarihGoster }}</td>
                            <td>
                                @if ($servis && $servis->musteri)
                                    @if (isset($canOpenServisDetay) && !$canOpenServisDetay)
                                        {{ $servis->musteri->ad }}
                                    @else
                                        <a href="#" class="servis-detay-link" data-servis-id="{{ $servis->id }}">
                                            {{ $servis->musteri->ad }}
                                        </a>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if ($servis)
                                    {{ $servis->marka->ad ?? '-' }}
                                    / {{ $servis->cihazTuru->ad ?? '-' }}
                                    / {{ isset($servis->cihaz_arizasi) && $servis->cihaz_arizasi !== '' ? (mb_strtoupper(mb_substr($servis->cihaz_arizasi, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($servis->cihaz_arizasi, 1, null, 'UTF-8')) : '-' }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $servisDurumGoster }}@if($isBekleyen) <span class="badge bg-danger ms-1">Beklemede</span>@endif</td>
                            @php
                                $tutarGoster = $hareket->tutar ?? 0;
                            @endphp
                            <td class="text-end">
                                @if($isGider)
                                    -{{ number_format((float)$tutarGoster, 2, ',', '.') }} TL
                                @else
                                    {{ number_format((float)$tutarGoster, 2, ',', '.') }} TL
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Kayıt bulunamadı.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
</div>

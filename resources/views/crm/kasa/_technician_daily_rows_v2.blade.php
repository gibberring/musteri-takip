@php
    $loggedInUser = Auth::user();
    $canMesaiAksiyon = $loggedInUser && in_array((int) $loggedInUser->poz_id, [1071, 1080]);
    $canSeeMesaiColumn = !$loggedInUser || (int) $loggedInUser->poz_id !== 1077;
    $canSeeBekleyenUyari = $loggedInUser && in_array((int) $loggedInUser->poz_id, [1071, 1080]);
@endphp
@forelse ($gunlukOzetler as $ozet)
    @php
        $gunGosterim = $gun && preg_match('/^\d{4}-\d{2}-\d{2}$/', $gun)
            ? \Carbon\Carbon::parse($gun)->format('d.m.Y')
            : $gun;
        $sonSaat = $ozet['son_saat'] ?? null;
        $sonSaatGoster = $sonSaat ? \Carbon\Carbon::parse($sonSaat)->format('H:i') : null;
        $gunTarihGoster = $sonSaatGoster ? ($gunGosterim . ' ' . $sonSaatGoster) : $gunGosterim;
    @endphp
    <tr class="teknisyen-row" data-teknisyen-id="{{ $ozet['teknisyen']->id }}" data-gun="{{ $gunGosterim }}" data-mesai="{{ (int) ($ozet['teknisyen']->mesai_basladimi ?? 0) }}">
        <td>{{ $gunTarihGoster }}</td>
        <td class="teknisyen-cell" style="cursor:pointer;">
            <span class="teknisyen-toggle" aria-hidden="true">
                <i class="feather feather-plus-circle"></i>
            </span>
            @if ($canSeeBekleyenUyari && !empty($ozet['bekleyen_tutar']) && (float)$ozet['bekleyen_tutar'] > 0)
                <span class="ms-1 text-warning" title="Bekleyen ödeme var" aria-label="Bekleyen ödeme var">
                    <i class="feather feather-alert-circle"></i>
                </span>
            @endif
            <span class="ms-1">{{ $ozet['teknisyen']->ad }}</span>
        </td>
        @if ($canSeeMesaiColumn)
        <td class="text-center mesai-cell">
            @if(($ozet['teknisyen']->mesai_basladimi ?? 0) == 1)
                <span class="badge bg-soft-success text-success">Çalışıyor</span>
            @else
                <span class="badge bg-soft-danger text-danger">Çalışmıyor</span>
            @endif
        </td>
        @endif
        <td class="text-center text-success">
            @if (!empty($ozet['adetli']))
                Adetli
            @else
                {{ number_format((float)$ozet['gelir'], 2, ',', '.') }} TL
            @endif
        </td>
        <td class="text-center text-danger">
            @if (!empty($ozet['adetli']))
                Adetli
            @else
                -{{ number_format((float)$ozet['gider'], 2, ',', '.') }} TL
            @endif
        </td>
        <td class="text-center fw-bold">
            @if (!empty($ozet['adetli']))
                Adetli
            @else
                {{ number_format((float)$ozet['teknisyen_payi'], 2, ',', '.') }} TL
            @endif
        </td>
        <td class="text-center fw-bold">
            {{ number_format((float)$ozet['firma_payi'], 2, ',', '.') }} TL
        </td>
        @if ($canMesaiAksiyon)
        <td class="text-center">
            <input type="checkbox" class="form-check-input teknisyen-select" value="{{ $ozet['teknisyen']->id }}">
        </td>
        @endif
    </tr>
    <tr class="teknisyen-detail-row d-none">
        @php
            $detailColspan = $canMesaiAksiyon ? 8 : 7;
            if (!$canSeeMesaiColumn) {
                $detailColspan -= 1;
            }
        @endphp
        <td colspan="{{ $detailColspan }}" class="bg-light"></td>
    </tr>
@empty
    <tr>
        @php
            $emptyColspan = $canMesaiAksiyon ? 8 : 7;
            if (!$canSeeMesaiColumn) {
                $emptyColspan -= 1;
            }
        @endphp
        <td colspan="{{ $emptyColspan }}" class="text-center">Kayıt bulunamadı.</td>
    </tr>
@endforelse

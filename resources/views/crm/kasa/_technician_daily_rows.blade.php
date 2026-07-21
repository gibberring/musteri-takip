@forelse ($gunlukOzetler as $ozet)
    @php
        $gunGosterim = $gun && preg_match('/^\d{4}-\d{2}-\d{2}$/', $gun)
            ? \Carbon\Carbon::parse($gun)->format('d.m.Y')
            : $gun;
    @endphp
    <tr class="teknisyen-row" data-teknisyen-id="{{ $ozet['teknisyen']->id }}" data-gun="{{ $gunGosterim }}" data-mesai="{{ (int) ($ozet['teknisyen']->mesai_basladimi ?? 0) }}">
        <td>{{ $gunGosterim }}</td>
        <td class="teknisyen-cell" style="cursor:pointer;">
            <span class="teknisyen-toggle" aria-hidden="true">
                <i class="feather feather-chevron-right"></i>
            </span>
            <span class="ms-1">{{ $ozet['teknisyen']->ad }}</span>
        </td>
        <td class="text-center">
            @if(($ozet['teknisyen']->mesai_basladimi ?? 0) == 1)
                <span class="badge bg-soft-success text-success">Çalışıyor</span>
            @else
                <span class="badge bg-soft-danger text-danger">Çalışmıyor</span>
            @endif
        </td>
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
                {{ number_format((float)$ozet['gider'], 2, ',', '.') }} TL
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
        <td class="text-center">
            <input type="checkbox" class="form-check-input teknisyen-select" value="{{ $ozet['teknisyen']->id }}">
        </td>
    </tr>
    <tr class="teknisyen-detail-row d-none">
        <td colspan="8" class="bg-light"></td>
    </tr>
@empty
    <tr>
        <td colspan="8" class="text-center">Kayıt bulunamadı.</td>
    </tr>
@endforelse

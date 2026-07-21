@forelse ($gunlukOzetler as $ozet)
    <tr class="teknisyen-row" data-teknisyen-id="{{ $ozet['teknisyen']->id }}">
        <td class="teknisyen-cell" style="cursor:pointer;">
            <span class="teknisyen-toggle" aria-hidden="true">
                <i class="feather feather-chevron-right"></i>
            </span>
            <span class="ms-1">{{ $ozet['teknisyen']->ad }}</span>
        </td>
        <td class="text-center text-success">
            {{ number_format((float)$ozet['gelir'], 2, ',', '.') }} TL
        </td>
        <td class="text-center text-danger">
            {{ number_format((float)$ozet['gider'], 2, ',', '.') }} TL
        </td>
        <td class="text-center fw-bold">
            {{ number_format((float)$ozet['teknisyen_payi'], 2, ',', '.') }} TL
        </td>
        <td class="text-center fw-bold">
            {{ number_format((float)$ozet['firma_payi'], 2, ',', '.') }} TL
        </td>
    </tr>
    <tr class="teknisyen-detail-row d-none">
        <td colspan="5" class="bg-light"></td>
    </tr>
@empty
    <tr>
        <td colspan="5" class="text-center">Kayıt bulunamadı.</td>
    </tr>
@endforelse

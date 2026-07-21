@php use Illuminate\Support\Str; use Carbon\Carbon; @endphp
@forelse ($kasaHareketleri as $hareket)
    <tr class="clickable-row" data-hareket-id="{{ $hareket->id }}" data-yon="{{ $hareket->odemeTuru->yon ?? 0 }}" style="cursor: pointer;">
        <td><a href="#" class="fw-bold">#{{ $hareket->id }}</a></td>
        @php
            $tarihGoster = 'N/A';
            if ($hareket->tarih) {
                $saatDeger = $hareket->saat;
                if (!$saatDeger && $hareket->created_at) {
                    $saatDeger = Carbon::parse($hareket->created_at)->format('H:i');
                }
                $tarihSaat = trim($hareket->tarih . ' ' . ($saatDeger ?? ''));
                $tarihGoster = Carbon::parse($tarihSaat)->format($saatDeger ? 'd.m.Y H:i' : 'd.m.Y');
            }
        @endphp
        <td>{{ $tarihGoster }}</td>
        <td>{{ $hareket->odemeTuru->ad ?? 'N/A' }}</td>
        <td>
            @php
                $muhattaplar = [];
                if ($hareket->odemeTuru && $hareket->odemeTuru->muhattap) {
                    $muhattaplar = array_map('strtoupper', array_map('trim', explode(',', $hareket->odemeTuru->muhattap)));
                }
                $personelGoster = in_array('PERSONEL', $muhattaplar);
                $servisGoster = in_array('SERVIS', $muhattaplar);
            @endphp

            @if ($personelGoster && $hareket->personel)
                <strong>Personel:</strong> {{ $hareket->personel->ad ?? 'N/A' }}<br>
            @endif
            @if ($servisGoster && $hareket->servis_id)
                <strong>Servis No:</strong> {{ $hareket->servis_id }}<br>
            @endif

            {{-- Eğer muhattap sadece ACIKLAMA ise veya diğerleri yoksa, ana açıklamayı göster --}}
            @php
                $aciklama = $hareket->aciklama ?? '';
                if (trim($aciklama) === 'Servis Detay ekranından manuel ödeme eklendi.') {
                    $aciklama = '';
                }
            @endphp
            @if (in_array('ACIKLAMA', $muhattaplar) || (!$personelGoster && !$servisGoster))
                @if($aciklama !== '')
                    {{ Str::limit($aciklama, 70) }}
                @endif
            @elseif(!$personelGoster && !$servisGoster && empty($aciklama))
                -
            @endif
        </td>
        <td>{{ $hareket->odemeSekli->ad ?? 'N/A' }}</td>
        <td>
            @if($hareket->gerceklesme == 1)
                <span class="badge bg-soft-success text-success">Tamamlandı</span>
            @elseif($hareket->gerceklesme === 0)
                <span class="badge bg-soft-warning text-warning">Beklemede</span>
            @else
                {{ $hareket->gerceklesme ?? 'N/A' }}
            @endif
        </td>
        <td>{{ $hareket->islem_tarihi ? Carbon::parse($hareket->islem_tarihi)->format('d.m.Y') : 'N/A' }}</td>
        <td class="text-end fw-bold {{ ($hareket->odeme_yonu == -1) ? 'text-danger' : 'text-success' }}">
            @if ($hareket->odeme_yonu == -1)
                -{{ number_format((float)($hareket->tutar ?? 0), 2, ',', '.') }} TL
            @else
                {{ number_format((float)($hareket->tutar ?? 0), 2, ',', '.') }} TL
            @endif
        </td>
        <td class="text-center no-print">
            @if(isset($isPatron) && $isPatron)
                <input type="checkbox" class="kasa-checkbox" data-id="{{ $hareket->id }}">
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8" class="text-center">Arama kriterlerinize uygun kayıt bulunamadı.</td>
    </tr>
@endforelse

@forelse($silinenKasa as $kasa)
    <tr data-row-id="kasa-{{ $kasa->id }}">
        <td>#{{ $kasa->id }}</td>
        <td>{{ $kasa->silinme_tarihi ?? '-' }}</td>
        <td>
            @if($kasa->servis?->id)
                <a href="{{ route('servisler.index', ['open_servis_id' => $kasa->servis->id]) }}" target="_blank" rel="noopener noreferrer">#{{ $kasa->servis->id }}</a>
            @else
                -
            @endif
        </td>
        <td>{{ $kasa->ilgiliPersonel?->ad ?? $kasa->personel?->ad ?? '-' }}</td>
        <td>{{ $kasa->odemeTuru?->ad ?? '-' }}</td>
        <td>{{ number_format((float) $kasa->tutar, 2, ',', '.') }} TL</td>
        <td>{{ $kasa->silenKisi?->ad ?? '-' }}</td>
        <td class="text-end">
            <form method="POST" action="{{ route('settings.deletedRecords.restoreKasa', $kasa->id) }}" class="d-inline restore-form">
                @csrf
                <button type="submit" class="btn btn-sm btn-success restore-btn" data-restore-url="{{ route('settings.deletedRecords.restoreKasa', $kasa->id) }}" onclick="return window.restoreDeletedRecord(this);">
                    <i class="feather-rotate-ccw"></i> Geri Al
                </button>
            </form>
        </td>
    </tr>
@empty
    <tr><td colspan="8" class="text-center text-muted">{{ $emptyMessage ?? 'Silinen kasa kaydı bulunamadı.' }}</td></tr>
@endforelse

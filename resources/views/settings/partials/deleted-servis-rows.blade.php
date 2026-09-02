@forelse($silinenServisler as $servis)
    <tr data-row-id="servis-{{ $servis->id }}">
        <td>#{{ $servis->id }}</td>
        <td>{{ $servis->silinme_tarihi ?? '-' }}</td>
        <td>{{ $servis->musteri?->ad ?? '-' }}</td>
        <td>{{ $servis->personel?->ad ?? '-' }}</td>
        <td>{{ $servis->servisDurum?->ad ?? '-' }}</td>
        <td>{{ $servis->silenKisi?->ad ?? '-' }}</td>
        <td class="text-end">
            <form method="POST" action="{{ route('settings.deletedRecords.restoreServis', $servis->id) }}" class="d-inline restore-form">
                @csrf
                <button type="submit" class="btn btn-sm btn-success restore-btn" data-restore-url="{{ route('settings.deletedRecords.restoreServis', $servis->id) }}" onclick="return window.restoreDeletedRecord(this);">
                    <i class="feather-rotate-ccw"></i> Geri Al
                </button>
            </form>
        </td>
    </tr>
@empty
    <tr><td colspan="7" class="text-center text-muted">{{ $emptyMessage ?? 'Silinen servis kaydı bulunamadı.' }}</td></tr>
@endforelse

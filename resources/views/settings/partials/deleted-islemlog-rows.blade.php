@forelse($silinenIslemLoglari as $log)
    <tr data-row-id="log-{{ $log->id }}">
        <td>#{{ $log->id }}</td>
        <td>{{ $log->silinme_tarihi ?? '-' }}</td>
        <td>{{ $log->servis?->id ? '#'.$log->servis->id : '-' }}</td>
        <td>{{ $log->servisDurum?->ad ?? '-' }}</td>
        <td class="text-truncate" style="max-width: 360px;">{{ $log->aciklama ?? '-' }}</td>
        <td>{{ $log->silenKisi?->ad ?? '-' }}</td>
        <td class="text-end">
            <form method="POST" action="{{ route('settings.deletedRecords.restoreIslemLog', $log->id) }}" class="d-inline restore-form">
                @csrf
                <button type="submit" class="btn btn-sm btn-success restore-btn" data-restore-url="{{ route('settings.deletedRecords.restoreIslemLog', $log->id) }}" onclick="return window.restoreDeletedRecord(this);">
                    <i class="feather-rotate-ccw"></i> Geri Al
                </button>
            </form>
        </td>
    </tr>
@empty
    <tr><td colspan="7" class="text-center text-muted">{{ $emptyMessage ?? 'Silinen işlem logu bulunamadı.' }}</td></tr>
@endforelse

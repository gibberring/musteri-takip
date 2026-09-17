@forelse($musteriIletisimGuncellemeleri as $row)
    <tr>
        <td class="text-nowrap">{{ $row->created_at ? $row->created_at->format('d.m.Y H:i') : '-' }}</td>
        <td>#{{ $row->subject_id }}</td>
        <td>{{ $row->personel?->ad ?? '-' }}</td>
        <td class="small">{{ $row->summary }}</td>
    </tr>
@empty
    <tr><td colspan="4" class="text-center text-muted">Henüz kayıt yok.</td></tr>
@endforelse

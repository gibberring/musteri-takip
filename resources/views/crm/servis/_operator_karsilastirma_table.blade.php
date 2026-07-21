<div class="table-responsive">
    <table class="table table-striped mb-0">
        <thead>
            <tr>
                <th>Operatör</th>
                <th class="text-end">Oluşturduğu Servis</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row['ad'] }}</td>
                    <td class="text-end fw-bold">{{ number_format($row['count'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" class="text-center">Operatör bulunamadı.</td>
                </tr>
            @endforelse
        </tbody>
        @if(!empty($rows) && $totalCount > 0)
            <tfoot>
                <tr>
                    <th>Toplam</th>
                    <th class="text-end">{{ number_format($totalCount, 0, ',', '.') }}</th>
                </tr>
            </tfoot>
        @endif
    </table>
</div>

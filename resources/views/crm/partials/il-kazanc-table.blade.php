@if(empty($ilBazliKazanc))
    <p class="text-center text-muted mb-0">Kasa hareketi olan servis kaydı bulunamadı.</p>
@else
    <div class="table-responsive">
        <table class="table table-hover mb-0 il-kazanc-table">
            <thead>
                <tr>
                    <th>İL</th>
                    <th class="text-end">SERVİS</th>
                    <th class="text-end">TOPLAM GELİR</th>
                    <th class="text-end">TOPLAM GİDER</th>
                    <th class="text-end">TEKNİSYEN PAYI</th>
                    <th class="text-end">FİRMA KAZANCI</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ilBazliKazanc as $row)
                    <tr>
                        <td class="fw-semibold">{{ $row['il'] }}</td>
                        <td class="text-end">{{ $row['servis_sayisi'] }}</td>
                        <td class="text-end">{{ number_format($row['toplam_gelir'] ?? 0, 2, ',', '.') }} TL</td>
                        <td class="text-end text-danger">{{ number_format($row['toplam_gider'] ?? 0, 2, ',', '.') }} TL</td>
                        <td class="text-end text-primary">{{ number_format($row['teknisyen_payi'] ?? 0, 2, ',', '.') }} TL</td>
                        <td class="text-end fw-bold {{ ($row['firma_kazanc'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ number_format($row['firma_kazanc'] ?? 0, 2, ',', '.') }} TL
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

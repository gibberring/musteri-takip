@extends('crm.main') {{-- Ana layout'u extend ediyoruz --}}

@section('content') {{-- main.blade.php'deki @yield('content') kısmını dolduruyoruz --}}
<div class="container-fluid px-4">
    <h1 class="mt-4">Müşteriler</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Müşteri Listesi</li>
    </ol>
    
    {{-- Yeni Müşteri Ekle Butonu (Rota henüz tam işlevsel değil) --}}
    <div class="mb-3">
        <a href="{{ route('musteriler.create') }}" class="btn btn-primary">Yeni Müşteri Ekle</a>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Müşteri Kayıtları
        </div>
        <div class="card-body">
            <table id="datatablesSimple" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ad Soyad</th> {{-- Varsayılan kolonlar, modele göre güncellenmeli --}}
                        <th>Email</th>
                        <th>Telefon</th>
                        <th>Kategori</th> {{-- İlişki üzerinden kategori adı gösterilebilir --}}
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($musteriler as $musteri)
                    <tr>
                        <td>{{ $musteri->id }}</td>
                        <td>{{ $musteri->ad_soyad ?? '-' }}</td> {{-- 'ad_soyad' alanı varsa göster, yoksa - --}}
                        <td>{{ $musteri->email ?? '-' }}</td>
                        <td>{{ $musteri->telefon ?? '-' }}</td>
                        <td>{{ $musteri->kategori->ad ?? 'Belirtilmemiş' }}</td> {{-- Kategori ilişkisi üzerinden adını alıyoruz --}}
                        <td>
                            <a href="{{ route('musteriler.show', $musteri->id) }}" class="btn btn-sm btn-info">Göster</a>
                            <a href="{{ route('musteriler.edit', $musteri->id) }}" class="btn btn-sm btn-warning">Düzenle</a>
                            {{-- Silme işlemi için form (JavaScript ile onay isteyebilir) --}}
                            <form action="{{ route('musteriler.destroy', $musteri->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Bu müşteriyi silmek istediğinizden emin misiniz?')">Sil</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">Kayıtlı müşteri bulunamadı.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            
            {{-- Sayfalama Linkleri --}}
            <div class="d-flex justify-content-center">
                {{ $musteriler->links() }}
            </div>
        </div>
    </div>
</div>
@endsection 
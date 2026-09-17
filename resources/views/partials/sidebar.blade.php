<nav class="nxl-navigation">
    <div class="navbar-wrapper">
        <div class="m-header">
            <a href="{{ url('/panel') }}" class="b-brand">
                <!-- ========   change your logo hear   ============ -->
                <img src="{{ asset('crm_assets/images/logo-full.png') }}" alt="" class="logo logo-lg" />
                <img src="{{ asset('crm_assets/images/logo-abbr.png') }}" alt="" class="logo logo-sm" />
            </a>
        </div>
        <div class="navbar-content">
            <ul class="nxl-navbar">
                @php
                    $loggedInUser = Auth::user();
                    $tsrnTeknisyenPozisyonId = 1077; // TŞRN Teknisyen pozisyon ID'si
                    $operatorPozisyonId = 1073; // Operatör pozisyon ID'si
                    $idariIslerPozisyonId = 1076; // İdari İşler pozisyon ID'si
                    $muhasebePozisyonId = 1080; // Muhasebe pozisyon ID'si
                    $patronPozisyonId = 1071; // Patron pozisyon ID'si
                    $bekleyenOdemeCount = null;
                    $bekleyenServisCount = null;
                    $bugunkuIptalCount = null;
                    $bugunkuUlasilamadiCount = null;
                    if ($loggedInUser && !in_array($loggedInUser->poz_id, [$operatorPozisyonId, $idariIslerPozisyonId])) {
                        try {
                            $bekleyenSorgu = \App\Models\Kasa::where('gerceklesme', 0)
                                ->where(function($q) {
                                    $q->where('silindi', '!=', 1)
                                      ->orWhereNull('silindi');
                                });
                            if ($loggedInUser->poz_id == $tsrnTeknisyenPozisyonId) {
                                $bekleyenSorgu->forTahsilEden($loggedInUser->id);
                            }
                            $bekleyenOdemeCount = $bekleyenSorgu->count();
                        } catch (\Throwable $e) {
                            $bekleyenOdemeCount = null;
                        }
                    }
                    if ($loggedInUser && in_array((int) $loggedInUser->poz_id, [$patronPozisyonId, $muhasebePozisyonId, $operatorPozisyonId], true)) {
                        try {
                            $bekleyenServisCount = \App\Models\Servis::whereIn('servis_durum_id', [9097, 9334])
                                ->where(function($q) {
                                    $q->where('silindi', '!=', 1)
                                      ->orWhereNull('silindi');
                                })
                                ->count();
                        } catch (\Throwable $e) {
                            $bekleyenServisCount = null;
                        }
                    }
                    // Panel İptal kartı ile aynı: bugün created + durum 9104 + silinmemiş
                    if ($loggedInUser && !in_array($loggedInUser->poz_id, [$operatorPozisyonId, $tsrnTeknisyenPozisyonId, $idariIslerPozisyonId])) {
                        try {
                            $bugunkuIptalCount = \App\Models\Servis::where('servis_durum_id', 9104)
                                ->whereDate('created_at', \Carbon\Carbon::today())
                                ->where(function($q) {
                                    $q->where('silindi', '!=', 1)
                                      ->orWhereNull('silindi');
                                })
                                ->count();
                        } catch (\Throwable $e) {
                            $bugunkuIptalCount = null;
                        }
                        try {
                            $bugunkuUlasilamadiCount = \App\Models\Servis::where('servis_durum_id', 9102)
                                ->whereDate('created_at', \Carbon\Carbon::today())
                                ->where(function($q) {
                                    $q->where('silindi', '!=', 1)
                                      ->orWhereNull('silindi');
                                })
                                ->count();
                        } catch (\Throwable $e) {
                            $bugunkuUlasilamadiCount = null;
                        }
                    }
                @endphp
                <li class="nxl-item nxl-caption">
                    <label>MENÜ</label>
                </li>
                @if ($loggedInUser && !in_array($loggedInUser->poz_id, [$operatorPozisyonId, $tsrnTeknisyenPozisyonId, $idariIslerPozisyonId]))
                <li class="nxl-item {{ request()->is('panel*') ? 'active' : '' }}">
                    <a href="{{ url('/panel') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-home"></i></span>
                        <span class="nxl-mtext">GENEL BAKIŞ</span>
                    </a>
                </li>
                @endif
                @if ($loggedInUser && $loggedInUser->poz_id == $operatorPozisyonId)
                <li class="nxl-item {{ request()->is('personeller/' . $loggedInUser->id . '/operator-profil') ? 'active' : '' }}">
                    <a href="{{ url('/personeller/' . $loggedInUser->id . '/operator-profil') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-user"></i></span>
                        <span class="nxl-mtext">PROFİLİM</span>
                    </a>
                </li>
                @endif
                @if ($loggedInUser && !in_array($loggedInUser->poz_id, [$tsrnTeknisyenPozisyonId, $idariIslerPozisyonId]))
                <li class="nxl-item {{ request()->is('musteriler*') ? 'active' : '' }}">
                    <a href="{{ url('/musteriler') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-users"></i></span>
                        <span class="nxl-mtext">MÜŞTERİLER</span>
                    </a>
                </li>
                @endif
                @if ($loggedInUser && $loggedInUser->poz_id == $tsrnTeknisyenPozisyonId)
                <li class="nxl-item {{ request()->is('personeller/' . $loggedInUser->id . '/profil') ? 'active' : '' }}">
                    <a href="{{ url('/personeller/' . $loggedInUser->id . '/profil') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-user"></i></span>
                        <span class="nxl-mtext">PROFİLİM</span>
                    </a>
                </li>
                @endif
                <li class="nxl-item {{ request()->is('servisler*') && !request()->is('servisler/bekleyen-kayitlar') && !request()->is('servisler/bugunku-iptaller') && !request()->is('servisler/ulasilamayan-musteriler') && !request()->is('servisler/teknisyen-bakisi') ? 'active' : '' }}">
                    <a href="{{ url('/servisler') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-tool"></i></span>
                        <span class="nxl-mtext">SERVİSLER</span>
                    </a>
                </li>
                @if ($loggedInUser && in_array((int) $loggedInUser->poz_id, [$patronPozisyonId, $muhasebePozisyonId, $operatorPozisyonId], true))
                <li class="nxl-item {{ request()->is('servisler/teknisyen-bakisi') ? 'active' : '' }}">
                    <a href="{{ route('servisler.teknisyenBakisi') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-eye"></i></span>
                        <span class="nxl-mtext">TEKNİSYEN BAKIŞI</span>
                    </a>
                </li>
                @endif
                @if ($loggedInUser && in_array((int) $loggedInUser->poz_id, [$patronPozisyonId, $muhasebePozisyonId, $operatorPozisyonId], true))
                <li class="nxl-item {{ request()->is('servisler/bekleyen-kayitlar') ? 'active' : '' }}">
                    <a href="{{ url('/servisler/bekleyen-kayitlar') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-clock"></i></span>
                        <span class="nxl-mtext">
                            <span class="nxl-mtext-label">BEKLEYEN KAYITLAR</span>
                            @if(!empty($bekleyenServisCount))
                                <span class="badge bg-warning text-dark">{{ $bekleyenServisCount }}</span>
                            @endif
                        </span>
                    </a>
                </li>
                @endif
                @if ($loggedInUser && !in_array($loggedInUser->poz_id, [$operatorPozisyonId, $tsrnTeknisyenPozisyonId, $idariIslerPozisyonId]))
                <li class="nxl-item {{ request()->is('servisler/bugunku-iptaller') ? 'active' : '' }}">
                    <a href="{{ url('/servisler/bugunku-iptaller') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-x-circle"></i></span>
                        <span class="nxl-mtext">
                            <span class="nxl-mtext-label">BUGÜNKÜ İPTALLER</span>
                            @if(!empty($bugunkuIptalCount))
                                <span class="badge bg-danger">{{ $bugunkuIptalCount }}</span>
                            @endif
                        </span>
                    </a>
                </li>
                <li class="nxl-item {{ request()->is('servisler/ulasilamayan-musteriler') ? 'active' : '' }}">
                    <a href="{{ url('/servisler/ulasilamayan-musteriler') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-phone-off"></i></span>
                        <span class="nxl-mtext">
                            <span class="nxl-mtext-label">ULAŞILAMAYAN MÜŞTERİLER</span>
                            @if(!empty($bugunkuUlasilamadiCount))
                                <span class="badge bg-info">{{ $bugunkuUlasilamadiCount }}</span>
                            @endif
                        </span>
                    </a>
                </li>
                @endif
                @if ($loggedInUser && !in_array($loggedInUser->poz_id, [$tsrnTeknisyenPozisyonId, $operatorPozisyonId, $idariIslerPozisyonId]))
                <li class="nxl-item {{ request()->is('personeller*') ? 'active' : '' }}">
                    <a href="{{ url('/personeller') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-user"></i></span>
                        <span class="nxl-mtext">PERSONELLER</span>
                    </a>
                </li>
                @endif

                @if ($loggedInUser && !in_array($loggedInUser->poz_id, [$tsrnTeknisyenPozisyonId, $operatorPozisyonId, $idariIslerPozisyonId]))
                <li class="nxl-item {{ request()->is('genelkasa*') ? 'active' : '' }}">
                    <a href="{{ url('/genelkasa') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-dollar-sign"></i></span>
                        <span class="nxl-mtext">GENEL KASA</span>
                    </a>
                </li>
                @endif
                @if ($loggedInUser && !in_array($loggedInUser->poz_id, [$operatorPozisyonId, $idariIslerPozisyonId]))
                <li class="nxl-item {{ request()->is('kasa') ? 'active' : '' }}">
                    <a href="{{ url('/kasa') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-bar-chart-2"></i></span>
                        <span class="nxl-mtext">KASA</span>
                    </a>
                </li>
                <li class="nxl-item {{ request()->is('kasa/bekleyen-odemeler*') ? 'active' : '' }}">
                    <a href="{{ url('/kasa/bekleyen-odemeler') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-clock"></i></span>
                        <span class="nxl-mtext">
                            <span class="nxl-mtext-label">BEKLEYEN ÖDEMELER</span>
                            @if(!empty($bekleyenOdemeCount) && $bekleyenOdemeCount > 0)
                                <span class="badge bg-danger">{{ $bekleyenOdemeCount }}</span>
                            @endif
                        </span>
                    </a>
                </li>
                @endif
                {{-- Silinen Kayıtlar menüsü: Sadece Patron (1071) erişsin --}}
                @if ($loggedInUser && $loggedInUser->poz_id == 1071)
                @php
                    $silinenKayitlarAktif = request()->is('ayarlar/silinen-kayitlar*');
                @endphp
                <li class="nxl-item nxl-hasmenu {{ $silinenKayitlarAktif ? 'active nxl-trigger' : '' }}">
                    <a href="javascript:void(0);" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-trash-2"></i></span>
                        <span class="nxl-mtext">SİLİNEN KAYITLAR</span>
                        <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                    </a>
                    <ul class="nxl-submenu">
                        <li class="nxl-item {{ request()->is('ayarlar/silinen-kayitlar/servis*') ? 'active' : '' }}">
                            <a class="nxl-link" href="{{ route('settings.deletedRecords.servis') }}">Servis</a>
                        </li>
                        <li class="nxl-item {{ request()->is('ayarlar/silinen-kayitlar/kasa*') ? 'active' : '' }}">
                            <a class="nxl-link" href="{{ route('settings.deletedRecords.kasa') }}">Kasa</a>
                        </li>
                        <li class="nxl-item {{ request()->is('ayarlar/silinen-kayitlar/diger*') ? 'active' : '' }}">
                            <a class="nxl-link" href="{{ route('settings.deletedRecords.diger') }}">Diğer</a>
                        </li>
                    </ul>
                </li>
                @endif
                {{-- Ayarlar menüsü: Sadece Patron (1071) erişsin (ileride genişletilebilir) --}}
                @if ($loggedInUser && $loggedInUser->poz_id == 1071)
                <li class="nxl-item {{ request()->is('ayarlar*') && !request()->is('ayarlar/silinen-kayitlar*') ? 'active' : '' }}">
                    <a href="{{ url('/ayarlar') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-settings"></i></span>
                        <span class="nxl-mtext">AYARLAR</span>
                    </a>
                </li>
                @endif

                {{-- Mevcut menünün geri kalanı şimdilik kaldırıldı, istenirse diğerleri de eklenebilir. --}}
                {{-- Eğer diğer menü elemanları da isteniyorsa, buraya eklenebilir. --}}
            </ul>
            {{--
            <div class="card text-center">
                <div class="card-body">
                    <i class="feather-sunrise fs-4 text-dark"></i>
                    <h6 class="mt-4 text-dark fw-bolder">Downloading Center</h6>
                    <p class="fs-11 my-3 text-dark">Duralux is a production ready CRM to get started up and running easily.</p>
                    <a href="javascript:void(0);" class="btn btn-primary text-dark w-100">Download Now</a>
                </div>
            </div>
            --}}
        </div>
    </div>
</nav> 
<style>
    .nxl-navigation .navbar-content .nxl-link {
        display: flex;
        align-items: center;
        white-space: nowrap;
        padding: 10px 12px;
    }
    .nxl-navigation .navbar-content .nxl-micon {
        flex-shrink: 0;
        margin-right: 8px;
    }
    .nxl-navigation .navbar-content .nxl-mtext {
        font-size: 0.72rem;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        min-width: 0;
        white-space: nowrap;
    }
    .nxl-navigation .navbar-content .nxl-mtext .badge {
        flex-shrink: 0;
        font-size: 0.65rem;
        padding: 0.15em 0.4em;
        line-height: 1.2;
    }
    html.minimenu .nxl-navigation:hover .navbar-content .nxl-mtext {
        display: inline-flex;
    }
    .nxl-navbar .nxl-caption label { font-size: 0.65rem; }
</style>
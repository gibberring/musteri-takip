<header class="nxl-header">
    <div class="header-wrapper">
        <!--! [Start] Header Left !-->
        <div class="header-left d-flex align-items-center gap-3">
            <a href="{{ url('/panel') }}" class="b-brand d-flex align-items-center d-lg-none">
                <img src="{{ asset('crm_assets/images/logo-abbr.png') }}" alt="" class="logo logo-sm header-logo-sm" />
            </a>
            <!--! [Start] nxl-head-mobile-toggler !-->
            <a href="javascript:void(0);" class="nxl-head-mobile-toggler" id="mobile-collapse">
                <div class="hamburger hamburger--arrowturn">
                    <div class="hamburger-box">
                        <div class="hamburger-inner"></div>
                    </div>
                </div>
            </a>
            <!--! [Start] nxl-head-mobile-toggler !-->
            <!--! [Start] nxl-navigation-toggle !-->

            <!--! [End] nxl-navigation-toggle !-->
            <!--! [Start] nxl-lavel-mega-menu-toggle !-->
      
            <!--! [End] nxl-lavel-mega-menu-toggle !-->
            <!--! [Start] nxl-lavel-mega-menu !-->
            <!--! [End] nxl-lavel-mega-menu !-->
        </div>
        <!--! [End] Header Left !-->
        <!--! [Start] Header Right !-->
        <div class="header-right ms-auto">
            <div class="d-flex align-items-center">
                {{-- Bu kısımdaki dil seçimi, arama, tam ekran, tema, saat, bildirim vb. ikonlar main.blade.php'den kopyalandı. --}}
                {{-- İhtiyaca göre bu ikonlar ve dropdown içerikleri dinamik hale getirilebilir veya sadeleştirilebilir. --}}
     
                {{-- Timesheets dropdown kaldırıldı --}}
                <div class="dropdown nxl-h-item" id="duyurularDropdown">
                    <a class="nxl-head-link me-3" data-bs-toggle="dropdown" href="#" role="button" data-bs-auto-close="outside">
                        <i class="feather-bell"></i>
                        <span class="badge bg-danger nxl-h-badge" id="duyuruBadge">0</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end nxl-h-dropdown nxl-notifications-menu" style="min-width: 320px;">
                        <div class="d-flex justify-content-between align-items-center notifications-head">
                            <h6 class="fw-bold text-dark mb-0">Duyurular</h6>
                        </div>
                        <div class="px-3 py-2" id="duyurularIcerik" style="max-height: 420px; overflow: auto;">
                            <div class="text-muted fs-12">Yükleniyor...</div>
                        </div>
                    </div>
                </div>
                <div class="dropdown nxl-h-item">
                    @php
                        $__user = Auth::user();
                        $__pozId = $__user->poz_id ?? null;
                        $__avatarFile = null;
                        if (in_array($__pozId, [1071, 1080])) { $__avatarFile = 'boss.png'; }
                        elseif (in_array($__pozId, [1074, 1077])) { $__avatarFile = '1.png'; }
                        elseif (in_array($__pozId, [1073, 1076])) { $__avatarFile = 'call.png'; }
                        $__avatarUrl = $__avatarFile ? asset('crm_assets/images/avatar/' . $__avatarFile) : ($__user->profile_photo_url ?? asset('crm_assets/images/avatar/1.png'));
                    @endphp
                    <a href="javascript:void(0);" data-bs-toggle="dropdown" role="button" data-bs-auto-close="outside">
                        <img src="{{ $__avatarUrl }}" alt="user-image" class="img-fluid user-avtar me-0" />
                    </a>
                    <div class="dropdown-menu dropdown-menu-end nxl-h-dropdown nxl-user-dropdown">
                        <div class="dropdown-header">
                            <div class="d-flex align-items-center">
                                <img src="{{ $__avatarUrl }}" alt="user-image" class="img-fluid user-avtar" />
                                <div>
                                    <h6 class="text-dark mb-0">{{ Auth::user()->name ?? (Auth::user()->ad ?? 'Kullanıcı') }}</h6>
                                    @php
                                        $__pozAd = null;
                                        try {
                                            $__pozAd = \DB::table('tnm_personel_pozisyon')->where('id', $__pozId)->value('ad');
                                        } catch (\Throwable $e) { $__pozAd = null; }
                                    @endphp
                                    <span class="fs-12 fw-medium text-muted">{{ $__pozAd ?? '' }}</span>
                                </div>
                            </div>
                        </div>
                        {{-- Kullanıcı durumu menüsü kaldırıldı --}}
                        @if ($__user && $__pozId == 1077)
                        <a href="{{ url('/personeller/' . $__user->id . '/profil') }}" class="dropdown-item">
                            <i class="feather-user"></i>
                            <span>Profilim</span>
                        </a>
                        @elseif ($__user && $__pozId == 1073)
                        <a href="{{ url('/personeller/' . $__user->id . '/operator-profil') }}" class="dropdown-item">
                            <i class="feather-user"></i>
                            <span>Profilim</span>
                        </a>
                        @else
                        <a href="#" class="dropdown-item">
                            <i class="feather-user"></i>
                            <span>Kullanıcı Profili</span>
                        </a>
                        @endif
                        {{-- Account Settings kaldırıldı --}}
                        <div class="dropdown-divider"></div>
                        <a href="{{ route('logout') }}"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                           class="dropdown-item">
                            <i class="feather-log-out"></i>
                            <span>Çıkış</span>
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!--! [End] Header Right !-->
    </div>
</header> 
<script>
document.addEventListener('DOMContentLoaded', function(){
  const dropdownEl = document.getElementById('duyurularDropdown');
  if (!dropdownEl) return;
  const badge = document.getElementById('duyuruBadge');
  const container = document.getElementById('duyurularIcerik');
  function render(groups){
    let html = '';
    function section(title, items){
      let sec = '<div class="mb-2"><div class="fw-semibold text-muted small mb-1">' + title + '</div>';
      if (!items || items.length===0) sec += '<div class="text-muted fs-12">Kayıt yok</div>';
      else {
        function fmtDate(v){ if(!v) return ''; try { var s=String(v).replace(' ','T'); var d=new Date(s); if(isNaN(d)) return ''; return d.toLocaleString('tr-TR',{ day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit' }); } catch(e){ return ''; } }
        items.forEach(function(it){
          sec += '<div class="border-bottom py-1 d-flex align-items-start gap-2">'
              + '<div class="flex-grow-1">'
              + (function(){ var tarih=fmtDate(it.published_at); return '<div>'
                  + '<span class="fw-semibold ' + (it.okundu? 'text-muted' : 'text-dark') + '">' + (it.baslik||'') + '</span>'
                  + (tarih? ' <span class="text-muted ms-2 fs-10 fw-light">'+tarih+'</span>' : '')
                  + '</div>'; })()
              + '<div class="fs-12 text-muted" style="white-space: pre-wrap;">' + (it.icerik||'') + '</div>'
              + '</div>'
              + '<button class="btn btn-sm ' + (it.okundu? 'btn-light' : 'btn-success') + ' duyuru-okundu-btn" data-id="' + it.id + '">' + (it.okundu? 'Okundu' : 'Okundu İşaretle') + '</button>'
              + '</div>';
        });
      }
      sec += '</div>';
      return sec;
    }
    var added = false;
    if (groups && Array.isArray(groups['TEKNISYEN']) && groups['TEKNISYEN'].length) {
      html += section('TEKNİSYEN DUYURULARI', groups['TEKNISYEN']);
      added = true;
    }
    if (groups && Array.isArray(groups['OPERATOR']) && groups['OPERATOR'].length) {
      html += section('OPERATÖR DUYURULARI', groups['OPERATOR']);
      added = true;
    }
    if (!added) { html = '<div class="text-muted fs-12">Duyuru bulunmuyor</div>'; }
    container.innerHTML = html;
  }
  function load(){
    fetch('/duyurular/son', { headers: { 'Accept': 'application/json' }})
      .then(r=>r.json())
      .then(d=>{ if (d){ if (badge) badge.textContent = d.unread||0; render(d.groups||{}); } })
      .catch(()=>{ if (container) container.innerHTML = '<div class="text-danger fs-12">Duyurular yüklenemedi.</div>'; });
  }
  dropdownEl.addEventListener('show.bs.dropdown', load);
  // Yedek tetikleyiciler: tıklandığında ve sayfa yüklenince rozeti güncelle
  const toggle = dropdownEl.querySelector('[data-bs-toggle="dropdown"]');
  if (toggle) { toggle.addEventListener('click', function(){ setTimeout(load, 0); }); }
  // İlk yüklemede rozeti güncelle
  load();
  document.addEventListener('click', function(e){
    const btn = e.target && e.target.closest('.duyuru-okundu-btn');
    if (!btn) return;
    const id = btn.getAttribute('data-id');
    if (!id) return;
    fetch('/duyurular/' + id + '/okundu', { method:'POST', headers:{'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')} })
      .then(r=>r.json()).then(()=> load());
  });
});
</script>
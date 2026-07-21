<!DOCTYPE html>
<html lang="tr"> <!-- Dil tr olarak ayarlandı -->

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="keyword" content="">
    <meta name="author" content="">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Yeni Müşteri Ekle</title>
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('crm_assets/images/favicon.ico') }}">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" type="text/css" href="{{ asset('crm_assets/css/bootstrap.min.css') }}">
    <!-- Vendors CSS -->
    <link rel="stylesheet" type="text/css" href="{{ asset('crm_assets/vendors/css/vendors.min.css') }}">
    <!-- Custom CSS -->
    <link rel="stylesheet" type="text/css" href="{{ asset('crm_assets/css/theme.min.css') }}">
    <!--[if lt IE 9]>
        <script src="https:oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
        <script src="https:oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <style>
        /* Gerekirse özel stiller buraya eklenebilir */
        .form-check-label { margin-left: 0.25rem; }
        #vergiAlanlari { display: none; } /* Başlangıçta gizli */
    </style>
</head>

<body>
    <!--! Navigation Manu (proposal.blade.php'den alınabilir veya layout kullanılabilir) !-->
    {{-- @include('partials.navigation') veya layout kullanılıyorsa gerek yok --}}
     <nav class="nxl-navigation"> /* Örnek Navigasyon */
        <div class="navbar-wrapper"> <div class="m-header"> <a href="#" class="b-brand"> <img src="{{ asset('crm_assets/images/logo-full.png') }}" alt="" class="logo logo-lg"> <img src="{{ asset('crm_assets/images/logo-abbr.png') }}" alt="" class="logo logo-sm"> </a> </div> <div class="navbar-content"> <ul class="nxl-navbar"> <li class="nxl-item nxl-caption"> <label>MENU</label> </li> <li class="nxl-item"><a href="{{ route('panel') }}" class="nxl-link"><span class="nxl-micon"><i class="feather-airplay"></i></span><span class="nxl-mtext">Ana Sayfa</span></a></li> <li class="nxl-item"><a href="{{ route('servisler.index') }}" class="nxl-link"><span class="nxl-micon"><i class="feather-layout"></i></span><span class="nxl-mtext">Servisler</span></a></li> <li class="nxl-item"><a href="{{ route('musteriler.index') }}" class="nxl-link"><span class="nxl-micon"><i class="feather-users"></i></span><span class="nxl-mtext">Müşteriler</span></a></li> </ul> </div> </div>
     </nav>
    <!--! Header (proposal.blade.php'den alınabilir veya layout kullanılabilir) !-->
    {{-- @include('partials.header') veya layout kullanılıyorsa gerek yok --}}
    <header class="nxl-header"> <div class="header-wrapper"> <div class="header-left d-flex align-items-center gap-4"> <a href="javascript:void(0);" class="nxl-head-mobile-toggler" id="mobile-collapse"> <div class="hamburger hamburger--arrowturn"> <div class="hamburger-box"> <div class="hamburger-inner"></div> </div> </div> </a> </div> <div class="header-right ms-auto"> <div class="d-flex align-items-center"> {{-- Diğer header ikonları --}} <div class="dropdown nxl-h-item"> <a href="javascript:void(0);" data-bs-toggle="dropdown" role="button" data-bs-auto-close="outside"> <img src="{{ asset('crm_assets/images/avatar/1.png') }}" alt="user-image" class="img-fluid user-avtar me-0"> </a> <div class="dropdown-menu dropdown-menu-end nxl-h-dropdown nxl-user-dropdown"> {{-- Kullanıcı menüsü içeriği --}} <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="dropdown-item"> <i class="feather-log-out"></i> <span>Logout</span> </a> <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;"> @csrf </form> </div> </div> </div> </div> </div> </header>

    <!--! Main Content !-->
    <main class="nxl-container">
        <div class="nxl-content">
            <!-- Page Header -->
            <div class="page-header">
                <div class="page-header-left d-flex align-items-center">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Yeni Müşteri Ekle</h5>
                    </div>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Ana Sayfa</a></li>
                        <li class="breadcrumb-item">Müşteriler</li>
                        <li class="breadcrumb-item">Yeni Müşteri</li>
                    </ul>
                </div>
            </div>
            <!-- End Page Header -->

            <!-- Main Content -->
            <div class="main-content">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card stretch stretch-full">
                            <div class="card-header">
                                <h5 class="card-title">Müşteri Bilgileri</h5>
                            </div>
                            <div class="card-body">
                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <ul class="mb-0">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <form action="{{ route('musteriler.store') }}" method="POST">
                                    @csrf
                                    <div class="row g-3">
                                        <!-- Müşteri Tipi -->
                                        <div class="col-md-12">
                                            <label class="form-label">Müşteri Tipi <span class="text-danger">*</span></label>
                                            <div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="musteri_tip" id="tipBireysel" value="0" checked>
                                                    <label class="form-check-label" for="tipBireysel">Bireysel</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="musteri_tip" id="tipKurumsal" value="1" {{ old('musteri_tip') == '1' ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="tipKurumsal">Kurumsal</label>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Kayıt Tarihi ve Saati -->
                                        <div class="col-md-6">
                                            <label for="kayit_tarihi" class="form-label">Kayıt Tarihi <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" id="kayit_tarihi" name="kayit_tarihi" value="{{ old('kayit_tarihi', date('Y-m-d')) }}" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="kayit_saati" class="form-label">Kayıt Saati <span class="text-danger">*</span></label>
                                            <input type="time" class="form-control" id="kayit_saati" name="kayit_saati" value="{{ old('kayit_saati', date('H:i')) }}" required>
                                        </div>

                                        <!-- Müşteri Adı -->
                                        <div class="col-12">
                                            <label for="ad" class="form-label">Müşteri Adı / Firma Adı <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="ad" name="ad" value="{{ old('ad') }}" placeholder="Ad Soyad veya Firma Adı" required>
                                        </div>

                                        <!-- Telefonlar -->
                                        <div class="col-md-6">
                                            <label for="tel1" class="form-label">Telefon 1 <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="tel1" name="tel1" value="{{ old('tel1') }}" placeholder="Örn: 5xxxxxxxxx" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="tel2" class="form-label">Telefon 2</label>
                                            <input type="text" class="form-control" id="tel2" name="tel2" value="{{ old('tel2') }}">
                                        </div>

                                        <!-- İl ve İlçe -->
                                        <div class="col-md-6">
                                            <label for="il_id" class="form-label">İl <span class="text-danger">*</span></label>
                                            <select class="form-select" id="il_id" name="il_id" required>
                                                <option value="" selected disabled>İl Seçiniz...</option>
                                                @isset($iller)
                                                    @foreach($iller as $il)
                                                        <option value="{{ $il->id }}" {{ old('il_id') == $il->id ? 'selected' : '' }}>{{ $il->ad }}</option>
                                                    @endforeach
                                                @endisset
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="ilce_id" class="form-label">İlçe <span class="text-danger">*</span></label>
                                            <select class="form-select" id="ilce_id" name="ilce_id" required disabled>
                                                <option value="" selected disabled>Önce İl Seçiniz...</option>
                                                {{-- İlçeler AJAX ile doldurulacak --}}
                                            </select>
                                        </div>

                                        <!-- Adres -->
                                        <div class="col-12">
                                            <label for="adres" class="form-label">Adres <span class="text-danger">*</span></label>
                                            <textarea class="form-control" id="adres" name="adres" rows="3" required>{{ old('adres') }}</textarea>
                                        </div>

                                        <!-- Vergi Alanları (Kurumsal seçilince görünecek) -->
                                        <div id="vergiAlanlari" class="row g-3" style="display: {{ old('musteri_tip') == '1' ? 'flex' : 'none' }};">
                                            <div class="col-md-6">
                                                <label for="vdaire" class="form-label">Vergi Dairesi</label>
                                                <input type="text" class="form-control" id="vdaire" name="vdaire" value="{{ old('vdaire') }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="vno" class="form-label">Vergi Numarası</label>
                                                <input type="text" class="form-control" id="vno" name="vno" value="{{ old('vno') }}">
                                            </div>
                                        </div>

                                        <!-- Kaydet Butonu -->
                                        <div class="col-12 text-end">
                                            <button type="submit" class="btn btn-primary">Kaydet</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Main Content -->
        </div>

        <!--! Footer (proposal.blade.php'den alınabilir veya layout kullanılabilir) !-->
        {{-- @include('partials.footer') veya layout kullanılıyorsa gerek yok --}}
        <footer class="footer"> <p class="fs-11 text-muted fw-medium text-uppercase mb-0 copyright"> <span>Copyright ©</span> <script> document.write(new Date().getFullYear()); </script> </p> <div class="d-flex align-items-center gap-4"> <a href="javascript:void(0);" class="fs-11 fw-semibold text-uppercase">Help</a> <a href="javascript:void(0);" class="fs-11 fw-semibold text-uppercase">Terms</a> <a href="javascript:void(0);" class="fs-11 fw-semibold text-uppercase">Privacy</a> </div> </footer>
    </main>

    <!--! Footer Script !-->
    <!-- Vendors JS -->
    <script src="{{ asset('crm_assets/vendors/js/vendors.min.js') }}"></script>
    <!-- Common JS -->
    <script src="{{ asset('crm_assets/js/common-init.min.js') }}"></script>
    <!-- Page Specific JS -->
    <script src="{{ asset('crm_assets/js/musteri-create.js') }}" defer></script>
    <script>
        // Eski veriyi ve hataları JavaScript'te kullanılabilir hale getir
        window.formOldData = @json(session()->getOldInput());
        window.formErrors = @json($errors->getMessages());
        window.selectedIlId = "{{ old('il_id') }}";
        window.selectedIlceId = "{{ old('ilce_id') }}";
    </script>
</body>
</html> 
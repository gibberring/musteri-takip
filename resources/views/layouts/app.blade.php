<!DOCTYPE html>
<html lang="tr" translate="no" class="notranslate">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="google" content="notranslate" />
    <meta name="description" content="@yield('page_description', 'CRM Uygulaması')" />
    <meta name="keyword" content="@yield('page_keyword', 'crm, musteri, takip')" />
    <meta name="author" content="WRAPCODERS" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Anasayfa') - Servis Takip</title>

    <!--! BEGIN: Favicon-->
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('crm_assets/images/favicon.ico') }}" />
    <!--! END: Favicon-->

    <!--! BEGIN: Bootstrap CSS-->
    <link rel="stylesheet" type="text/css" href="{{ asset('crm_assets/css/bootstrap.min.css') }}" />
    <!--! END: Bootstrap CSS-->

    <!--! BEGIN: Vendors CSS -->
    <link rel="stylesheet" type="text/css" href="{{ asset('crm_assets/vendors/css/vendors.min.css') }}" />
    <!--! END: Vendors CSS-->

    <!--! BEGIN: Custom CSS (Genel tema dosyanız) -->
    <link rel="stylesheet" type="text/css" href="{{ asset('crm_assets/css/theme.min.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('crm_assets/css/custom.css') }}" />
    <!--! END: Custom CSS-->

    {{-- MERKEZİ ÖZEL STİLLERİNİZ BURAYA TAŞINDI --}}
    <style>
        :root {
            --bs-modal-padding: 13px !important; /* Tüm modallar için iç padding 13px olarak güncellendi */
        }

        /* Yapışkan Altbilgi İçin CSS Başlangıcı */
        html, body {
            height: 100%;
            margin: 0;
        }
        .nxl-container {
            display: flex;
            flex-direction: column;
            min-height: 100vh; /* Ekran yüksekliğinin tamamını kapla */
        }
        .nxl-content {
            flex-grow: 1; /* Kalan boş alanı doldur */
        }
        /* Yapışkan Altbilgi İçin CSS Sonu */

        .nxl-navigation .logo-lg,
        .nxl-navigation .logo-sm {
            max-height: 36px;
            width: auto;
        }
        .header-logo-sm {
            max-height: 24px;
            width: auto;
        }

        /* Servis Detay Modalı - İşlem Logları ve Para Hareketleri Stilleri */
        #servisDetayModal .card-header h6 { /* Bölüm Başlıkları */
            font-family: 'Inter', sans-serif;
            font-size: 13px;
            font-weight: 600;
            color: #283c50; 
            line-height: 1.6;
        }

        /* Servis Detay Modalı - İşlem Logları ve Para Hareketleri Tablo İçerik Stilleri (th dahil edildi) */
        #servisDetayModal #modalIslemLoglariBody td,
        #servisDetayModal #modalKasaHareketleriBody td,
        #servisDetayModal #modalIslemLoglariBody th, 
        #servisDetayModal #modalKasaHareketleriBody th,
        #servisDetayModal #servisYapilanIslemlerDv table thead th, /* Birleştirildi */
        #servisDetayModal #kasaGosterDiv table thead th { /* Birleştirildi */
            font-family: 'Inter', sans-serif;
            font-size: 11px; /* Font boyutu küçültüldü */
            font-weight: 500; 
            color: #6b7885;
            line-height: 1.3;   /* Satır yüksekliği azaltıldı */
            padding-top: 0.15rem;    /* Dikey padding azaltıldı */
            padding-bottom: 0.15rem; /* Dikey padding azaltıldı */
            padding-left: 0.4rem;    
            padding-right: 0.4rem;   
            vertical-align: middle; 
        }

        /* Tablo Başlıkları (th) için font-weight ve color tekrar ayarlanıyor */
        #servisDetayModal #modalIslemLoglariBody th,
        #servisDetayModal #modalKasaHareketleriBody th,
        #servisDetayModal #servisYapilanIslemlerDv table thead th,
        #servisDetayModal #kasaGosterDiv table thead th {
            font-weight: 600; 
            color: #283c50;   
        }

        /* Servis Detay Modalı - Durum Güncelleme Select Kutusu Stilleri */
        #servisDetayModal #modalDurumGuncelleSelect {
            font-family: 'Roboto', sans-serif;
            font-size: 0.75rem;     /* 12px */
            height: auto;
            padding-top: 0.3rem;    /* Yaklaşık 4.8px */
            padding-bottom: 0.3rem; /* Yaklaşık 4.8px */
            padding-left: 0.5rem;   /* Yatay padding */
            padding-right: 0.5rem;  /* Yatay padding (ok ikonu için yer bırakır) */
            line-height: 1.4;       /* Biraz artırıldı */
            background-position: right 0.5rem center; /* Ok ikonu pozisyonu */
        }

        /* Servis Detay Modalı - Müşteri, Cihaz, Servis Bilgileri Veri Font Boyutu */
        #servisDetayModal #modalMusteriAd,
        #servisDetayModal #modalMusteriTel,
        #servisDetayModal #modalMusteriAdres,
        #servisDetayModal #modalVergiDairesi,
        #servisDetayModal #modalVergiNo,
        #servisDetayModal #modalOperatorNotu, /* Servis Bilgileri içinde */
        #servisDetayModal #modalMarkaAd,
        #servisDetayModal #modalCihazTuruAd,
        #servisDetayModal #modalCihazModel,
        #servisDetayModal #modalSeriNo,
        #servisDetayModal #modalCihazAriza {
            font-size: 11px !important; /* Font boyutu küçültüldü, !important eklendi */
        }
        #modalMusteriAdresLink {
            text-decoration: none;
            color: inherit;
        }
        #modalMusteriAdresLink.is-mobile {
            color: #3454d1;
            cursor: pointer;
            font-size: 1.6rem;
        }
        #modalMusteriAdresLink.is-mobile i {
            font-size: 1.6rem;
        }

        #servisDetayModal .form-label.small.text-muted { /* Bilgi Etiketleri */
            font-size: 10px !important; /* Font boyutu küçültüldü, !important eklendi */
        }

        /* Servis Detay Modalı - Footer Buton Yüksekliğini ve Genişliğini Azaltma */
        #servisDetayModal .modal-footer .btn {
            padding-top: 0.3rem;     /* Dikey padding biraz artırıldı */
            padding-bottom: 0.3rem;  /* Dikey padding biraz artırıldı */
            padding-left: 0.4rem;    /* Yatay padding */
            padding-right: 0.4rem;   /* Yatay padding */
            /* font-size Bootstrap varsayılanını kullanacak */
        }

        #servisDetayModal .modal-footer .btn i.feather {
            vertical-align: middle; /* Dikey ortalama için */
             /* font-size varsayılanını kullanacak */
        }

        /* Yeni Servis Modalı - Footer Buton Yüksekliğini ve Genişliğini Ayarlama */
        #yeniServisModal .modal-footer .btn {
            padding-top: 0.3rem;     
            padding-bottom: 0.3rem;  
            padding-left: 0.4rem;    
            padding-right: 0.4rem;   
            /* font-size Bootstrap varsayılanını kullanacak */
            text-transform: none; /* Türkçe büyük harf bozulmasını engelle */
        }

        /* Yeni Servis Modalı - Footer Alanının Yüksekliğini Azaltma */
        #yeniServisModal .modal-footer {
            padding-top: 0.5rem;    /* Varsayılan modal padding'den (13px) daha az */
            padding-bottom: 0.5rem; /* Varsayılan modal padding'den (13px) daha az */
        }

        /* Müşteri Detay Modalı - Footer Buton Yüksekliğini ve Genişliğini Azaltma */
        #musteriDetayModal .modal-footer .btn {
            padding-top: 0.3rem;
            padding-bottom: 0.3rem;
            padding-left: 0.4rem;
            padding-right: 0.4rem;
        }

        #musteriDetayModal .modal-footer .btn i.feather { 
            vertical-align: middle;
        }

        /* Müşteri Detay Modalı - Footer Alanının Yüksekliğini Azaltma */
        #musteriDetayModal .modal-footer {
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }

        /* Yeni Müşteri (index'teki) Modalı - Footer Buton Yüksekliğini ve Genişliğini Azaltma */
        #yeniMusteriModal .modal-footer .btn { /* musteri/index.blade.php içindeki modal */
            padding-top: 0.3rem;
            padding-bottom: 0.3rem;
            padding-left: 0.4rem;
            padding-right: 0.4rem;
        }

        #yeniMusteriModal .modal-footer .btn i.feather { 
            vertical-align: middle;
        }

        /* Yeni Müşteri (index'teki) Modalı - Footer Alanının Yüksekliğini Azaltma */
        #yeniMusteriModal .modal-footer { /* musteri/index.blade.php içindeki modal */
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }

    </style>

    {{-- Sayfaya özel CSS dosyaları buraya (@push) yüklenir ve yukarıdaki merkezi stilleri ezebilir --}}
    @stack('page_specific_css')

    {{-- Kalın scrollbar (tüm sayfalar, masaüstü) – en son yüklensin diye burada --}}
    <style>
    @media (min-width: 992px) and (pointer: fine) {
      html::-webkit-scrollbar,
      body::-webkit-scrollbar,
      .nxl-container::-webkit-scrollbar,
      .nxl-content::-webkit-scrollbar,
      main::-webkit-scrollbar,
      .main-content::-webkit-scrollbar {
        width: 24px !important;
      }
      html::-webkit-scrollbar-track,
      body::-webkit-scrollbar-track,
      .nxl-container::-webkit-scrollbar-track,
      .nxl-content::-webkit-scrollbar-track,
      main::-webkit-scrollbar-track,
      .main-content::-webkit-scrollbar-track {
        background: #f1f1f1 !important;
      }
      html::-webkit-scrollbar-thumb,
      body::-webkit-scrollbar-thumb,
      .nxl-container::-webkit-scrollbar-thumb,
      .nxl-content::-webkit-scrollbar-thumb,
      main::-webkit-scrollbar-thumb,
      .main-content::-webkit-scrollbar-thumb {
        background: #adb5bd !important;
        border-radius: 10px !important;
        border: 5px solid #f1f1f1 !important;
        min-height: 80px !important;
      }
      html::-webkit-scrollbar-thumb:hover,
      body::-webkit-scrollbar-thumb:hover,
      .nxl-container::-webkit-scrollbar-thumb:hover,
      .nxl-content::-webkit-scrollbar-thumb:hover,
      main::-webkit-scrollbar-thumb:hover,
      .main-content::-webkit-scrollbar-thumb:hover {
        background: #6c757d !important;
      }
      html, body, .nxl-container, .nxl-content, main, .main-content {
        scrollbar-width: auto !important;
      }
    }
    </style>

    <!--! HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries !-->
    <!--! WARNING: Respond.js doesn"t work if you view the page via file: !-->
    <!--[if lt IE 9]>
        <script src="https:oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
        <script src="https:oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body class="notranslate @yield('body_class', '')" translate="no">
    <!--! ================================================================ !-->
    <!--! COMMON Navigation Manu START !-->
    <!--! ================================================================ !-->
    @include('partials.sidebar')
    <!--! ================================================================ !-->
    <!--! COMMON Navigation Manu END !-->
    <!--! ================================================================ !-->

    <!--! ================================================================ !-->
    <!--! COMMON Header START !-->
    <!--! ================================================================ !-->
    @include('partials.header')
    <!--! ================================================================ !-->
    <!--! COMMON Header END !-->
    <!--! ================================================================ !-->

    <!--! ================================================================ !-->
    <!--! [Start] Main Content !-->
    <!--! ================================================================ !-->
    <main class="nxl-container">
        <div class="nxl-content">
            @yield('content') {{-- Sayfaya özgü içerik buraya gelecek --}}
        </div>

        <!-- [ Footer ] start -->
        <footer class="footer">
            <p class="fs-11 text-muted fw-medium mb-0 copyright">
                <span>Copyright ©</span>
                <script>
                    document.write(new Date().getFullYear());
                </script>
                <span class="ms-2">Laravel 10 ve <i class="feather-coffee" style="font-size: 1.1em; vertical-align: middle;"></i> ile kodlanmıştır.</span>
            </p>
            
        </footer>
        <!-- [ Footer ] end -->
    </main>
    <!--! ================================================================ !-->
    <!--! [End] Main Content !-->
    <!--! ================================================================ !-->

    {{-- Müşteri/Servis Detay Modalı (Tüm sayfalarda ortak kullanılır) --}}
    @include('partials.modals.servis-detay')
    {{-- Global: Ek modallar (tüm sayfalarda kullanılabilir) --}}
    @include('partials.modals.duzenle-islem-log')
    @include('partials.modals.servis-fisi')
    @include('partials.modals.servis-fisi-whatsapp')
    @include('partials.modals.imza')
    {{-- Ek modallar için sayfa bazlı alan --}}
    @yield('modals')


    <!--! ================================================================ !-->
    <!--! Footer Script !-->
    <!--! ================================================================ !-->
    <!--! BEGIN: Vendors JS !-->
    <script src="{{ asset('crm_assets/vendors/js/vendors.min.js') }}"></script>
    {{-- Sayfaya özel vendor JS dosyaları (jQuery ve Bootstrap sonrası, genel init öncesi) --}}
    @stack('page_specific_vendor_js')
    <!--! END: Vendors JS !-->

    <!--! BEGIN: Apps Init  !-->
    <script src="{{ asset('crm_assets/js/common-init.min.js') }}"></script>
    {{-- Genel Apps Init sonrası, genel veri bloğu --}}
    <script>
        window.crmData = {
            servisDurumlar: @json($servisDurumlar ?? []),
            servisDurumSorular: @json($servisDurumSorular ?? []),
            personeller: @json($personeller ?? []),
            markalar: @json($markalar ?? []),
            cihazTurleri: @json($cihazTurleri ?? []),
            loggedInUserPozId: @json(optional(optional(auth()->user())->pozisyon)->id ?? (optional(auth()->user())->pozisyon_id ?? null)),
            loggedInUserId: @json(optional(auth()->user())->id),
            permOverrides: @json(\App\Models\RoleAbility::all()->groupBy('role_id')->map(function($items){return $items->pluck('allowed','ability');}))
        };
        // Uretimde console loglarini sustur
        if (!@json(config('app.debug'))) {
            window.console = window.console || {};
            ['log', 'debug', 'info', 'warn', 'error'].forEach(function(method) {
                window.console[method] = function() {};
            });
        }
        // Sayfaya özel ek script verileri veya anlık scriptler için
        @stack('page_specific_data_scripts')
    </script>

    {{-- Global yardımcılar ve servis detay modal köprüsü --}}
    <script src="{{ asset('crm_assets/js/helpers.js') }}"></script>
    <script src="{{ asset('crm_assets/js/permissions.js') }}?v={{ filemtime(public_path('crm_assets/js/permissions.js')) }}"></script>
    <script src="{{ asset('crm_assets/js/servis-detay-modal.js') }}?v={{ filemtime(public_path('crm_assets/js/servis-detay-modal.js')) }}"></script>
    <script src="{{ asset('crm_assets/js/servis-detay-modal-handler.js') }}?v={{ filemtime(public_path('crm_assets/js/servis-detay-modal-handler.js')) }}"></script>

    {{-- Sayfaya özel ana işlevsellik script dosyaları (window.crmData tanımlandıktan sonra) --}}
    @stack('page_specific_main_scripts')

    {{-- Sayfaya özel başlatıcı JS dosyaları (ana scriptler yüklendikten sonra) --}}
    @stack('page_specific_init_js')

    {{-- Sayfaya özel anlık script blokları (tüm diğer scriptler yüklendikten sonra) --}}
    @stack('page_specific_scripts')
    <!--! END: Apps Init !-->

    <!--! BEGIN: Theme Customizer  !-->
    <script src="{{ asset('crm_assets/js/theme-customizer-init.min.js') }}"></script>
    <!--! END: Theme Customizer !-->

</body>
</html>

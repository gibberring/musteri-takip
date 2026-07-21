<!DOCTYPE html>
<html lang="zxx">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="keyword" content="">
    <meta name="author" content="WRAPCODERS">
    <!--! The above 6 meta tags *must* come first in the head; any other head content must come *after* these tags !-->
    <!--! BEGIN: Apps Title-->
    <title>Giriş - Servis Takip</title>
    <!--! END:  Apps Title-->
    <!--! BEGIN: Favicon-->
    {{-- <link rel="shortcut icon" type="image/x-icon" href="./../assets/images/favicon.ico"> --}}
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('crm_assets/images/favicon.ico') }}">
    <!--! END: Favicon-->

    {{-- Eski CSS Linkleri kaldırıldı --}}

    <!-- Vite Direktifi Güncellendi -->
    @vite([
        'resources/css/app.css', 
        'resources/js/app.js',
        'public/crm_assets/css/bootstrap.min.css',
        'public/crm_assets/vendors/css/vendors.min.css',
        'public/crm_assets/css/theme.min.css'
    ])

    <!--! HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries !-->
    <!--! WARNING: Respond.js doesn"t work if you view the page via file: !-->
    <!--[if lt IE 9]>
			<script src="https:oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
			<script src="https:oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
</head>

<body>
    <!--! ================================================================ !-->
    <!--! [Start] Main Content !-->
    <!--! ================================================================ !-->
    <main class="auth-creative-wrapper">
        <div class="auth-creative-inner">
            <div class="creative-card-wrapper">
                <div class="card my-4 overflow-hidden" style="z-index: 1">
                    <div class="row flex-1 g-0">
                        <div class="col-lg-6 h-100 my-auto order-1 order-lg-0">
                            <div class="wd-50 bg-white p-2 rounded-circle shadow-lg position-absolute translate-middle top-50 start-50 d-none d-lg-block">
                                {{-- <img src="./../assets/images/logo-abbr.png" alt="" class="img-fluid"> --}}
                                <img src="{{ asset('crm_assets/images/logo-abbr.png') }}" alt="" class="img-fluid">
                            </div>
                            <div class="creative-card-body card-body p-sm-5">
                                <h2 class="fs-20 fw-bolder mb-4">Giriş</h2>
                                
                                <p class="fs-12 fw-medium text-muted">Sisteme size özel oluşturulan kullanıcı adı ve şifreniz ile giriş yapabilirsiniz. Şifre güvenliğiniz tamamen size aittir.</p>
                                @if (session('message'))
                                    <div class="alert alert-warning mb-3" role="alert">{{ session('message') }}</div>
                                @endif
                                <form method="POST" action="{{ route('login') }}" class="w-100 mt-4 pt-2">
                                    @csrf
                                    <div class="mb-4">
                                        <input type="text" name="nick" class="form-control @error('nick') is-invalid @enderror" placeholder="Kullanıcı Adı" value="{{ old('nick') }}" required autofocus>
                                        @error('nick')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="mb-3">
                                        <div class="input-group">
                                            <input type="password" name="password" id="loginPassword" class="form-control @error('password') is-invalid @enderror" placeholder="Şifre" required autocomplete="current-password">
                                            <button class="btn btn-outline-secondary toggle-password-btn" type="button" data-target="#loginPassword" aria-label="Şifreyi göster/gizle">
                                                <i class="feather feather-eye"></i>
                                            </button>
                                        </div>
                                        @error('password')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="remember">Beni hatırla</label>
                                            </div>
                                        </div>
                                        <div></div>
                                    </div>
                                    <div class="mt-5">
                                        <button type="submit" class="btn btn-lg btn-primary w-100">GİRİŞ YAP</button>
                                    </div>
                                </form>
                                
                                <div class="mt-3 text-center text-muted" style="font-size: 10px;">Servis_Takip v1.2</div>
                            </div>
                        </div>
                        <div class="col-lg-6 bg-primary order-0 order-lg-1">
                            <div class="h-100 d-flex align-items-center justify-content-center">
                                {{-- <img src="./../assets/images/auth/auth-user.png" alt="" class="img-fluid"> --}}
                                <img src="{{ asset('crm_assets/images/auth/auth-user.png') }}" alt="" class="img-fluid">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <!--! ================================================================ !-->
    <!--! [End] Main Content !-->
    <!--! ================================================================ !-->
    <!--! ================================================================ !-->
    <!--! Footer Script !-->
    <!--! ================================================================ !-->
    <!--! BEGIN: Vendors JS !-->
    {{-- <script src="./../assets/vendors/js/vendors.min.js"></script> --}}
    <script src="{{ asset('crm_assets/vendors/js/vendors.min.js') }}"></script>
    <!-- vendors.min.js {always must need to be top} -->
    <!--! END: Vendors JS !-->
    <!--! BEGIN: Apps Init  !-->
    {{-- <script src="./../assets/js/common-init.min.js"></script> --}}
    <script src="{{ asset('crm_assets/js/common-init.min.js') }}"></script>
    <!--! END: Apps Init !-->
    <script>
        (function () {
            document.querySelectorAll('.toggle-password-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var target = document.querySelector(btn.getAttribute('data-target'));
                    if (!target) return;
                    var isPassword = target.getAttribute('type') === 'password';
                    target.setAttribute('type', isPassword ? 'text' : 'password');
                    var icon = btn.querySelector('i');
                    if (icon) {
                        icon.classList.toggle('feather-eye', !isPassword);
                        icon.classList.toggle('feather-eye-off', isPassword);
                    }
                });
            });
        })();
    </script>
    
</body>

</html>
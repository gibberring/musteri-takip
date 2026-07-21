<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="keyword" content="">
    <meta name="author" content="WRAPCODERS">
    <!--! The above 6 meta tags *must* come first in the head; any other head content must come *after* these tags !-->
    <!--! BEGIN: Apps Title-->
    <title>İki Adımlı Doğrulama - Servis Takip</title>
    <!--! END:  Apps Title-->
    <!--! BEGIN: Favicon-->
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('crm_assets/images/favicon.ico') }}">
    <!--! END: Favicon-->
    <!--! BEGIN: Bootstrap CSS-->
    @vite([
        'resources/css/app.css', 
        'resources/js/app.js',
        'public/crm_assets/css/bootstrap.min.css',
        'public/crm_assets/vendors/css/vendors.min.css',
        'public/crm_assets/css/theme.min.css'
    ])
    <!--! END: Bootstrap CSS-->
    <!--! BEGIN: Vendors CSS-->
    <!--! END: Vendors CSS-->
    <!--! BEGIN: Custom CSS-->
    <style>
        #otp {
            display: flex;
            flex-direction: row;
            flex-wrap: nowrap;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        #otp .form-control {
            color: #111 !important;
            background-color: #fff !important;
            -webkit-text-fill-color: #111 !important;
            caret-color: #111 !important;
            font-weight: 600;
            font-size: 18px;
            border-color: #d9dee8;
            width: 40px;
            height: 44px;
            line-height: 44px;
            padding: 0;
            text-align: center;
        }
        @media (max-width: 480px) {
            #otp {
                gap: 4px;
            }
            #otp .form-control {
                width: 34px;
                height: 40px;
                line-height: 40px;
                font-size: 16px;
            }
        }
    </style>
    <!--! END: Custom CSS-->
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
                        <div class="col-lg-6 h-100 my-auto order-2 order-lg-0">
                            <div class="wd-50 bg-white p-2 rounded-circle shadow-lg position-absolute translate-middle top-50 start-50 d-none d-lg-block">
                                <img src="{{ asset('crm_assets/images/logo-abbr.png') }}" alt="" class="img-fluid">
                            </div>
                            <div class="creative-card-body card-body p-sm-5">
                                <h2 class="fs-20 fw-bolder mb-4">İki Adımlı Doğrulama</h2>
                                <h4 class="fs-13 fw-bold mb-2">Google Authenticator uygulamasındaki 6 haneli kodu girin.</h4>
                                <p class="fs-12 fw-medium text-muted">Kod sadece sizin cihazınızda oluşturulur.</p>
                                <form method="POST" action="{{ route('twofactor.challenge.verify') }}" class="w-100 mt-4 pt-2" id="twoFactorVerifyForm">
                                    @csrf
                                    <input type="hidden" name="code" id="twoFactorCode" value="">
                                    <div id="otp" class="inputs d-flex flex-row justify-content-center mt-2">
                                        <input class="m-2 text-center form-control rounded" type="text" id="first" maxlength="1" inputmode="numeric" autocomplete="one-time-code" required>
                                        <input class="m-2 text-center form-control rounded" type="text" id="second" maxlength="1" inputmode="numeric" required>
                                        <input class="m-2 text-center form-control rounded" type="text" id="third" maxlength="1" inputmode="numeric" required>
                                        <input class="m-2 text-center form-control rounded" type="text" id="fourth" maxlength="1" inputmode="numeric" required>
                                        <input class="m-2 text-center form-control rounded" type="text" id="fifth" maxlength="1" inputmode="numeric" required>
                                        <input class="m-2 text-center form-control rounded" type="text" id="sixth" maxlength="1" inputmode="numeric" required>
                                    </div>
                                    @error('code')
                                        <div class="text-danger small mt-2">{{ $message }}</div>
                                    @enderror
                                    <div class="mt-5">
                                        <button type="submit" class="btn btn-lg btn-primary w-100">Doğrula</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="col-lg-6 bg-primary order-1 order-lg-1">
                            <div class="h-100 d-flex align-items-center justify-content-center">
                                <img src="{{ asset('crm_assets/images/auth/auth-user.png') }}" alt="" class="img-fluid">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!--! BEGIN: Vendors JS !-->
    <script src="{{ asset('crm_assets/vendors/js/vendors.min.js') }}"></script>
    <!-- vendors.min.js {always must need to be top} -->
    <!--! END: Vendors JS !-->
    <!--! BEGIN: Apps Init  !-->
    <script src="{{ asset('crm_assets/js/common-init.min.js') }}"></script>
    <!--! END: Apps Init !-->
 
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            function OTPInput() {
                const inputs = document.querySelectorAll("#otp > *[id]");
                inputs.forEach((input, index) => {
                    input.addEventListener("keydown", function(event) {
                        if (event.key === "Backspace") {
                            input.value = "";
                            if (index !== 0) inputs[index - 1].focus();
                        }
                    });

                    input.addEventListener("input", function() {
                        input.value = (input.value || '').replace(/\D/g, '').slice(0, 1);
                        if (input.value && index !== inputs.length - 1) {
                            inputs[index + 1].focus();
                        }
                        const allFilled = Array.from(inputs).every((el) => (el.value || '').trim().length === 1);
                        if (allFilled) {
                            form.requestSubmit();
                        }
                    });

                    input.addEventListener("paste", function(event) {
                        event.preventDefault();
                        const pasted = (event.clipboardData || window.clipboardData).getData('text');
                        const digits = (pasted || '').replace(/\D/g, '').slice(0, inputs.length);
                        if (!digits) return;
                        digits.split('').forEach((digit, i) => {
                            if (inputs[i]) inputs[i].value = digit;
                        });
                        const nextIndex = Math.min(digits.length, inputs.length - 1);
                        inputs[nextIndex].focus();
                        const allFilled = Array.from(inputs).every((el) => (el.value || '').trim().length === 1);
                        if (allFilled) {
                            form.requestSubmit();
                        }
                    });
                });
            }
            OTPInput();

            const form = document.getElementById('twoFactorVerifyForm');
            const codeInput = document.getElementById('twoFactorCode');
            if (form && codeInput) {
                form.addEventListener('submit', function() {
                    const inputs = document.querySelectorAll("#otp > *[id]");
                    let code = '';
                    inputs.forEach((el) => { code += (el.value || '').trim(); });
                    codeInput.value = code;
                });
            }
        });
    </script>
</body>

</html>
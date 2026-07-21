<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>2FA Kurulum</title>
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('crm_assets/images/favicon.ico') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('crm_assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('crm_assets/vendors/css/vendors.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('crm_assets/css/theme.min.css') }}">
    <style>
        .qr-wrap { width: 180px; height: 180px; margin: 0 auto; }
        .auth-card { max-width: 460px; margin: 4rem auto; }
    </style>
</head>
<body>
    <div class="auth-card card shadow-sm">
        <div class="card-body p-4">
            <h5 class="mb-3">İki Adımlı Doğrulama Kurulumu</h5>
            <p class="text-muted small mb-3">Google Authenticator uygulamasıyla QR kodu taratın ve aşağıdaki kod ile kurulumu tamamlayın.</p>
            <div class="qr-wrap mb-3" id="twoFactorQr"></div>
            <div class="text-center text-muted small mb-3">Anahtar: <span class="fw-semibold">{{ $secret }}</span></div>
            <form method="POST" action="{{ route('twofactor.setup.verify') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label small">Doğrulama Kodu</label>
                    <input type="text" name="code" class="form-control form-control-sm @error('code') is-invalid @enderror" placeholder="123456" required>
                    @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary w-100">Kurulumu Tamamla</button>
            </form>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        new QRCode(document.getElementById('twoFactorQr'), {
            text: @json($otpauth),
            width: 180,
            height: 180
        });
    </script>
</body>
</html>

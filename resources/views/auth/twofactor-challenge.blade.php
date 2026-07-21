<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>2FA Doğrulama</title>
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('crm_assets/images/favicon.ico') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('crm_assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('crm_assets/vendors/css/vendors.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('crm_assets/css/theme.min.css') }}">
    <style>
        .auth-card { max-width: 420px; margin: 4rem auto; }
    </style>
</head>
<body>
    <div class="auth-card card shadow-sm">
        <div class="card-body p-4">
            <h5 class="mb-3">İki Adımlı Doğrulama</h5>
            <p class="text-muted small mb-3">Google Authenticator uygulamasındaki 6 haneli kodu girin.</p>
            <form method="POST" action="{{ route('twofactor.challenge.verify') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label small">Doğrulama Kodu</label>
                    <input type="text" name="code" class="form-control form-control-sm @error('code') is-invalid @enderror" placeholder="123456" required>
                    @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary w-100">Doğrula</button>
            </form>
        </div>
    </div>
</body>
</html>

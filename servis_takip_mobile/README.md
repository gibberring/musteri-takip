# Servis Takip Mobil

Web CRM ile aynı tasarım ve API kullanan Flutter mobil uygulaması.

## Gereksinimler

- Flutter SDK 3.5+
- Backend API (Laravel) çalışır durumda ve `API_BASE_URL` ile erişilebilir olmalı

## Kurulum

```bash
cd servis_takip_mobile
flutter pub get
```

## API adresi

Varsayılan: `https://your-domain.com`. Değiştirmek için:

1. **Derleme ile:**  
   `flutter run --dart-define=API_BASE_URL=https://your-api.com`

2. **Kod ile:**  
   `lib/core/api/api_client.dart` içinde `kBaseUrl` veya `ApiClient.setBaseUrl()` kullanın (ör. uygulama başlangıcında).

## Çalıştırma

```bash
flutter run
```

## Özellikler

- Web ile aynı renk paleti (primary #3454D1, Inter font)
- Giriş (nick + şifre), 2FA desteği
- Rol bazlı menü (Patron, Operatör, Teknisyen vb.)
- Panel özeti, Müşteriler listesi, Servisler listesi
- Drawer menü ve AppBar (web sidebar/header karşılığı)

## Backend API

Laravel tarafında `routes/api.php` ve Sanctum kullanılıyor. Web rotaları değiştirilmedi.

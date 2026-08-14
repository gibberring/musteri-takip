<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\Personel;
use App\Models\Servis;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TeknisyenYonlendirmeBildirimService
{
    public const DURUM_TEKNISYEN_YONLENDIRILDI = 9098;

    public const SETTING_SABLON = 'whatsapp_teknisyen_yonlendirme_sablon';

    /**
     * Görüldü sıfırla + teknisyene WhatsApp bildirimi gönder (yalnızca yapılandırılmış API).
     *
     * @return array{sent:bool,mode:?string,phone:?string,error:?string}
     */
    public function handleYonlendirme(Servis $servis, ?int $teknisyenId = null): array
    {
        $teknisyenId = $teknisyenId ?: (int) ($servis->personel_id ?? 0);

        $this->resetGoruldu($servis);

        if ($teknisyenId <= 0) {
            return [
                'sent' => false,
                'mode' => null,
                'phone' => null,
                'error' => 'Teknisyen atanmadı.',
            ];
        }

        return $this->notifyTeknisyen($servis->fresh([
            'musteri.il',
            'musteri.ilce',
            'marka',
            'cihazTuru',
            'personel',
        ]) ?: $servis, $teknisyenId);
    }

    public function resetGoruldu(Servis $servis): void
    {
        $servis->teknisyen_goruldu_at = null;
        $servis->teknisyen_goruldu_personel_id = null;
        $servis->save();
    }

    /**
     * @return array{sent:bool,mode:?string,phone:?string,error:?string}
     */
    public function notifyTeknisyen(Servis $servis, int $teknisyenId): array
    {
        $personel = Personel::find($teknisyenId);
        if (!$personel) {
            return [
                'sent' => false,
                'mode' => null,
                'phone' => null,
                'error' => 'Teknisyen bulunamadı.',
            ];
        }

        $phone = $this->normalizePhone($personel->tel1 ?? '');
        $message = $this->buildMessage($servis, $personel);

        if (!$phone) {
            Log::warning('Teknisyen WhatsApp: cep yok', [
                'servis_id' => $servis->id,
                'personel_id' => $teknisyenId,
            ]);
            return [
                'sent' => false,
                'mode' => null,
                'phone' => null,
                'error' => 'Teknisyen cep numarası yok.',
            ];
        }

        $apiResult = $this->sendViaConfiguredApi($phone, $message);
        if ($apiResult['sent']) {
            Log::info('Teknisyen WhatsApp API gönderildi', [
                'servis_id' => $servis->id,
                'personel_id' => $teknisyenId,
                'mode' => $apiResult['mode'],
            ]);
            return [
                'sent' => true,
                'mode' => $apiResult['mode'],
                'phone' => $phone,
                'error' => null,
            ];
        }

        $error = $this->formatApiFailureMessage($apiResult['error'] ?? null);
        Log::warning('Teknisyen WhatsApp API gönderilemedi (wa.me yok)', [
            'servis_id' => $servis->id,
            'personel_id' => $teknisyenId,
            'api_error' => $apiResult['error'],
        ]);

        return [
            'sent' => false,
            'mode' => $apiResult['mode'] ?? null,
            'phone' => $phone,
            'error' => $error,
        ];
    }

    private function formatApiFailureMessage(?string $apiError): string
    {
        $apiError = trim((string) $apiError);
        if (
            $apiError === ''
            || stripos($apiError, 'API URL') !== false
            || stripos($apiError, 'yapılandır') !== false
            || stripos($apiError, 'ayarlı değil') !== false
        ) {
            return 'WhatsApp API ayarlı değil. Otomatik gönderim için Ayarlar → WhatsApp API URL ve Key girin.';
        }
        return 'WhatsApp gönderilemedi: ' . $apiError;
    }

    public function buildDeepLink(int $servisId): string
    {
        $base = rtrim((string) config('app.url'), '/');
        return $base . '/servisler#servis-' . $servisId;
    }

    public function normalizePhone(?string $phone): ?string
    {
        if ($phone === null || trim($phone) === '') {
            return null;
        }
        $digits = preg_replace('/\D+/', '', $phone);
        if ($digits === null || $digits === '') {
            return null;
        }
        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }
        if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            $digits = '90' . substr($digits, 1);
        } elseif (strlen($digits) === 10 && str_starts_with($digits, '5')) {
            $digits = '90' . $digits;
        }
        if (strlen($digits) < 11) {
            return null;
        }
        return $digits;
    }

    public static function defaultTemplate(): string
    {
        return "Sayın teknisyenimiz [teknisyen_adı] size yeni bir iş atandı.\n\n"
            . "[servis_bilgileri]\n\n"
            . "Atanan işi kabul etmek için hemen [link] adresine giderek onaylayınız.\n\n"
            . "Aksi halde yöneticinize başvurunuz.";
    }

    /**
     * UI yardımı için desteklenen etiketler.
     *
     * @return array<int, array{tag:string,label:string}>
     */
    public static function availablePlaceholders(): array
    {
        return [
            ['tag' => '[teknisyen_adı]', 'label' => 'Teknisyen adı'],
            ['tag' => '[servis_bilgileri]', 'label' => 'Formatlı servis özeti'],
            ['tag' => '[link]', 'label' => 'Servis deep-link'],
            ['tag' => '[servis_no]', 'label' => 'Servis no'],
            ['tag' => '[musteri_adi]', 'label' => 'Müşteri adı'],
            ['tag' => '[musteri_tel]', 'label' => 'Müşteri telefon'],
            ['tag' => '[adres]', 'label' => 'Adres'],
            ['tag' => '[cihaz]', 'label' => 'Cihaz'],
            ['tag' => '[ariza]', 'label' => 'Arıza'],
            ['tag' => '[gidis_tarihi]', 'label' => 'Gidiş tarihi'],
            ['tag' => '[operator_not]', 'label' => 'Operatör notu'],
        ];
    }

    public function getTemplate(): string
    {
        $saved = trim((string) AppSetting::getValue(self::SETTING_SABLON, ''));
        return $saved !== '' ? $saved : self::defaultTemplate();
    }

    public function buildMessage(Servis $servis, ?Personel $teknisyen = null): string
    {
        $teknisyen = $teknisyen ?: ($servis->relationLoaded('personel') ? $servis->personel : null);
        $map = $this->buildPlaceholderMap($servis, $teknisyen);
        $template = $this->getTemplate();

        return strtr($template, $map);
    }

    /**
     * Mevcut güzel formatlı servis özeti bloğu (deep-link hariç).
     */
    public function buildServisBilgileri(Servis $servis): string
    {
        $musteri = $servis->musteri;
        $adres = $this->formatAdres($musteri);
        $gidis = $this->formatGidisTarihi($servis);
        $cihaz = $this->formatCihaz($servis);
        $tel = $this->formatMusteriTel($musteri);

        $lines = [
            '🔧 *Yeni Servis Yönlendirmesi*',
            '',
            '*Servis No:* #' . $servis->id,
            '*Müşteri:* ' . $this->safe($musteri?->ad ?? null),
            '*Telefon:* ' . $tel,
            '*Adres:* ' . $adres,
            '*Cihaz:* ' . $cihaz,
            '*Arıza:* ' . $this->safe($servis->cihaz_arizasi ?? null),
            '*Gidiş Tarihi:* ' . $gidis,
        ];

        $opNot = trim((string) ($servis->operator_not ?? ''));
        if ($opNot !== '') {
            $lines[] = '*Operatör Notu:* ' . $opNot;
        }

        return implode("\n", $lines);
    }

    /**
     * @return array<string, string>
     */
    private function buildPlaceholderMap(Servis $servis, ?Personel $teknisyen): array
    {
        $musteri = $servis->musteri;

        return [
            '[teknisyen_adı]' => $this->safe($teknisyen?->ad ?? null),
            '[servis_bilgileri]' => $this->buildServisBilgileri($servis),
            '[link]' => $this->buildDeepLink((int) $servis->id),
            '[servis_no]' => (string) $servis->id,
            '[musteri_adi]' => $this->safe($musteri?->ad ?? null),
            '[musteri_tel]' => $this->formatMusteriTel($musteri),
            '[adres]' => $this->formatAdres($musteri),
            '[cihaz]' => $this->formatCihaz($servis),
            '[ariza]' => $this->safe($servis->cihaz_arizasi ?? null),
            '[gidis_tarihi]' => $this->formatGidisTarihi($servis),
            '[operator_not]' => $this->safe($servis->operator_not ?? null),
        ];
    }

    private function safe(?string $value): string
    {
        $v = trim((string) ($value ?? ''));
        return $v !== '' ? $v : '-';
    }

    private function formatAdres($musteri): string
    {
        if (!$musteri) {
            return '-';
        }
        $parts = array_filter([
            $musteri->adres ?? null,
            optional($musteri->ilce)->ad,
            optional($musteri->il)->ad,
        ]);
        $adres = implode(' / ', $parts);
        return $adres !== '' ? $adres : '-';
    }

    private function formatMusteriTel($musteri): string
    {
        if (!$musteri) {
            return '-';
        }
        $tel = trim(($musteri->tel1 ?? '') . (($musteri->tel2 ?? '') ? ' / ' . $musteri->tel2 : ''));
        return $tel !== '' ? $tel : '-';
    }

    private function formatCihaz(Servis $servis): string
    {
        $cihaz = trim(implode(' ', array_filter([
            optional($servis->marka)->ad,
            optional($servis->cihazTuru)->ad,
            $servis->cihaz_model ?? null,
        ])));
        return $cihaz !== '' ? $cihaz : '-';
    }

    private function formatGidisTarihi(Servis $servis): string
    {
        if (empty($servis->tarih)) {
            return '-';
        }
        try {
            return Carbon::parse($servis->tarih)->format('d.m.Y');
        } catch (\Throwable $e) {
            return $this->safe((string) $servis->tarih);
        }
    }

    /**
     * Ayarlar ekranından test gönderimi (yalnızca API; wa.me yok).
     *
     * @return array{sent:bool,mode:?string,error:?string}
     */
    public function sendTestMessage(string $phoneRaw, string $message): array
    {
        $phone = $this->normalizePhone($phoneRaw);
        if (!$phone) {
            return [
                'sent' => false,
                'mode' => null,
                'error' => 'Geçersiz telefon.',
            ];
        }
        $apiResult = $this->sendViaConfiguredApi($phone, $message);
        if (!empty($apiResult['sent'])) {
            return [
                'sent' => true,
                'mode' => $apiResult['mode'] ?? null,
                'error' => null,
            ];
        }
        return [
            'sent' => false,
            'mode' => $apiResult['mode'] ?? null,
            'error' => $this->formatApiFailureMessage($apiResult['error'] ?? null),
        ];
    }

    /**
     * @return array{sent:bool,mode:?string,error:?string}
     */
    private function sendViaConfiguredApi(string $phone, string $message): array
    {
        $apiUrl = trim((string) (
            AppSetting::getValue('whatsapp_api_url')
            ?: config('services.whatsapp.api_url')
            ?: ''
        ));
        $apiKey = trim((string) (
            AppSetting::getValue('whatsapp_api_key')
            ?: config('services.whatsapp.api_key')
            ?: ''
        ));

        if ($apiUrl === '') {
            return ['sent' => false, 'mode' => null, 'error' => 'WhatsApp API ayarlı değil.'];
        }

        try {
            $url = $apiUrl;
            $payload = [];
            $mode = 'http';

            // Green-API: .../waInstanceXXXX[/sendMessage/TOKEN]
            if (stripos($apiUrl, 'green-api.com') !== false || stripos($apiUrl, 'waInstance') !== false) {
                $mode = 'green-api';
                if (stripos($apiUrl, 'sendMessage') === false) {
                    $url = rtrim($apiUrl, '/') . '/sendMessage/' . rawurlencode($apiKey);
                }
                $payload = [
                    'chatId' => $phone . '@c.us',
                    'message' => $message,
                ];
            } else {
                $payload = [
                    'phone' => $phone,
                    'message' => $message,
                    'chatId' => $phone . '@c.us',
                    'text' => $message,
                ];
            }

            $request = Http::timeout(20)->acceptJson();
            if ($apiKey !== '' && stripos($url, $apiKey) === false && $mode !== 'green-api') {
                $request = $request->withToken($apiKey);
            }

            $response = $request->post($url, $payload);
            if ($response->successful()) {
                return ['sent' => true, 'mode' => $mode, 'error' => null];
            }

            return [
                'sent' => false,
                'mode' => $mode,
                'error' => 'API HTTP ' . $response->status() . ': ' . mb_substr($response->body(), 0, 200),
            ];
        } catch (\Throwable $e) {
            Log::error('Teknisyen WhatsApp API hata: ' . $e->getMessage());
            return ['sent' => false, 'mode' => null, 'error' => $e->getMessage()];
        }
    }
}

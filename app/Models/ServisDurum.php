<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServisDurum extends Model
{
    use HasFactory;

    /** Kullanıcılara seçenek olarak gösterilmeyen (görünmez) durum ID'si - "Haber Verecek", eski kayıtlar aynen kalır */
    public const GORUNMEZ_DURUM_ID = 9110;

    /** Tamamlanmış / kapanmış — teknisyen açık iş yüküne dahil değil */
    public const TEKNISYEN_KAPALI_DURUM_IDS = [
        9099, // Servisi Sonlandırıldı
        9104, // Müşteri İptal Etti
        9106, // Fiyatta Anlaşılamadı
        9114, // Teslimata Hazır (Tamamlandı)
        9115, // Cihaz Teslim Edildi
        9358, // Ücret İadesi Gerçekleşti
        9359, // Bize Kalan Ürünler
    ];

    /**
     * Teknisyen açık iş yükü (kart sırası + Toplam açık iş).
     * Kaynak: teknisyen profil sekmeleri + atölye/nakliye durumları.
     * Kapalı (9115, 9104, 9106, 9099, 9114, 9358, 9359) ve operatör ön aşaması (9097, 9334) yok.
     * İptal (9104) ve Fiyatta Anlaşılamadı (9106) kartlarda ayrı satırdır; açık toplama girmez.
     */
    public const TEKNISYEN_ACIK_DURUM_IDS = [
        9098, // Teknisyen Yönlendirildi
        9477, // Yarın Gidilecek
        9100, // Atölyeye Alındı
        9103, // Parça Gidecek
        9105, // Yerinde Bakım Yapıldı
        9116, // Tekrar Servis
        9102, // Müşteriye Ulaşılamadı
        9113, // Atölyede Tamir Ediliyor
        9117, // Nakliyede
        9524, // Ücret İadesi Sürecinde
        9110, // Haber Verecek
        9526, // T.Atölyeye Alındı
        9784, // Tv Süpürge Atölyesi
        9786, // T.Nakliyede
        9787, // İzmir Atölye
    ];

    /** Kart satırı olarak gösterilir; Toplam açık iş sayısına dahil edilmez. */
    public const TEKNISYEN_BAKISI_KART_EK_DURUM_IDS = [
        9104, // Müşteri İptal Etti
        9106, // Fiyatta Anlaşılamadı
    ];

    /** Kart satırları + durum_id liste allowlist (açık + İptal / Fiyatta Anlaşılamadı). */
    public static function teknisyenBakisiKartDurumIds(): array
    {
        return array_merge(self::TEKNISYEN_ACIK_DURUM_IDS, self::TEKNISYEN_BAKISI_KART_EK_DURUM_IDS);
    }

    public static function teknisyenBakisiKisaAd(int $durumId, ?string $fallback = null): string
    {
        $map = [
            9098 => 'Teknisyen Yönlendirildi',
            9477 => 'Yarın Gidilecek',
            9100 => 'Atölyede',
            9103 => 'Parça Gidecek',
            9105 => 'Yerinde Bakım',
            9116 => 'Tekrar Servis',
            9102 => 'Ulaşılamadı',
            9113 => 'Atölyede Tamir',
            9117 => 'Nakliyede',
            9524 => 'Ücret İadesi',
            9110 => 'Haber Verecek',
            9526 => 'T.Atölye',
            9784 => 'TV Süpürge Atölye',
            9786 => 'T.Nakliyede',
            9787 => 'İzmir Atölye',
            9104 => 'İptal',
            9106 => 'Fiyatta Anlaşılamadı',
        ];

        return $map[$durumId] ?? ($fallback ?: (string) $durumId);
    }

    public static function teknisyenBakisiBadgeClass(int $durumId): string
    {
        return match ($durumId) {
            9098 => 'bg-secondary',
            9477, 9103 => 'bg-warning text-dark',
            9100, 9524, 9113, 9526, 9784, 9787 => 'bg-primary',
            9105 => 'bg-success',
            9116, 9102, 9104 => 'bg-danger',
            9110, 9106 => 'bg-info',
            9117, 9786 => 'bg-dark',
            default => 'bg-light text-dark',
        };
    }

    /** Seçenek listelerinde görünür durumlar (Haber Verecek hariç) */
    public function scopeGorunur($query)
    {
        return $query->where('id', '!=', self::GORUNMEZ_DURUM_ID);
    }

    protected $table = 'servis_durum'; // Tablo adını belirtiyoruz
    public $timestamps = true;

    protected $fillable = [
        'uye_firma_id',
        'ad',
        'hangi_asamlarda_goruınur',
        'sira',
        'sikayetci',
        'yonlendirme_var',
        'atolyeci'
    ];

    /**
     * Bu duruma sahip servis kayıtlarını getirir.
     */
    public function servisler(): HasMany
    {
        return $this->hasMany(Servis::class, 'servis_durum_id');
        // Eğer yabancı anahtar 'servis_durum_id' değilse:
        // return $this->hasMany(Servis::class, 'foreign_key');
        // Eğer yerel anahtar 'id' değilse:
        // return $this->hasMany(Servis::class, 'foreign_key', 'local_key');
    }

    /**
     * Bu duruma ait soruları getirir.
     */
    public function sorular(): HasMany
    {
        return $this->hasMany(ServisDurumSoru::class, 'servis_durum_id');
    }

    /**
     * Bu duruma ait cevapları getirir.
     */
    public function cevaplar(): HasMany
    {
        return $this->hasMany(ServisDurumCevap::class, 'servis_durum_id');
    }

    /**
     * Bu duruma ait cevap0 kayıtlarını getirir.
     */
    public function cevap0lar(): HasMany
    {
        return $this->hasMany(ServisDurumCevap0::class, 'servis_durum_id');
    }

    /**
     * Bu durumun ait olduğu üye firmayı getirir.
     */
    public function uyeFirma(): BelongsTo
    {
        return $this->belongsTo(UyeFirma::class, 'uye_firma_id');
    }
} 
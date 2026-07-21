<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Musteri extends Model
{
    use HasFactory;

    protected $table = 'musteriler'; // Tablo adını açıkça belirtiyoruz

    protected $fillable = [
        'musteri_tip',
        'tarih',
        'saat',
        'ad',
        'tel1',
        'tel2',
        'il_id',
        'ilce_id',
        'adres',
        'vdaire',
        'vno',
        'personel_id',
        // 'uye_firma_id', // Gerekliyse ekleyin
        // Başka gerekli alanlar varsa buraya ekleyin
    ];

    /**
     * Müşterinin ait olduğu ili getirir.
     */
    public function il(): BelongsTo
    {
        return $this->belongsTo(Il::class, 'il_id'); // Il modelini varsayıyoruz
    }

    /**
     * Müşterinin ait olduğu ilçeyi getirir.
     */
    public function ilce(): BelongsTo
    {
        return $this->belongsTo(Ilce::class, 'ilce_id'); // Ilce modelini varsayıyoruz
    }

    /**
     * Müşteriye ait servis kayıtlarını getirir.
     */
    public function servisler(): HasMany
    {
        return $this->hasMany(Servis::class, 'musteri_id');
    }

    /**
     * Müşterinin ait olduğu kategoriyi getirir.
     */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(MusteriKategori::class, 'kat'); // MusteriKategori modelini ve 'kat' foreign key'ini varsayıyoruz
    }

    // 'ad' sütunu için accessor
    protected function ad(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? mb_convert_case(mb_strtolower($value, 'UTF-8'), MB_CASE_TITLE, "UTF-8") : null,
        );
    }
} 
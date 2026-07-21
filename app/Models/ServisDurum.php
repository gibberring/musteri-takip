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
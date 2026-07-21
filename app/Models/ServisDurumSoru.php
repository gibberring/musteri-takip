<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServisDurumSoru extends Model
{
    use HasFactory;

    protected $table = 'servisdurum_sorulari'; // Tablo adını belirtiyoruz
    public $timestamps = false; // Timestamps yok

    protected $fillable = [
        'uye_firma_id',
        'servis_durum_id',
        'soru',
        'cevap_format',
        'sira'
    ];

    /**
     * Sorunun ait olduğu servis durumunu getirir.
     */
    public function servisDurum(): BelongsTo
    {
        return $this->belongsTo(ServisDurum::class);
    }

    /**
     * Sorunun ait olduğu üye firmayı getirir.
     */
    public function uyeFirma(): BelongsTo
    {
        return $this->belongsTo(UyeFirma::class, 'uye_firma_id');
    }

    /**
     * Soruya verilen cevapları getirir.
     */
    public function cevaplar(): HasMany
    {
        return $this->hasMany(ServisDurumCevap::class, 'soru_id');
    }
} 
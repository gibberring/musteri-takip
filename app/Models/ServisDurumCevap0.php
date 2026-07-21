<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServisDurumCevap0 extends Model
{
    use HasFactory;

    protected $table = 'servisdurum_cevap0'; // Tablo adını belirtiyoruz
    public $timestamps = false;

    protected $fillable = [
        'uye_firma_id',
        'personel_id',
        'servis_id',
        'servis_durum_id',
        'tarih',
        'saat'
    ];

    /**
     * Cevap0'ın ait olduğu servisi getirir.
     */
    public function servis(): BelongsTo
    {
        return $this->belongsTo(Servis::class);
    }

    /**
     * Cevap0'ın ait olduğu servis durumunu getirir.
     */
    public function servisDurum(): BelongsTo
    {
        return $this->belongsTo(ServisDurum::class);
    }

    /**
     * Cevap0'ın ait olduğu personeli getirir.
     */
    public function personel(): BelongsTo
    {
        return $this->belongsTo(Personel::class);
    }

    /**
     * Cevap0'ın ait olduğu üye firmayı getirir.
     */
    public function uyeFirma(): BelongsTo
    {
        return $this->belongsTo(UyeFirma::class, 'uye_firma_id');
    }

    /**
     * Cevap0'a bağlı cevapları getirir.
     */
    public function cevaplar(): HasMany
    {
        return $this->hasMany(ServisDurumCevap::class, 'durum_cevap0_id');
    }
} 
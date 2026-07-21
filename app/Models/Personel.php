<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Personel extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * Veritabanında şifre sütunu `password` değil `sifre` olduğu için Laravel'in
     * giriş sonrası rehash/update işlemi doğru kolona yazılmalıdır.
     *
     * @var string
     */
    protected $authPasswordName = 'sifre';

    protected $table = 'personel';

    protected $fillable = [
        'uye_firma_id',
        'kaydeden_personel_id',
        'ad',
        'nick',
        'sifre',
        'poz_id',
        'aktif',
        'tel1',
        'tel2',
        'ilce_id',
        'il_id',
        'adres',
        'resim',
        'vno',
        'is_basi_tarih',
        'email',
        'servis_fis_kullanabilir',
        'yazici_icerigi',
        'yazici_genisligi',
        'mesai_basladimi',
        'fis_firma',
        'fis_tel',
        'fis_adres',
        'e_fis_verebilir',
        'calisma_sekli_type',
        'calisma_sekli_adet_tutar',
        'operator_kazanc_tutar',
        'two_factor_secret',
        'two_factor_enabled',
        'two_factor_verified_at',
        'two_factor_required',
    ];

    /**
     * Gizlenmesi gereken alanlar.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'sifre',
        'two_factor_secret',
        'remember_token',
    ];

    /**
     * Personele ait servis kayıtlarını getirir.
     */
    public function servisler(): HasMany
    {
        return $this->hasMany(Servis::class);
        // Eğer yabancı anahtar 'personel_id' değilse:
        // return $this->hasMany(Servis::class, 'foreign_key');
        // Eğer yerel anahtar 'id' değilse:
        // return $this->hasMany(Servis::class, 'foreign_key', 'local_key');
    }

    /**
     * Personelin pozisyonunu getirir.
     */
    public function pozisyon(): BelongsTo
    {
        return $this->belongsTo(TnmPersonelPozisyon::class, 'poz_id', 'id');
    }
} 
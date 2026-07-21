<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Islemloglari extends Model
{
    use HasFactory;

    protected $table = 'islemloglari';
    public $timestamps = false; // Migration'da timestamps yoktu

    protected $fillable = [
        'islemi_yapan_personel_id',
        'servis_id',
        'servis_durum_id',
        'tarih',
        'saat',
        'aciklama',
        'silindi',
        'silinme_tarihi',
        'silen_kisi_id',
    ];

    public function scopeNotDeleted($query)
    {
        return $query->where(function ($q) {
            $q->where('silindi', '!=', 1)
              ->orWhereNull('silindi');
        });
    }

    public function scopeOnlyDeleted($query)
    {
        return $query->where('silindi', 1);
    }

    /**
     * İşlemi yapan personeli getirir.
     */
    public function personel(): BelongsTo
    {
        // Migration'da sütun adı `islemiyapan` idi, modelde `personel_id` varsaydık, düzeltelim.
        // Migration'daki ad: `islemi_yapan_personel_id` (önceki düzenlemelerde düzeltilmiş)
        return $this->belongsTo(Personel::class, 'islemi_yapan_personel_id'); 
    }

    /**
     * İşlemin ait olduğu servisi getirir.
     */
    public function servis(): BelongsTo
    {
        return $this->belongsTo(Servis::class, 'servis_id');
    }

    /**
     * İşlemin ait olduğu servis durumunu getirir.
     */
    public function servisDurum(): BelongsTo
    {
        return $this->belongsTo(ServisDurum::class, 'servis_durum_id');
    }

    /**
     * Logu silen kişiyi getirir.
     */
    public function silenKisi(): BelongsTo
    {
        return $this->belongsTo(Personel::class, 'silen_kisi_id');
    }
} 
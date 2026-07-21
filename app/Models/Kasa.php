<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kasa extends Model
{
    use HasFactory;

    protected $table = 'kasa';
    // public $timestamps = true; // Migration dosyasında timestamps vardı

    // Migration'daki sütunlara göre fillable (tahmini)
    protected $fillable = [
        'uye_firma_id',
        'personel_id',
        'tarih',
        'saat',
        'tutar',
        'pb',
        'islem_tarihi',
        'islem_saati',
        'gerceklesme',
        'odeme_yonu', // Eklendi: Kullanıcının seçtiği ödeme yönü
        'servis_id',
        'satis_id',
        'tedarikci_id',
        'ilgili_personel_id',
        'aciklama',
        'odeme_turu_id',
        'odeme_sekli_id',
        'taksitmi',
        // 'stok_hareket_id', // Silinmişti
        'silindi',
        'silen_kisi_id',
        'silinme_tarihi',
        'sadece_kasa',
    ];

    /**
     * Kasa kaydının ait olduğu servisi getirir.
     */
    public function servis(): BelongsTo
    {
        return $this->belongsTo(Servis::class, 'servis_id');
    }

    /**
     * İşlemi yapan personeli getirir.
     */
    public function personel(): BelongsTo
    {
        return $this->belongsTo(Personel::class, 'personel_id');
    }
    
    /**
     * Ödeme türünü getirir.
     */
    public function odemeTuru(): BelongsTo
    {
        // KasaOdemeTuru modelinin var olduğunu varsayıyoruz
        return $this->belongsTo(KasaOdemeTuru::class, 'odeme_turu_id'); 
    }

    /**
     * Ödeme şeklini getirir.
     */
    public function odemeSekli(): BelongsTo
    {
        // KasaOdemeSekli modelinin var olduğunu varsayıyoruz
        return $this->belongsTo(KasaOdemeSekli::class, 'odeme_sekli_id');
    }
    
     /**
     * Tedarikçiyi getirir.
     */
    public function tedarikci(): BelongsTo
    {
        // ServisTedarikciler modelinin var olduğunu varsayıyoruz
        return $this->belongsTo(ServisTedarikciler::class, 'tedarikci_id');
    }
    
     /**
     * İlgili personeli getirir.
     */
    public function ilgiliPersonel(): BelongsTo
    {
        return $this->belongsTo(Personel::class, 'ilgili_personel_id');
    }
    
     /**
     * Silen kişiyi getirir.
     */
    public function silenKisi(): BelongsTo
    {
        return $this->belongsTo(Personel::class, 'silen_kisi_id');
    }
    
    // uye_firma ilişkisi de eklenebilir...
} 
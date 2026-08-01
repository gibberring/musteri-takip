<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Servis extends Model
{
    use HasFactory;

    protected $table = 'servisler'; // Tablo adı belirtildi

    // protected $fillable = []; // Gerekirse doldurulacak alanları buraya ekleyebilirsiniz
    protected $fillable = [
        // Sadece modal'daki cihaz bilgileri güncellemesinde kullanılan alanlar:
        'marka_id',
        'cihaz_tur_id',
        'cihaz_model',
        'seri_no',
        'cihaz_arizasi',
        // Diğer alanlar (musteri_id, personel_id, servis_durum_id vb.)
        // bu formdan güncellenmiyorsa fillable'dan çıkarıldı.
        // Eğer başka yerlerde toplu atama ile güncelleniyorlarsa,
        // bu listenin daha kapsamlı olması veya farklı bir yöntem kullanılması gerekebilir.
        // Yeni servis oluşturma için eklenenler:
        'musteri_id',
        'servis_durum_id',
        'personel_id',
        'olusturan_personel_id',
        'tarih', // Veritabanı sütun adı varsayımı
        'saat',  // Veritabanı sütun adı varsayımı
        'operator_not', // 'aciklama' yerine doğru sütun adı
        // Soft delete için eklenecek alanlar:
        'silindi',
        'silen_kisi_id',
        'silinme_tarihi',
        // 'kaynak_id', // Formda yok ama gerekirse?
        // 'tahmini_teslim_tarihi', // Formda yok
        // 'teslim_tarihi', // Formda yok
        // 'kargo_no', // Formda yok
        // 'kargo_firma', // Formda yok
        // 'garanti_durumu', // Formda yok
    ];

    /**
     * Servis kaydının ait olduğu müşteriyi getirir.
     */
    public function musteri(): BelongsTo
    {
        return $this->belongsTo(Musteri::class, 'musteri_id');
    }

    /**
     * Servis kaydına atanan personeli getirir.
     */
    public function personel(): BelongsTo
    {
        return $this->belongsTo(Personel::class, 'personel_id');
    }

    /**
     * Servis kaydının durumunu getirir.
     */
    public function servisDurum(): BelongsTo
    {
        return $this->belongsTo(ServisDurum::class, 'servis_durum_id');
    }

    /**
     * Servis kaydının markasını getirir.
     */
    public function marka(): BelongsTo
    {
        return $this->belongsTo(TnmMarka::class, 'marka_id'); // Model adı TnmMarka varsayıldı
    }

    /**
     * Servis kaydının cihaz türünü getirir.
     */
    public function cihazTuru(): BelongsTo
    {
        return $this->belongsTo(TnmCihazTuru::class, 'cihaz_tur_id'); // Model adı TnmCihazTuru olarak düzeltildi
    }

    /**
     * Servisi silen kişiyi getirir.
     */
    public function silenKisi(): BelongsTo
    {
        return $this->belongsTo(Personel::class, 'silen_kisi_id');
    }

    /**
     * Servise ait işlem loglarını getirir.
     */
    public function islemloglari(): HasMany
    {
        return $this->hasMany(Islemloglari::class, 'servis_id')
            ->where(function ($q) {
                $q->where('silindi', '!=', 1)
                  ->orWhereNull('silindi');
            });
    }

    /**
     * Servise ait kasa hareketlerini getirir (soft-delete edilenler hariç).
     */
    public function kasaHareketleri(): HasMany
    {
        return $this->hasMany(Kasa::class, 'servis_id')
            ->where(function ($q) {
                $q->where('silindi', '!=', 1)
                  ->orWhereNull('silindi');
            });
    }

    /**
     * Servise ait fişleri getirir.
     */
    public function fisleri(): HasMany
    {
        return $this->hasMany(ServisFisi::class, 'servis_id');
    }

    /**
     * Servise ait resimleri getirir.
     */
    public function servisResimleri(): HasMany
    {
        return $this->hasMany(ServisResmi::class, 'servis_id');
    }
}

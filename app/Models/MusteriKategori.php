<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MusteriKategori extends Model
{
    use HasFactory;

    protected $table = 'musteri_kategori'; // Tablo adını belirtiyoruz
    // protected $fillable = []; // Gerekirse doldurulacak alanları buraya ekleyebilirsiniz

    /**
     * Bu kategoriye ait müşterileri getirir.
     */
    public function musteriler(): HasMany
    {
        return $this->hasMany(Musteri::class, 'musteri_kategori_id');
        // Varsayılan yabancı anahtar 'musteri_kategori_id' yerine 'kategori_id' olsaydı:
        // return $this->hasMany(Musteri::class);
        // Eğer yerel anahtar 'id' değilse:
        // return $this->hasMany(Musteri::class, 'musteri_kategori_id', 'local_key');
    }
} 
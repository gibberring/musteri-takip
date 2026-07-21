<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MusteriSoruCevap extends Model
{
    use HasFactory;

    protected $table = 'musteri_soru_cevaplari'; // Tablo adını belirtiyoruz
    // protected $fillable = []; // Gerekirse doldurulacak alanları buraya ekleyebilirsiniz
} 
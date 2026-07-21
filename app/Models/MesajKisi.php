<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MesajKisi extends Model
{
    use HasFactory;

    // protected $fillable = []; // Gerekirse doldurulacak alanları buraya ekleyebilirsiniz
    protected $table = 'mesaj_kisiler'; // Tablo adını belirtiyoruz, Laravel varsayılan olarak çoğul yapar (mesaj_kisis)
} 
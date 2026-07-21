<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonelSoru extends Model
{
    use HasFactory;

    protected $table = 'personel_sorulari'; // Tablo adını belirtiyoruz
    // protected $fillable = []; // Gerekirse doldurulacak alanları buraya ekleyebilirsiniz
} 
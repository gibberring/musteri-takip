<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class YonlendirilenPersonel extends Model
{
    use HasFactory;

    protected $table = 'yonlendirilen_personeller'; // Tablo adını belirtiyoruz
    // protected $fillable = []; // Gerekirse doldurulacak alanları buraya ekleyebilirsiniz
} 
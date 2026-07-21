<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TnmMarka extends Model
{
    use HasFactory;

    protected $table = 'tnm_markalar'; // Tablo adını belirtiyoruz
    // protected $fillable = []; // Gerekirse doldurulacak alanları buraya ekleyebilirsiniz
} 
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KasaOdemeTuru extends Model
{
    use HasFactory;

    protected $table = 'kasa_odeme_turu';

    // Timestamps migration ile eklendiği için bu satır varsayılan olarak true kalabilir
    // public $timestamps = true; 

    protected $fillable = [
        'uye_firma_id',
        'ad',
        'muhattap',
        'yon',
        'sira',
        'servisler_kategorisi',
        'stok_kategorisi',
        'garanti_geliri_kategorisi' // Bu sütun migration'da vardı
    ];
}

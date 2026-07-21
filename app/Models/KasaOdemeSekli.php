<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KasaOdemeSekli extends Model
{
    use HasFactory;

    protected $table = 'kasa_odeme_sekli';

    // public $timestamps = true;

    protected $fillable = [
        'uye_firma_id',
        'ad',
        'sira',
    ];
}

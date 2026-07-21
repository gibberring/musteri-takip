<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'baslik', 'icerik', 'hedef_rol', 'personel_id', 'servis_id', 'aktif', 'published_at', 'processed_at', 'olusturan_personel_id'
    ];
}



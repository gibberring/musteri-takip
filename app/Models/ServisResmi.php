<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServisResmi extends Model
{
    use HasFactory;

    protected $table = 'servis_resimleri';

    protected $fillable = [
        'servis_id',
        'dosya_yolu',
        'aciklama',
        'ekleyen_personel_id',
    ];

    public function servis()
    {
        return $this->belongsTo(Servis::class, 'servis_id');
    }

    public function ekleyenPersonel()
    {
        return $this->belongsTo(Personel::class, 'ekleyen_personel_id');
    }
} 
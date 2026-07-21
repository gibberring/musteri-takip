<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServisFisi extends Model
{
    use HasFactory;

    protected $table = 'servis_fisleri';
    public $timestamps = false;

    protected $fillable = [
        'servis_id',
        'pdf',
        'tarih',
        'saat',
        'olusturan_personel_id',
        'mus_imza',
        'tek_imza',
    ];

    /**
     * Fişin ait olduğu servisi getirir.
     */
    public function servis()
    {
        return $this->belongsTo(Servis::class);
    }

    /**
     * Fişi oluşturan personeli getirir.
     */
    public function olusturanPersonel()
    {
        return $this->belongsTo(Personel::class, 'olusturan_personel_id');
    }
}
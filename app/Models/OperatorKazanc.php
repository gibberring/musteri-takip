<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperatorKazanc extends Model
{
    protected $table = 'operator_kazanclari';

    protected $fillable = [
        'operator_id',
        'servis_id',
        'kazanc_tutar',
        'aciklama',
    ];

    public function operator()
    {
        return $this->belongsTo(Personel::class, 'operator_id');
    }

    public function servis()
    {
        return $this->belongsTo(Servis::class, 'servis_id');
    }
}

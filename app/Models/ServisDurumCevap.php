<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServisDurumCevap extends Model
{
    use HasFactory;

    protected $table = 'servisdurum_cevaplari';
    public $timestamps = true;

    protected $fillable = [
        'uye_firma_id',
        'soru_id',
        'cevap',
        'durumCevap0_id'
    ];

    /**
     * Cevabın ait olduğu soruyu getirir.
     */
    public function soru(): BelongsTo
    {
        return $this->belongsTo(ServisDurumSoru::class, 'soru_id');
    }

    /**
     * Cevabın bağlı olduğu cevap0 kaydını getirir.
     */
    public function durumCevap0(): BelongsTo
    {
        return $this->belongsTo(ServisDurumCevap0::class, 'durumCevap0_id');
    }
} 
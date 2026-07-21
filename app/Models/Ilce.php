<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ilce extends Model
{
    use HasFactory;

    protected $table = 'ilceler';

    // Fillable alanları (gerekirse)
    protected $fillable = ['id', 'il_id', 'ad', 'google_adi', 'bolge'];

    /**
     * İlçenin ait olduğu ili getirir.
     */
    public function il(): BelongsTo
    {
        return $this->belongsTo(Il::class, 'il_id');
    }
}

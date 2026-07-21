<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Il extends Model
{
    use HasFactory;

    protected $table = 'iller';
    public $timestamps = false; // Migration'da timestamps yok
    public $incrementing = false; // ID otomatik artmıyor
    protected $keyType = 'int'; // ID türü integer

    // Fillable alanları (gerekirse)
    protected $fillable = ['id', 'ad', 'varsayilan'];

    /**
     * İle ait ilçeleri getirir.
     */
    public function ilceler(): HasMany
    {
        return $this->hasMany(Ilce::class, 'il_id');
    }
}

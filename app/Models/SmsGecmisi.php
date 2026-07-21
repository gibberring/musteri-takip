<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsGecmisi extends Model
{
    use HasFactory;

    protected $table = 'sms_gecmisi'; // Tablo adını belirtiyoruz
    // protected $fillable = []; // Gerekirse doldurulacak alanları buraya ekleyebilirsiniz
} 
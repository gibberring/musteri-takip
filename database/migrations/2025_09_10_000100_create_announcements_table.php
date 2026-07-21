<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('baslik');
            $table->text('icerik')->nullable();
            // hedef_rol: TEKNISYEN, OPERATOR (ileride genişletilebilir)
            $table->string('hedef_rol', 32); 
            $table->boolean('aktif')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('olusturan_personel_id')->nullable();
            $table->timestamps();

            $table->index(['hedef_rol', 'aktif']);
            $table->index('published_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};



<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tnm_personel_pozisyon', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('uye_firma_id')->nullable(); // isteğe bağlı, firma ile ilişkilendirilebilir
            $table->string('ad');
            $table->boolean('izin_servisler')->default(false);
            $table->boolean('izin_musteriler')->default(false);
            $table->boolean('izin_personel')->default(false);
            $table->boolean('izin_stoklar')->default(false);
            $table->boolean('izin_kasa')->default(false);
            $table->boolean('izin_ayar')->default(false);
            $table->boolean('izin_uyelik')->default(false);
            $table->boolean('izin_servissilme')->default(false);
            $table->boolean('izin_tumfirmalarigorme')->default(false);
            $table->text('asamayetkileri')->nullable(); // JSON olarak saklanabilir
            $table->boolean('izin_topluyonlendirme')->default(false);
            $table->timestamps();

            // Eğer uye_firma_id bir foreign key ise:
            // $table->foreign('uye_firma_id')->references('id')->on('its_uye_firmalar')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tnm_personel_pozisyon');
    }
};

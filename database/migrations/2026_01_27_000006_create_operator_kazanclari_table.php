<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operator_kazanclari', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('operator_id');
            $table->unsignedBigInteger('servis_id');
            $table->decimal('kazanc_tutar', 12, 2)->default(0);
            $table->string('aciklama', 500)->nullable();
            $table->timestamps();

            $table->unique(['operator_id', 'servis_id'], 'operator_kazanclari_unique');
            $table->index(['operator_id', 'created_at'], 'operator_kazanclari_operator_tarih_idx');
            $table->index(['servis_id'], 'operator_kazanclari_servis_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operator_kazanclari');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('markalar', function (Blueprint $table) {
            $table->id();
            $table->string('ad');
        });
    }

    public function down()
    {
        Schema::dropIfExists('markalar');
    }
}; 
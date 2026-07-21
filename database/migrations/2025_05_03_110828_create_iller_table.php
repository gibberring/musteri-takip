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
        Schema::create('iller', function (Blueprint $table) {
            // SQL: [id] [int] NOT NULL - Not auto-incrementing, Laravel default is auto-incrementing ID.
            // Using standard auto-incrementing ID for simplicity, adjust if needed.
            $table->id(); 
            // If you need non-incrementing integer ID as primary:
            // $table->integer('id')->primary();
            $table->string('ad', 50)->nullable(); // SQL: [ad] [nvarchar](50) NULL
            $table->integer('varsayilan')->nullable(); // SQL: [varsayilan] [int] NULL
            // $table->timestamps(); // Kaldırıldı
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iller');
    }
};

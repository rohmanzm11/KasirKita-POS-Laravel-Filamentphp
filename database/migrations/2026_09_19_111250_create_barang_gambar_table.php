<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barang_gambar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')->constrained('barang')->cascadeOnDelete();
            $table->string('path');                    // path file gambar
            $table->string('keterangan')->nullable();  // opsional
            $table->boolean('is_utama')->default(false); // gambar utama
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();

            $table->index('barang_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barang_gambar');
    }
};
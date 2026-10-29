<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->unsignedBigInteger('kategori_id');
            $table->string('kode_barang', 50);
            $table->string('nama_barang', 150);
            $table->string('satuan', 20)->default('pcs');
            $table->decimal('harga_jual', 12, 2);
            $table->integer('stok')->default(0);
            $table->integer('stok_minimal')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'kode_barang']);
            $table->unique(['tenant_id', 'id']);
            $table->index(['tenant_id', 'nama_barang']);
            $table->index(['tenant_id', 'kategori_id']);

            // Composite FK anti lintas-tenant
            $table->foreign(['tenant_id', 'kategori_id'])
                  ->references(['tenant_id', 'id'])
                  ->on('kategori')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barang');
    }
};
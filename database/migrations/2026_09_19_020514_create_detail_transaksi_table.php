<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_transaksi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->unsignedBigInteger('transaksi_id');
            $table->unsignedBigInteger('barang_id');
            $table->integer('jumlah');
            $table->decimal('harga_beli', 12, 2)->default(0);
            $table->decimal('harga_satuan', 12, 2);
            $table->decimal('subtotal_item', 12, 2);

            $table->unique(['transaksi_id', 'barang_id']);
            $table->index(['tenant_id', 'transaksi_id']);

            $table->foreign(['tenant_id', 'transaksi_id'])
                  ->references(['tenant_id', 'id'])
                  ->on('transaksi')
                  ->cascadeOnDelete();

            $table->foreign(['tenant_id', 'barang_id'])
                  ->references(['tenant_id', 'id'])
                  ->on('barang')
                  ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_transaksi');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\JenisMutasi;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stok_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->unsignedBigInteger('barang_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('transaksi_id')->nullable();
            $table->string('jenis');
            $table->integer('jumlah'); // positif = masuk, negatif = keluar
            $table->integer('stok_sebelum');
            $table->integer('stok_sesudah');
            $table->string('keterangan', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['tenant_id', 'barang_id', 'created_at']);

            $table->foreign(['tenant_id', 'barang_id'])
                  ->references(['tenant_id', 'id'])
                  ->on('barang')
                  ->restrictOnDelete();

            $table->foreign(['tenant_id', 'user_id'])
                  ->references(['tenant_id', 'id'])
                  ->on('users')
                  ->restrictOnDelete();

            $table->foreign(['tenant_id', 'transaksi_id'])
                  ->references(['tenant_id', 'id'])
                  ->on('transaksi')
                  ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stok_log');
    }
};
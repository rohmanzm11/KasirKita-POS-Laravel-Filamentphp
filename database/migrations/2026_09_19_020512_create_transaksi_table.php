<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\MetodePembayaran;
use App\Enums\StatusTransaksi;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('kode_transaksi', 30);
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('pelanggan_id')->nullable();
            $table->unsignedBigInteger('diskon_id')->nullable();
            $table->decimal('subtotal', 12, 2);
            $table->decimal('total_diskon', 12, 2)->default(0);
            $table->decimal('total_bayar', 12, 2);
            $table->decimal('nominal_uang', 12, 2);
            $table->decimal('kembalian', 12, 2)->default(0);
            $table->string('metode_pembayaran')->default('tunai');
            $table->string('status')->default('selesai');
            $table->text('catatan')->nullable();
            $table->timestamp('tgl_transaksi')->useCurrent();

            $table->unique(['tenant_id', 'kode_transaksi']);
            $table->unique(['tenant_id', 'id']);
            $table->index(['tenant_id', 'tgl_transaksi']);
            $table->index(['tenant_id', 'user_id']);
            $table->index(['tenant_id', 'pelanggan_id']);

            // Composite FKs
            $table->foreign(['tenant_id', 'user_id'])
                  ->references(['tenant_id', 'id'])
                  ->on('users')
                  ->restrictOnDelete();

            $table->foreign(['tenant_id', 'pelanggan_id'])
                ->references(['tenant_id', 'id'])
                ->on('pelanggan')
                ->restrictOnDelete();   // atau cascadeOnDelete()

            $table->foreign(['tenant_id', 'diskon_id'])
                ->references(['tenant_id', 'id'])
                ->on('diskon')
                ->restrictOnDelete();   // atau cascadeOnDelete()
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi');
    }
};
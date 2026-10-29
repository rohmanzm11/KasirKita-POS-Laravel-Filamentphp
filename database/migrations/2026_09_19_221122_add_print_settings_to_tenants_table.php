<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->enum('ukuran_kertas_struk', ['58mm', '80mm'])
                ->default('58mm')
                ->after('logo');

            $table->text('catatan_struk')
                ->nullable()
                ->after('ukuran_kertas_struk');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['ukuran_kertas_struk', 'catatan_struk']);
        });
    }
};
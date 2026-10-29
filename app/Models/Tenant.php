<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    protected $fillable = [
        'kode',
        'nama',
        'alamat',
        'logo',
        'ukuran_kertas_struk',
        'catatan_struk',
        'no_hp',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function kategori(): HasMany
    {
        return $this->hasMany(Kategori::class);
    }

    public function barang(): HasMany
    {
        return $this->hasMany(Barang::class);
    }

    public function pelanggan(): HasMany
    {
        return $this->hasMany(Pelanggan::class);
    }

    public function diskon(): HasMany
    {
        return $this->hasMany(Diskon::class);
    }

    public function transaksi(): HasMany
    {
        return $this->hasMany(Transaksi::class);
    }
}
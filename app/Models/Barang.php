<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Barang extends Model
{
    use \App\Models\Concerns\BelongsToTenant;

    protected $table = 'barang';

    protected $fillable = [
        'tenant_id',
        'kategori_id',
        'kode_barang',
        'nama_barang',
        'satuan',
        'harga_jual',
        'stok',
        'stok_minimal',
        'is_active',
    ];

    protected $casts = [
        'harga_jual' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class);
    }

    public function detailTransaksi(): HasMany
    {
        return $this->hasMany(DetailTransaksi::class);
    }

    public function stokLogs(): HasMany
    {
        return $this->hasMany(StokLog::class);
    }

    public function gambar()
    {
        return $this->hasMany(BarangGambar::class, 'barang_id')->orderBy('urutan');
    }

    public function gambarUtama()
    {
        return $this->hasOne(BarangGambar::class, 'barang_id')->where('is_utama', true);
    }
    
}
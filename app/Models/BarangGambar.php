<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BarangGambar extends Model
{
    protected $table = 'barang_gambar';

    protected $fillable = [
        'barang_id',
        'path',
        'keterangan',
        'is_utama',
        'urutan',
    ];

    protected $casts = [
        'is_utama' => 'boolean',
    ];

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }
}
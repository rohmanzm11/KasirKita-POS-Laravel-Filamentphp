<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailTransaksi extends Model
{
    public $timestamps = false;

    protected $table = 'detail_transaksi';

    protected $fillable = [
        'tenant_id',
        'transaksi_id',
        'barang_id',
        'jumlah',
        'harga_beli',
        'harga_satuan',
        'subtotal_item',
    ];

    protected $casts = [
        'harga_beli' => 'decimal:2',
        'harga_satuan' => 'decimal:2',
        'subtotal_item' => 'decimal:2',
    ];

     protected static function booted(): void
    {
        // tenant_id selalu mengikuti transaksi induknya
        static::creating(function (self $detail) {
            $detail->tenant_id = Transaksi::withoutGlobalScopes()
                ->whereKey($detail->transaksi_id)
                ->value('tenant_id');
        });

        // subtotal item dihitung ulang di server, bukan dipercaya dari form
        static::saving(function (self $detail) {
            $detail->subtotal_item = round((float) $detail->jumlah * (float) $detail->harga_satuan, 2);
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(Transaksi::class);
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class);
    }
}
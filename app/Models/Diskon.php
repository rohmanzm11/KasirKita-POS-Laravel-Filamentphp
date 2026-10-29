<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Diskon extends Model
{
    public $timestamps = false;

    protected $table = 'diskon';

    protected $fillable = [
        'tenant_id',
        'nama_diskon',
        'persen',
        'potongan',
        'min_belanja',
        'tgl_mulai',
        'tgl_selesai',
        'is_active',
    ];

    protected $casts = [
        'persen' => 'decimal:2',
        'potongan' => 'decimal:2',
        'min_belanja' => 'decimal:2',
        'tgl_mulai' => 'date',
        'tgl_selesai' => 'date',
        'is_active' => 'boolean',
    ];

    public function hitung(float $subtotal, $tanggal = null): float
    {
        $tgl = \Carbon\Carbon::parse($tanggal ?? now())->toDateString();

        if ($this->tgl_mulai && $tgl < \Carbon\Carbon::parse($this->tgl_mulai)->toDateString()) {
            return 0;
        }
        if ($this->tgl_selesai && $tgl > \Carbon\Carbon::parse($this->tgl_selesai)->toDateString()) {
            return 0;
        }
        if ($subtotal < (float) $this->min_belanja) {
            return 0;
        }

        $nilai = $this->persen
            ? $subtotal * (float) $this->persen / 100
            : (float) $this->potongan;

        return round(min($nilai, $subtotal), 2);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function transaksi(): HasMany
    {
        return $this->hasMany(Transaksi::class);
    }
}
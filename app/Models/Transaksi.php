<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Transaksi extends Model
{
    public $timestamps = false;

    protected $table = 'transaksi';

    protected $fillable = [
        'tenant_id',
        'kode_transaksi',
        'user_id',
        'pelanggan_id',
        'diskon_id',
        'subtotal',
        'total_diskon',
        'total_bayar',
        'nominal_uang',
        'kembalian',
        'metode_pembayaran',
        'status',
        'catatan',
        'tgl_transaksi',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'total_diskon' => 'decimal:2',
        'total_bayar' => 'decimal:2',
        'nominal_uang' => 'decimal:2',
        'kembalian' => 'decimal:2',
        'tgl_transaksi' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Transaksi $transaksi) {
            // Field tenant_id di form hanya tampil untuk super admin,
            // jadi untuk owner/kasir kita isi otomatis dari user yang login.
            if (blank($transaksi->tenant_id)) {
                $transaksi->tenant_id = auth()->user()?->tenant_id;
            }

            if (blank($transaksi->kode_transaksi)) {
                $transaksi->kode_transaksi = static::generateKode(
                    (int) $transaksi->tenant_id,
                    $transaksi->tgl_transaksi ? Carbon::parse($transaksi->tgl_transaksi) : now(),
                );
            }
        });
    }

    /**
     * Format: 0001/19/09/2026 (nomor urut reset tiap hari, per tenant)
     */
    public static function generateKode(int $tenantId, ?Carbon $tanggal = null): string
    {
        $tanggal ??= now();
        $suffix = $tanggal->format('/d/m/Y');

        $last = static::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('kode_transaksi', 'like', '%' . $suffix)
            ->orderByRaw("CAST(SUBSTRING_INDEX(kode_transaksi, '/', 1) AS UNSIGNED) DESC")
            ->value('kode_transaksi');

        $next = $last ? ((int) strstr($last, '/', true)) + 1 : 1;

        return str_pad($next, 4, '0', STR_PAD_LEFT) . $suffix;
    }

    /**
     * Hitung ulang semua nilai dari detail di database.
     */
    public function hitungUlang(): void
    {
        $subtotal = round((float) $this->details()->sum('subtotal_item'), 2);

        $diskon = $this->diskon_id ? Diskon::find($this->diskon_id) : null;
        $totalDiskon = $diskon?->hitung($subtotal, $this->tgl_transaksi) ?? 0;

        $totalBayar = max($subtotal - $totalDiskon, 0);
        $tunai = $this->metode_pembayaran === 'tunai';
        $nominal = $tunai ? (float) $this->nominal_uang : $totalBayar;

        $this->forceFill([
            'subtotal'     => $subtotal,
            'total_diskon' => $totalDiskon,
            'total_bayar'  => $totalBayar,
            'nominal_uang' => $nominal,
            'kembalian'    => $tunai ? max($nominal - $totalBayar, 0) : 0,
        ])->saveQuietly();
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class);
    }

    public function diskon(): BelongsTo
    {
        return $this->belongsTo(Diskon::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(DetailTransaksi::class);
    }

    public function stokLogs(): HasMany
    {
        return $this->hasMany(StokLog::class);
    }
}
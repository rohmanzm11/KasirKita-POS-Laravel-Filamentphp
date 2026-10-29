<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kategori extends Model
{
    public $timestamps = false; // hanya created_at

    protected $table = 'kategori';

    protected $fillable = [
        'tenant_id',
        'nama_kategori',
    ];

    protected $casts = [
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
];
  

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function barang(): HasMany
    {
        return $this->hasMany(Barang::class);
    }
}
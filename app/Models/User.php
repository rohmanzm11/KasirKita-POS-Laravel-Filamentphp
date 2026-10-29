<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;
use Filament\Models\Contracts\HasAvatar;

class User extends Authenticatable implements HasAvatar
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'tenant_id',
        'name',
        'username',
        'password',
        'email',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];


    // public function isSuperAdmin(): bool
    // {
    //     return $this->hasRole('super_admin');
    // }

    public function isSuperAdmin(): bool
{
    return $this->hasRole('super admin') || $this->role === 'Super Admin';
}

    // Pengecekan Owner
public function isOwner(): bool
{
    $roleName = Str::lower($this->role ?? '');

    return $this->hasRole(['Owner', 'owner']) 
        || $roleName === 'owner';
}

// Pengecekan Kasir
public function isKasir(): bool
{
    $roleName = Str::lower($this->role ?? '');

    return $this->hasRole(['Kasir', 'kasir']) 
        || $roleName === 'kasir';
}


    public function getFilamentAvatarUrl(): ?string
    {
        // Super admin tidak punya tenant → pakai avatar default/null
        if ($this->isSuperAdmin()) {
            return null; // atau url('/images/default-avatar.png')
        }

        $logoPath = $this->tenant?->logo;

        return $logoPath
            ? \Illuminate\Support\Facades\Storage::disk('public')->url($logoPath)
            : url('/images/default-logo.png'); // fallback kalau toko belum upload logo
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function transaksi()
    {
        return $this->hasMany(Transaksi::class);
    }

    public function stokLogs()
    {
        return $this->hasMany(StokLog::class);
    }
}
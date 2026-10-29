<?php
// app/Filament/Support/TenantField.php

namespace App\Filament\Support;

use Filament\Forms\Components\Select;

class TenantField
{
    public static function make(): Select
    {
        return Select::make('tenant_id')
            ->label('Tenant')
            ->relationship('tenant', 'nama')
            ->searchable()
            ->preload()
            ->required()
            ->visible(fn () => auth()->user()?->isSuperAdmin());
    }
}
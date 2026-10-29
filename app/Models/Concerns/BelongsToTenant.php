<?php
// app/Models/Concerns/BelongsToTenant.php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::creating(function ($model) {
            $user = auth()->user();

            if (! $user) {
                return; // seeder / console
            }

            if ($user->isSuperAdmin()) {
                $model->tenant_id ??= $user->tenant_id; // boleh pilih dari form
            } else {
                $model->tenant_id = $user->tenant_id;   // owner & kasir: dipaksa
            }
        });

        static::addGlobalScope('tenant', function (Builder $builder) {
            $user = auth()->user();

            if ($user && ! $user->isSuperAdmin()) {
                $builder->where(
                    $builder->getModel()->getTable() . '.tenant_id',
                    $user->tenant_id
                );
            }
        });
    }
}
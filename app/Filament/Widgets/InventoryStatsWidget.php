<?php

namespace App\Filament\Widgets;

use App\Models\Barang;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class InventoryStatsWidget extends BaseWidget
{
    protected static ?int $sort = -2; // tampil setelah WelcomeWidget

    public static function canView(): bool
    {
        $user = Auth::user();

        return $user && ! $user->isKasir();
    }

    protected function getStats(): array
    {
        $user = Auth::user();

        $query = Barang::query();

        if (! $user->isSuperAdmin()) {
            $query->where('tenant_id', $user->tenant_id);
        }

        $s = $query
            ->selectRaw('
                COUNT(*) as total,
                COALESCE(SUM(CASE WHEN stok <= 0 THEN 1 ELSE 0 END), 0) as habis,
                COALESCE(SUM(CASE WHEN stok > 0 AND stok <= stok_minimal THEN 1 ELSE 0 END), 0) as menipis,
                COALESCE(SUM(stok * harga_jual), 0) as nilai
            ')
            ->first();

        return [
            Stat::make('Total Barang', number_format($s->total ?? 0))
                ->icon('heroicon-o-cube')
                ->color('primary'),

            Stat::make('Stok Menipis', number_format($s->menipis ?? 0))
                ->icon('heroicon-o-exclamation-triangle')
                ->color('warning'),

            Stat::make('Stok Habis', number_format($s->habis ?? 0))
                ->icon('heroicon-o-x-circle')
                ->color('danger'),

            Stat::make('Nilai Stok', 'Rp ' . number_format($s->nilai ?? 0, 0, ',', '.'))
                ->icon('heroicon-o-banknotes')
                ->color('success'),
        ];
    }
}
<?php

namespace App\Filament\Widgets;

use App\Models\Transaksi;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class PemasukanHariIniWidget extends BaseWidget
{
    protected static ?int $sort = -1; // tampil setelah InventoryStatsWidget

    public static function canView(): bool
    {
        $user = Auth::user();

        // Kasir tidak boleh lihat laporan (sama seperti LaporanPemasukan::canViewAny)
        return $user && (($user->isSuperAdmin() ?? false) || $user->isOwner());
    }

    protected function getStats(): array
    {
        $user  = Auth::user();
        $hariIni = today();

        $query = Transaksi::query()
            ->whereBetween('tgl_transaksi', [$hariIni->copy()->startOfDay(), $hariIni->copy()->endOfDay()]);

        if (! $user->isSuperAdmin()) {
            $query->where('tenant_id', $user->tenant_id);
        }

        $rows = (clone $query)
            ->selectRaw('metode_pembayaran, COUNT(*) as jumlah, SUM(total_bayar) as total')
            ->groupBy('metode_pembayaran')
            ->get();

        $totalHariIni  = (float) $rows->sum('total');
        $jumlahTransaksi = (int) $rows->sum('jumlah');
        $rataRata      = $jumlahTransaksi > 0 ? $totalHariIni / $jumlahTransaksi : 0;

        $tunai = (float) ($rows->firstWhere('metode_pembayaran', 'tunai')->total ?? 0);
        $qris  = (float) ($rows->firstWhere('metode_pembayaran', 'qris')->total ?? 0);
        $debit = (float) ($rows->firstWhere('metode_pembayaran', 'debit')->total ?? 0);

        $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');

        return [
            Stat::make('Pemasukan Hari Ini', $rp($totalHariIni))
                ->description($jumlahTransaksi . ' transaksi')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->icon('heroicon-o-currency-dollar')
                ->color('success'),

            Stat::make('Rata-rata / Transaksi', $rp($rataRata))
                ->icon('heroicon-o-chart-bar')
                ->color('info'),

            Stat::make('Tunai', $rp($tunai))
                ->icon('heroicon-o-banknotes')
                ->color('gray'),

            Stat::make('QRIS', $rp($qris))
                ->icon('heroicon-o-qr-code')
                ->color('primary'),
        ];
    }
}
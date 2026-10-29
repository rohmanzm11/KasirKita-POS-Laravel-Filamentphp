<?php

namespace App\Exports;

use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanPemasukanExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles, WithColumnFormatting, WithTitle
{
    public function __construct(
        protected Carbon $dari,
        protected Carbon $sampai,
    ) {}

    /** Query dipakai bersama oleh Excel, PDF, dan tabel di halaman */
    public static function query(Carbon $dari, Carbon $sampai): Builder
    {
        return Transaksi::query()
            ->with(['user', 'pelanggan'])
            ->where('status', 'selesai')
            ->whereBetween('tgl_transaksi', [$dari, $sampai]);
    }

    public function collection(): Collection
    {
        $rows = self::query($this->dari, $this->sampai)
            ->orderBy('tgl_transaksi')
            ->get();

        $data = $rows->map(fn ($t, $i) => [
            $i + 1,
            $t->kode_transaksi,
            Carbon::parse($t->tgl_transaksi)->format('d/m/Y H:i'),
            $t->user?->name,
            $t->pelanggan?->nama ?? 'Umum',
            strtoupper($t->metode_pembayaran),
            (float) $t->subtotal,
            (float) $t->total_diskon,
            (float) $t->total_bayar,
        ]);

        // Baris total di paling bawah
        $data->push([
            '', '', '', '', '', 'TOTAL',
            (float) $rows->sum('subtotal'),
            (float) $rows->sum('total_diskon'),
            (float) $rows->sum('total_bayar'),
        ]);

        return $data;
    }

    public function headings(): array
    {
        return ['No', 'Kode', 'Tanggal', 'Kasir', 'Pelanggan', 'Metode', 'Subtotal', 'Diskon', 'Total Bayar'];
    }

    public function columnFormats(): array
    {
        return [
            'G' => '#,##0',
            'H' => '#,##0',
            'I' => '#,##0',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $last = $sheet->getHighestRow();

        $sheet->getStyle('A1:I1')->getFont()->setBold(true);
        $sheet->getStyle("A{$last}:I{$last}")->getFont()->setBold(true);

        return [];
    }

    public function title(): string
    {
        return 'Laporan Pemasukan';
    }
}
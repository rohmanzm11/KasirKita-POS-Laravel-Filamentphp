<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KategoriReferenceSheet implements FromArray, WithHeadings, WithTitle, WithStyles, WithColumnWidths
{
    public function __construct(protected array $kategoris) {}

    public function title(): string
    {
        return 'Kategori';
    }

    public function headings(): array
    {
        return ['nama_kategori'];
    }

    public function array(): array
    {
        return empty($this->kategoris)
            ? [['(belum ada kategori, tambahkan dahulu)']]
            : array_map(fn ($k) => [$k], $this->kategoris);
    }

    public function columnWidths(): array
    {
        return ['A' => 32];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '374151'],
                ],
            ],
        ];
    }
}
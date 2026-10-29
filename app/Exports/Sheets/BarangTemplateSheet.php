<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BarangTemplateSheet implements FromArray, WithHeadings, WithTitle, WithStyles, WithColumnWidths, WithEvents
{
    /** Jumlah baris kosong yang diberi dropdown validasi di bawah baris contoh. */
    protected const LAST_ROW = 300;

    protected int $jumlahKategori;

    public function __construct(array $kategoris)
    {
        $this->jumlahKategori = max(count($kategoris), 1);
    }

    public function title(): string
    {
        return 'Template';
    }

    public function headings(): array
    {
        return [
            'kode_barang', 'nama_barang', 'kategori', 'satuan',
            'harga_jual', 'stok', 'stok_minimal', 'is_active',
        ];
    }

    public function array(): array
    {
        return [
            ['BRG-001', 'Contoh Nama Barang', '(pilih dari sheet Kategori)', 'pcs', 15000, 100, 10, '1'],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 16, 'B' => 32, 'C' => 26, 'D' => 12,
            'E' => 14, 'F' => 10, 'G' => 14, 'H' => 12,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '16A34A'],
                ],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ],
            2 => [
                'font' => ['italic' => true, 'color' => ['rgb' => '9CA3AF']],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->freezePane('A2');
                $sheet->setAutoFilter('A1:H1');
                $sheet->getRowDimension(1)->setRowHeight(22);

                // dropdown kategori -> ambil daftar dari sheet "Kategori" kolom A
                $this->applyListValidation(
                    $sheet,
                    'C2:C' . self::LAST_ROW,
                    '=Kategori!$A$2:$A$' . (1 + $this->jumlahKategori),
                    'Kategori tidak valid',
                    'Pilih salah satu kategori dari daftar (lihat sheet "Kategori").'
                );

                // dropdown satuan
                $this->applyListValidation(
                    $sheet,
                    'D2:D' . self::LAST_ROW,
                    '"pcs,dos,lusin"',
                    'Satuan tidak valid',
                    'Pilih salah satu: pcs, dos, lusin.'
                );

                // dropdown is_active
                $this->applyListValidation(
                    $sheet,
                    'H2:H' . self::LAST_ROW,
                    '"1,0"',
                    'Status tidak valid',
                    'Isi 1 untuk aktif, 0 untuk tidak aktif (kosongkan = aktif).'
                );
            },
        ];
    }

    protected function applyListValidation(
        Worksheet $sheet,
        string $range,
        string $formula1,
        string $errorTitle,
        string $message
    ): void {
        [$start, $end] = explode(':', $range);
        preg_match('/^([A-Z]+)(\d+)$/', $start, $startMatch);
        preg_match('/^([A-Z]+)(\d+)$/', $end, $endMatch);

        $column = $startMatch[1];
        $from   = (int) $startMatch[2];
        $to     = (int) $endMatch[2];

        $validation = new DataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_STOP);
        $validation->setAllowBlank(true);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setErrorTitle($errorTitle);
        $validation->setError($message);
        $validation->setPromptTitle('Pilihan');
        $validation->setPrompt($message);
        $validation->setFormula1($formula1);

        for ($row = $from; $row <= $to; $row++) {
            $sheet->setDataValidation($column . $row, clone $validation);
        }
    }
}
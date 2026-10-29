<?php

namespace App\Exports;

use App\Exports\Sheets\BarangTemplateSheet;
use App\Exports\Sheets\KategoriReferenceSheet;
use App\Models\Kategori;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

// Di Laravel Excel v4, kelas yang memakai WithMultipleSheets wajib juga
// mengimplementasikan marker interface Export secara eksplisit,
// karena WithMultipleSheets sendiri bukan data-source concern.
class BarangImportTemplateExport implements Export, WithMultipleSheets
{
    public function __construct(protected int $tenantId) {}

    public function sheets(): array
    {
        $kategoris = Kategori::where('tenant_id', $this->tenantId)
            ->orderBy('nama_kategori')
            ->pluck('nama_kategori')
            ->filter()
            ->values()
            ->all();

        return [
            new BarangTemplateSheet($kategoris),
            new KategoriReferenceSheet($kategoris),
        ];
    }
}
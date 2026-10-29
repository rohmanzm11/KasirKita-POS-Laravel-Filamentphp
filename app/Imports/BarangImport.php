<?php

namespace App\Imports;

use App\Models\Barang;
use App\Models\Kategori;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BarangImport implements ToCollection, WithHeadingRow
{
    public const MAX_ROWS = 2000;

    /** Nilai satuan yang valid, harus selaras dengan Select 'satuan' di form Barang. */
    public const SATUAN_VALID = ['pcs', 'dos', 'lusin'];

    public int $berhasil = 0;

    /** @var array<int, string> */
    public array $gagal = [];

    public function __construct(protected int $tenantId) {}

    public function collection(Collection $rows): void
    {
        if ($rows->count() > self::MAX_ROWS) {
            $this->gagal[] = 'Maksimal ' . self::MAX_ROWS . ' baris per import.';

            return;
        }

        $kategoriMap = Kategori::where('tenant_id', $this->tenantId)
            ->get(['id', 'nama_kategori'])
            ->mapWithKeys(fn ($k) => [
                mb_strtolower($this->bersihkan((string) $k->nama_kategori)) => $k->id,
            ]);

        foreach ($rows as $index => $row) {
            $baris = $index + 2; // baris 1 = header

            $kode         = $this->bersihkan((string) ($row['kode_barang'] ?? ''));
            $nama         = $this->bersihkan((string) ($row['nama_barang'] ?? ''));
            $namaKategori = $this->bersihkan((string) ($row['kategori'] ?? ''));
            $satuan       = mb_strtolower($this->bersihkan((string) ($row['satuan'] ?? '')));

            // lewati baris kosong
            if ($kode === '' && $nama === '') {
                continue;
            }

            if ($kode === '' || $nama === '') {
                $this->gagal[] = "Baris {$baris}: kode_barang dan nama_barang wajib diisi.";

                continue;
            }

            $kategoriId = $kategoriMap[mb_strtolower($namaKategori)] ?? null;

            if (! $kategoriId) {
                $this->gagal[] = "Baris {$baris}: kategori '{$namaKategori}' kosong atau tidak ditemukan.";

                continue;
            }

            if ($satuan !== '' && ! in_array($satuan, self::SATUAN_VALID, true)) {
                $this->gagal[] = "Baris {$baris}: satuan '{$satuan}' tidak valid. Gunakan: " . implode('/', self::SATUAN_VALID) . '.';

                continue;
            }

            $numerikGagal = false;

            foreach (['harga_jual', 'stok', 'stok_minimal'] as $field) {
                $nilai = $row[$field] ?? null;

                if ($nilai !== null && $nilai !== '' && ! is_numeric($nilai)) {
                    $this->gagal[] = "Baris {$baris}: kolom {$field} harus berupa angka.";
                    $numerikGagal = true;

                    break;
                }
            }

            if ($numerikGagal) {
                continue;
            }

            try {
                Barang::updateOrCreate(
                    [
                        'tenant_id'   => $this->tenantId,
                        'kode_barang' => $kode,
                    ],
                    [
                        'nama_barang'  => $nama,
                        'kategori_id'  => $kategoriId,
                        'satuan'       => $satuan ?: 'pcs',
                        'harga_jual'   => (float) ($row['harga_jual'] ?? 0),
                        'stok'         => (int) ($row['stok'] ?? 0),
                        'stok_minimal' => (int) ($row['stok_minimal'] ?? 0),
                        'is_active'    => $this->toBool($row['is_active'] ?? null),
                    ]
                );

                $this->berhasil++;
            } catch (\Throwable $e) {
                $this->gagal[] = "Baris {$baris}: " . $e->getMessage();
            }
        }
    }

    protected function toBool(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return true; // default aktif
        }

        return in_array(
            mb_strtolower($this->bersihkan((string) $value)),
            ['1', 'true', 'ya', 'y', 'yes', 'aktif'],
            true
        );
    }

    /**
     * Bersihkan spasi ganda, non-breaking space, dan whitespace di awal/akhir teks
     * dari input Excel/CSV (sumber umum kegagalan pencocokan kategori/kode).
     */
    protected function bersihkan(string $teks): string
    {
        $teks = str_replace("\xC2\xA0", ' ', $teks); // non-breaking space

        return trim(preg_replace('/\s+/u', ' ', $teks) ?? $teks); // rapikan spasi ganda
    }
}
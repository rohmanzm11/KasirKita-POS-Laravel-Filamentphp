<?php

namespace App\Filament\Pages;

use App\Exports\BarangImportTemplateExport;
use App\Filament\Resources\BarangResource;
use App\Imports\BarangImport;
use App\Models\Barang;
use App\Models\Kategori;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class Inventory extends Page
{
    use WithPagination;

    protected static ?string $navigationIcon  = 'heroicon-o-archive-box';
    // protected static ?string $navigationGroup = 'Tenant';
    protected static ?string $navigationLabel = 'Inventory';
    protected static ?string $title           = 'Inventory';
    protected static ?string $slug            = 'Inventory';
    protected static ?int $navigationSort     = 1;

    protected static string $view = 'filament.pages.inventory';

    public static function canViewAny(): bool
{
    /** @var \App\Models\User|null $user */
    $user = auth()->user();

    return $user?->isSuperAdmin() || $user?->isOwner() || $user?->isKasir();
}

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public ?string $kategori = null;

    /** all | aman | menipis | habis | nonaktif */
    #[Url]
    public string $status = 'all';

    /** nama_asc | nama_desc | stok_asc | stok_desc | harga_asc | harga_desc | terbaru */
    #[Url]
    public string $sort = 'nama_asc';

    /** grid | list */
    #[Url]
    public string $tampilan = 'grid';

    public function updated($property): void
    {
        if (in_array($property, ['search', 'kategori', 'status', 'sort', 'tampilan'])) {
            $this->resetPage();
        }
    }

    public function resetFilter(): void
    {
        $this->reset(['search', 'kategori', 'status']);
        $this->resetPage();
    }

    protected function getHeaderActions(): array
    {
        return [
            // ------------- DOWNLOAD TEMPLATE -------------
            Action::make('downloadTemplateBarang')
                ->label('Download Template')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->tooltip('Unduh template Excel berisi header, contoh baris, dan dropdown kategori/satuan/status')
                ->action(fn () => Excel::download(
                    new BarangImportTemplateExport(Auth::user()->tenant_id),
                    'template-import-barang.xlsx'
                )),

            // ------------- IMPORT EXCEL -------------
            Action::make('importBarang')
                ->label('Import Barang')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->modalHeading('Import Data Barang')
                ->modalSubmitActionLabel('Import')
                ->modalWidth(MaxWidth::FourExtraLarge)
                ->form([
                    Forms\Components\Grid::make([
                        'default' => 1,
                        'md'      => 5,
                    ])->schema([
                        // -------- KOLOM KIRI: DAFTAR KATEGORI --------
                        Forms\Components\Group::make([
                            Forms\Components\View::make('filament.forms.components.kategori-list')
                                ->viewData(fn () => [
                                    'kategoris' => Kategori::where('tenant_id', Auth::user()->tenant_id)
                                        ->orderBy('nama_kategori')
                                        ->pluck('nama_kategori')
                                        ->all(),
                                ]),
                        ])->columnSpan(['default' => 1, 'md' => 2]),

                        // -------- KOLOM KANAN: FILE UPLOAD --------
                        Forms\Components\Group::make([
                            Forms\Components\Placeholder::make('info_template')
                                ->label('')
                                ->content(new HtmlString(
                                    '<div class="flex items-start gap-2 rounded-lg bg-primary-50 dark:bg-primary-500/10 px-3 py-2 text-xs text-primary-700 dark:text-primary-300">
                                        <span>Belum punya filenya? Tutup dialog ini lalu klik <strong>Download Template</strong> untuk mengunduh format Excel yang sudah siap pakai (lengkap dengan dropdown kategori &amp; satuan).</span>
                                    </div>'
                                )),

                            Forms\Components\FileUpload::make('file')
                                ->label('File Excel / CSV')
                                ->required()
                                ->disk('local')
                                ->directory('imports/barang')
                                ->visibility('private')
                                ->maxSize(10240)
                                ->acceptedFileTypes([
                                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
                                    'application/vnd.ms-excel',                                          // .xls & .csv (Windows)
                                    'text/csv',
                                    'text/plain',
                                    'application/csv',
                                ])
                                // pertahankan ekstensi asli agar Laravel Excel bisa mendeteksi tipe file
                                ->getUploadedFileNameForStorageUsing(
                                    fn (TemporaryUploadedFile $file): string =>
                                        Str::uuid() . '.' . strtolower($file->getClientOriginalExtension())
                                )
                                ->uploadingMessage('Mengunggah file…')
                                ->helperText('Format: .xlsx, .xls, atau .csv (maks. ' . BarangImport::MAX_ROWS . ' baris)'),
                        ])->columnSpan(['default' => 1, 'md' => 3]),
                    ]),
                ])
                ->action(function (array $data): void {
                    $path   = $data['file'];
                    $import = new BarangImport(Auth::user()->tenant_id);

                    try {
                        Excel::import($import, $path, 'local');
                    } catch (\Throwable $e) {
                        report($e);

                        Notification::make()
                            ->title('Import gagal')
                            ->body('File tidak dapat dibaca: ' . $e->getMessage())
                            ->danger()
                            ->persistent()
                            ->send();

                        return;
                    } finally {
                        Storage::disk('local')->delete($path);
                    }

                    $pesan = "{$import->berhasil} baris berhasil diimpor.";

                    if ($import->gagal) {
                        $pesan .= ' ' . count($import->gagal) . ' baris gagal:<br>'
                            . collect($import->gagal)->take(10)->map(fn ($m) => '• ' . e($m))->implode('<br>');
                    }

                    Notification::make()
                        ->title('Import selesai')
                        ->body(new HtmlString($pesan))
                        ->color($import->gagal ? 'warning' : 'success')
                        ->icon($import->gagal ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-check-circle')
                        ->persistent()
                        ->send();

                    // statistik & daftar barang dihitung ulang otomatis saat render berikutnya
                    $this->resetPage();
                }),

            Action::make('tambahBarang')
                ->label('Tambah Barang')
                ->icon('heroicon-o-plus')
                ->url(BarangResource::getUrl('create')),
        ];
    }

    protected function baseQuery()
    {
        return Barang::query()->where('tenant_id', Auth::user()->tenant_id);
    }

    protected function getViewData(): array
    {
        $s = $this->baseQuery()
            ->selectRaw('
                COUNT(*) as total,
                COALESCE(SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END), 0) as aktif,
                COALESCE(SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END), 0) as nonaktif,
                COALESCE(SUM(CASE WHEN stok <= 0 THEN 1 ELSE 0 END), 0) as habis,
                COALESCE(SUM(CASE WHEN stok > 0 AND stok <= stok_minimal THEN 1 ELSE 0 END), 0) as menipis,
                COALESCE(SUM(stok * harga_jual), 0) as nilai
            ')
            ->first();

        $stats = [
            'total'    => (int) $s->total,
            'aktif'    => (int) $s->aktif,
            'nonaktif' => (int) $s->nonaktif,
            'habis'    => (int) $s->habis,
            'menipis'  => (int) $s->menipis,
            'aman'     => max(0, (int) $s->total - (int) $s->habis - (int) $s->menipis),
            'nilai'    => (float) $s->nilai,
        ];

        [$kolom, $arah] = match ($this->sort) {
            'nama_desc'  => ['nama_barang', 'desc'],
            'stok_asc'   => ['stok', 'asc'],
            'stok_desc'  => ['stok', 'desc'],
            'harga_asc'  => ['harga_jual', 'asc'],
            'harga_desc' => ['harga_jual', 'desc'],
            'terbaru'    => ['id', 'desc'],
            default      => ['nama_barang', 'asc'],
        };

        $barangs = $this->baseQuery()
            ->with(['kategori', 'gambarUtama'])
            ->when($this->search !== '', function ($q) {
                $q->where(function ($q) {
                    $q->where('nama_barang', 'like', "%{$this->search}%")
                      ->orWhere('kode_barang', 'like', "%{$this->search}%");
                });
            })
            ->when($this->kategori, fn ($q, $k) => $q->where('kategori_id', $k))
            ->when($this->status !== 'all', function ($q) {
                match ($this->status) {
                    'habis'    => $q->where('stok', '<=', 0),
                    'menipis'  => $q->where('stok', '>', 0)->whereColumn('stok', '<=', 'stok_minimal'),
                    'aman'     => $q->whereColumn('stok', '>', 'stok_minimal'),
                    'nonaktif' => $q->where('is_active', false),
                    default    => null,
                };
            })
            ->orderBy($kolom, $arah)
            ->paginate($this->tampilan === 'grid' ? 12 : 20);

        return [
            'stats'     => $stats,
            'barangs'   => $barangs,
            'kategoris' => Kategori::where('tenant_id', Auth::user()->tenant_id)
                ->orderBy('nama_kategori')
                ->pluck('nama_kategori', 'id'),
        ];
    }

    /**
     * Metadata status stok. 'tone' dipetakan ke class CSS .inv-t-* di view.
     */
    public function stokMeta(Barang $barang): array
    {
        $target = max($barang->stok_minimal * 2, 1);
        $pct    = (int) min(100, max(0, round($barang->stok / $target * 100)));

        if ($barang->stok <= 0) {
            return ['label' => 'Habis', 'pct' => 0, 'tone' => 'danger'];
        }

        if ($barang->stok <= $barang->stok_minimal) {
            return ['label' => 'Menipis', 'pct' => $pct, 'tone' => 'warn'];
        }

        return ['label' => 'Aman', 'pct' => $pct, 'tone' => 'ok'];
    }
}
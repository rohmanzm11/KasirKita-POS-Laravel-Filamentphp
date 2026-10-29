<?php

namespace App\Filament\Pages;

use App\Exports\LaporanPemasukanExport;
use App\Filament\Widgets\PemasukanHariIniWidget;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn\TextColumnSize;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\FontWeight;

class LaporanPemasukan extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    // protected static ?string $navigationGroup = 'Transaksi';
    protected static ?string $navigationLabel = 'Laporan Pemasukan';
    protected static ?string $title = 'Laporan Pemasukan';
    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.laporan-pemasukan';

    public ?array $data = [];

    // Kasir tidak boleh melihat laporan
    public static function canViewAny(): bool
{
    /** @var \App\Models\User|null $user */
    $user = auth()->user();

    return $user?->isSuperAdmin() || $user?->isOwner();
}
    public function mount(): void
    {
        $this->form->fill([
            'periode' => 'harian',
            'tanggal' => today()->toDateString(),
        ]);
    }

    // ==================== FILTER PERIODE ====================
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\ToggleButtons::make('periode')
                    ->label('Periode')
                    ->options([
                        'harian'   => 'Harian',
                        'mingguan' => 'Mingguan',
                        'bulanan'  => 'Bulanan',
                        'tahunan'  => 'Tahunan',
                    ])
                    ->inline()
                    ->grouped()
                    ->live()
                    ->afterStateUpdated(fn () => $this->resetPage()),

                Forms\Components\DatePicker::make('tanggal')
                    ->label('Tanggal acuan')
                    ->helperText('Minggu/bulan/tahun dihitung dari tanggal ini.')
                    ->native(false)
                    ->maxDate(today())
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn () => $this->resetPage()),
            ])
            ->columns(2)
            ->statePath('data');
    }

    /** @return array{0: Carbon, 1: Carbon} */
    public function getRange(): array
    {
        $tgl = Carbon::parse($this->data['tanggal'] ?? today());

        return match ($this->data['periode'] ?? 'harian') {
            'mingguan' => [$tgl->copy()->startOfWeek(), $tgl->copy()->endOfWeek()],
            'bulanan'  => [$tgl->copy()->startOfMonth(), $tgl->copy()->endOfMonth()],
            'tahunan'  => [$tgl->copy()->startOfYear(), $tgl->copy()->endOfYear()],
            default    => [$tgl->copy()->startOfDay(), $tgl->copy()->endOfDay()],
        };
    }

    public function getPeriodeLabel(): string
    {
        [$dari, $sampai] = $this->getRange();

        return match ($this->data['periode'] ?? 'harian') {
            'harian'  => $dari->translatedFormat('l, d F Y'),
            'bulanan' => $dari->translatedFormat('F Y'),
            'tahunan' => 'Tahun ' . $dari->format('Y'),
            default   => $dari->translatedFormat('d M Y') . ' - ' . $sampai->translatedFormat('d M Y'),
        };
    }

    public function getSubheading(): ?string
    {
        return $this->getPeriodeLabel();
    }

    /** Ringkasan untuk kartu di atas tabel (mengikuti periode yang dipilih) */
    public function getRingkasan(): array
    {
        [$dari, $sampai] = $this->getRange();

        $rows = LaporanPemasukanExport::query($dari, $sampai)
            ->setEagerLoads([])
            ->selectRaw('metode_pembayaran, COUNT(*) as jumlah, SUM(total_bayar) as total')
            ->groupBy('metode_pembayaran')
            ->get();

        $total  = (float) $rows->sum('total');
        $jumlah = (int) $rows->sum('jumlah');

        return [
            'total'  => $total,
            'jumlah' => $jumlah,
            'rata'   => $jumlah > 0 ? $total / $jumlah : 0,
            'metode' => $rows->keyBy('metode_pembayaran'),
        ];
    }

    // ==================== WIDGET ====================
    protected function getHeaderWidgets(): array
    {
        return [PemasukanHariIniWidget::class];
    }

    // ==================== TABEL (LIST KECIL) ====================
   public function table(Table $table): Table
    {
        $rp = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');

        return $table
            ->query(function (): Builder {
                [$dari, $sampai] = $this->getRange();

                return LaporanPemasukanExport::query($dari, $sampai);
            })
            ->heading('Rincian Transaksi')
            ->columns([
                // Kode + tanggal digabung jadi satu kolom (2 baris)
                Tables\Columns\TextColumn::make('kode_transaksi')
                    ->label('Transaksi')
                    ->fontFamily(FontFamily::Mono)
                    ->weight(FontWeight::Medium)
                    ->size(TextColumnSize::Small)
                    ->description(fn ($record) => Carbon::parse($record->tgl_transaksi)->translatedFormat('d M Y · H:i'))
                    ->searchable()
                    ->sortable(query: fn (Builder $query, string $direction) => $query->orderBy('tgl_transaksi', $direction)),

                // Kasir + pelanggan digabung jadi satu kolom
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Kasir')
                    ->size(TextColumnSize::Small)
                    ->description(fn ($record) => $record->pelanggan?->nama ?? 'Pelanggan umum')
                    ->searchable()
                    ->visibleFrom('md'),

                Tables\Columns\TextColumn::make('metode_pembayaran')
                    ->label('Metode')
                    ->badge()
                    ->size(TextColumnSize::Small)
                    ->formatStateUsing(fn (string $state) => strtoupper($state))
                    ->icon(fn (string $state) => match ($state) {
                        'tunai' => 'heroicon-m-banknotes',
                        'qris'  => 'heroicon-m-qr-code',
                        'debit' => 'heroicon-m-credit-card',
                        default => null,
                    })
                    ->color(fn (string $state) => match ($state) {
                        'tunai' => 'success',
                        'qris'  => 'info',
                        'debit' => 'warning',
                        default => 'gray',
                    }),

                // Disembunyikan default, bisa dimunculkan lewat toggle kolom
                Tables\Columns\TextColumn::make('total_diskon')
                    ->label('Diskon')
                    ->size(TextColumnSize::Small)
                    ->color('danger')
                    ->formatStateUsing(fn ($state) => (float) $state > 0 ? '- ' . $rp($state) : '-')
                    ->alignEnd()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('total_bayar')
                    ->label('Total')
                    ->weight(FontWeight::Bold)
                    ->formatStateUsing(fn ($state) => $rp($state))
                    ->alignEnd()
                    ->sortable()
                    ->summarize(
                        Sum::make()
                            ->label('Total')
                            ->formatStateUsing(fn ($state) => $rp($state))
                    ),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('metode_pembayaran')
                    ->label('Metode')
                    ->options(['tunai' => 'Tunai', 'qris' => 'QRIS', 'debit' => 'Debit']),
            ])
            ->defaultSort('tgl_transaksi', 'desc')
            ->striped()
            ->searchPlaceholder('Cari kode atau kasir...')
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10)
            ->extremePaginationLinks()
            ->emptyStateHeading('Belum ada pemasukan di periode ini')
            ->emptyStateDescription('Coba ganti periode atau tanggal acuan.')
            ->emptyStateIcon('heroicon-o-banknotes');
    }

    // ==================== EXPORT ====================
    protected function getHeaderActions(): array
    {
        return [
            Action::make('excel')
                ->label('Excel')
                ->icon('heroicon-o-table-cells')
                ->color('success')
                ->action(function () {
                    [$dari, $sampai] = $this->getRange();

                    return Excel::download(
                        new LaporanPemasukanExport($dari, $sampai),
                        'laporan-pemasukan-' . $dari->format('Ymd') . '-' . $sampai->format('Ymd') . '.xlsx'
                    );
                }),

            Action::make('pdf')
                ->label('PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('danger')
                ->action(function () {
                    [$dari, $sampai] = $this->getRange();

                    $rows = LaporanPemasukanExport::query($dari, $sampai)
                        ->orderBy('tgl_transaksi')
                        ->get();

                    $pdf = Pdf::loadView('exports.laporan-pemasukan-pdf', [
                        'rows'  => $rows,
                        'label' => $this->getPeriodeLabel(),
                        'toko'  => auth()->user()->tenant?->nama ?? config('app.name'),
                    ])->setPaper('a4', 'portrait');

                    return response()->streamDownload(
                        fn () => print($pdf->output()),
                        'laporan-pemasukan-' . $dari->format('Ymd') . '-' . $sampai->format('Ymd') . '.pdf'
                    );
                }),
        ];
    }
}
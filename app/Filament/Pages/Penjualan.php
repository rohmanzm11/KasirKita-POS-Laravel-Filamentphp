<?php

namespace App\Filament\Pages;

use App\Models\Barang;
use App\Models\Diskon;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\Pelanggan;
use App\Models\Tenant;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Penjualan extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    // protected static ?string $navigationGroup = 'Tenant';
    protected static ?string $navigationLabel = 'Penjualan';
    protected static ?int $navigationSort = 0;
    protected static ?string $title = 'Penjualan (Kasir)';

    protected static string $view = 'filament.pages.penjualan';

    public static function canViewAny(): bool
{
    /** @var \App\Models\User|null $user */
    $user = auth()->user();

    // Mengembalikan true HANYA jika user adalah Kasir
    return $user?->isKasir() ?? false;
}

    /**
     * State form sisi kanan (informasi transaksi).
     */
    public ?array $data = [];

    /**
     * Keranjang belanja, keyed by barang_id.
     * @var array<int, array{barang_id:int, kode:string, nama:string, gambar:?string, harga:float, jumlah:int, stok:int}>
     */
    public array $cart = [];

    public string $search = '';

    public function mount(): void
    {
        $user = auth()->user();

        $this->form->fill([
            'tenant_id'         => $user->isSuperAdmin() ? null : $user->tenant_id,
            'user_id'           => $user->isKasir() ? $user->id : null,
            'pelanggan_id'      => null,
            'diskon_id'         => null,
            'metode_pembayaran' => 'tunai',
            'tgl_transaksi'     => now(),
            'nominal_uang'      => 0,
            'catatan'           => null,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('tenant_id')
                    ->label('Tenant')
                    ->options(fn () => Tenant::query()->orderBy('nama')->pluck('nama', 'id'))
                    ->searchable()
                    ->preload()
                    ->live()
                    ->required()
                    ->afterStateUpdated(function (Set $set) {
                        $set('user_id', null);
                        $set('pelanggan_id', null);
                        $set('diskon_id', null);
                        $this->cart = [];
                    })
                    ->visible(fn () => auth()->user()?->isSuperAdmin()),

                Select::make('user_id')
                    ->label('Kasir')
                    ->options(function (Get $get) {
                        $user = auth()->user();
                        $query = User::query();

                        if ($user->isSuperAdmin()) {
                            if ($get('tenant_id')) {
                                $query->where('tenant_id', $get('tenant_id'));
                            }
                        } else {
                            $query->where('tenant_id', $user->tenant_id);
                        }

                        return $query->orderBy('name')->pluck('name', 'id');
                    })
                    ->default(fn () => auth()->id())
                    ->disabled(fn () => auth()->user()->isKasir())
                    ->dehydrated()
                    ->searchable()
                    ->preload()
                    ->required(),

                // ===================== PELANGGAN (dengan modal tambah baru) =====================
                Select::make('pelanggan_id')
                    ->label('Pelanggan')
                    ->options(function (Get $get) {
                        $query = Pelanggan::query();

                        if ($get('tenant_id')) {
                            $query->where('tenant_id', $get('tenant_id'));
                        }

                        return $query->orderBy('nama')->pluck('nama', 'id');
                    })
                    ->searchable()
                    ->preload()
                    ->placeholder('Pelanggan umum')
                    ->nullable()
                    // Menampilkan tombol "+" di sebelah select yang membuka modal
                    // untuk menambahkan pelanggan baru tanpa keluar dari halaman kasir.
                    ->createOptionForm([
                        TextInput::make('nama')
                            ->label('Nama Pelanggan')
                            ->required()
                            ->maxLength(100),

                        TextInput::make('no_hp')
                            ->label('No. HP')
                            ->tel()
                            ->maxLength(20),
                    ])
                    ->createOptionUsing(function (array $data, Get $get) {
                        $user = auth()->user();

                        $pelanggan = Pelanggan::create([
                            'tenant_id' => $user->isSuperAdmin()
                                ? $get('tenant_id')
                                : $user->tenant_id,
                            'nama'   => $data['nama'],
                            'no_hp'  => $data['no_hp'] ?? null,
                        ]);

                        Notification::make()
                            ->success()
                            ->title('Pelanggan baru ditambahkan')
                            ->send();

                        return $pelanggan->getKey();
                    })
                    ->createOptionModalHeading('Tambah Pelanggan Baru'),

                Select::make('diskon_id')
                    ->label('Diskon')
                    ->options(function (Get $get) {
                        $query = Diskon::query();

                        if ($get('tenant_id')) {
                            $query->where('tenant_id', $get('tenant_id'));
                        }

                        $query->where('is_active', true)
                            ->where(fn ($q) => $q->whereNull('tgl_mulai')->orWhereDate('tgl_mulai', '<=', today()))
                            ->where(fn ($q) => $q->whereNull('tgl_selesai')->orWhereDate('tgl_selesai', '>=', today()));

                        return $query->orderBy('nama_diskon')->pluck('nama_diskon', 'id');
                    })
                    ->searchable()
                    ->preload()
                    ->live()
                    ->nullable()
                    ->helperText('Berlaku jika subtotal mencapai minimal belanja diskon.'),

                Select::make('metode_pembayaran')
                    ->label('Metode Pembayaran')
                    ->options([
                        'tunai' => 'Tunai',
                        'qris'  => 'QRIS',
                        'debit' => 'Debit',
                    ])
                    ->required()
                    ->default('tunai')
                    ->live(),

                DateTimePicker::make('tgl_transaksi')
                    ->label('Tanggal Transaksi')
                    ->required()
                    ->default(now())
                    ->live(),

                TextInput::make('nominal_uang')
                    ->label('Uang Diterima')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0)
                    ->live(debounce: 400)
                    ->visible(fn ($get) => $get('metode_pembayaran') === 'tunai'),
            ])->statePath('data');

    }

    // ===================== KERANJANG =====================

    /** Daftar barang untuk dipilih di sisi kiri, mengikuti pencarian & tenant. */
    public function getBarangsProperty()
    {
        $tenantId = $this->data['tenant_id'] ?? auth()->user()->tenant_id;

        return Barang::query()
            ->with('gambarUtama')
            ->where('is_active', true)
            ->when(auth()->user()->isSuperAdmin() && $tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('nama_barang', 'like', "%{$this->search}%")
                    ->orWhere('kode_barang', 'like', "%{$this->search}%");
            }))
            ->orderBy('nama_barang')
            ->limit(60)
            ->get();
    }

    public function addToCart(int $barangId): void
    {
        $barang = Barang::with('gambarUtama')->find($barangId);

        if (! $barang) {
            return;
        }

        if (isset($this->cart[$barangId])) {
            if ($this->cart[$barangId]['jumlah'] < $barang->stok) {
                $this->cart[$barangId]['jumlah']++;
            } else {
                Notification::make()->warning()->title('Stok tidak mencukupi')->send();
            }

            return;
        }

        if ($barang->stok < 1) {
            Notification::make()->warning()->title('Stok habis')->send();

            return;
        }

        $this->cart[$barangId] = [
            'barang_id' => $barang->id,
            'kode'      => $barang->kode_barang,
            'nama'      => $barang->nama_barang,
            'gambar'    => $barang->gambarUtama?->path ? Storage::url($barang->gambarUtama->path) : null,
            'harga'     => (float) $barang->harga_jual,
            'harga_beli' => (float) ($barang->harga_beli ?? 0),
            'jumlah'    => 1,
            'stok'      => (int) $barang->stok,
        ];
    }

    public function incrementQty(int $barangId): void
    {
        if (! isset($this->cart[$barangId])) {
            return;
        }

        if ($this->cart[$barangId]['jumlah'] < $this->cart[$barangId]['stok']) {
            $this->cart[$barangId]['jumlah']++;
        } else {
            Notification::make()->warning()->title('Stok tidak mencukupi')->send();
        }
    }

    public function decrementQty(int $barangId): void
    {
        if (! isset($this->cart[$barangId])) {
            return;
        }

        $this->cart[$barangId]['jumlah'] = max(1, $this->cart[$barangId]['jumlah'] - 1);
    }

    public function updateQty(int $barangId, $value): void
    {
        if (! isset($this->cart[$barangId])) {
            return;
        }

        $qty = max(1, (int) $value);
        $qty = min($qty, $this->cart[$barangId]['stok']);
        $this->cart[$barangId]['jumlah'] = $qty;
    }

    public function removeFromCart(int $barangId): void
    {
        unset($this->cart[$barangId]);
    }

    // ===================== KALKULASI NILAI TRANSAKSI =====================

    public function getSubtotalProperty(): float
    {
        return round(collect($this->cart)->sum(fn ($i) => $i['harga'] * $i['jumlah']), 2);
    }

    public function getTotalDiskonProperty(): float
    {
        $diskonId = $this->data['diskon_id'] ?? null;

        if (! $diskonId) {
            return 0;
        }

        $diskon = Diskon::find($diskonId);

        return $diskon?->hitung($this->subtotal, $this->data['tgl_transaksi'] ?? now()) ?? 0;
    }

    public function getTotalBayarProperty(): float
    {
        return max($this->subtotal - $this->totalDiskon, 0);
    }

    public function getKembalianProperty(): float
    {
        if (($this->data['metode_pembayaran'] ?? 'tunai') !== 'tunai') {
            return 0;
        }

        return max((float) ($this->data['nominal_uang'] ?? 0) - $this->totalBayar, 0);
    }

    // ===================== SIMPAN =====================

   public bool $showNotaModal = false;
public ?int $lastTransaksiId = null;

public function simpan(): void
{
    if (empty($this->cart)) {
        Notification::make()->danger()->title('Keranjang masih kosong')->send();
        return;
    }

    $state = $this->form->getState();

    if (($state['metode_pembayaran'] ?? 'tunai') === 'tunai' && (float) ($state['nominal_uang'] ?? 0) < $this->totalBayar) {
        Notification::make()->danger()->title('Uang diterima kurang dari total bayar')->send();
        return;
    }

    $transaksi = DB::transaction(function () use ($state) {
        $transaksi = Transaksi::create([ 
            'tenant_id'         => $state['tenant_id'] ?? auth()->user()->tenant_id,
            'user_id'           => $state['user_id'],
            'pelanggan_id'      => $state['pelanggan_id'],
            'diskon_id'         => $state['diskon_id'],
            'metode_pembayaran' => $state['metode_pembayaran'],
            'tgl_transaksi'     => $state['tgl_transaksi'],
            'subtotal'          => $this->subtotal,
            'total_diskon'      => $this->totalDiskon,
            'total_bayar'       => $this->totalBayar,
            'nominal_uang'      => $state['metode_pembayaran'] === 'tunai' ? $state['nominal_uang'] : $this->totalBayar,
            'kembalian'         => $this->kembalian,
            'catatan'           => $state['catatan'] ?? null,
            'status'            => 'selesai',
        ]);

     foreach ($this->cart as $item) {
            DetailTransaksi::create([
                'transaksi_id' => $transaksi->id,
                'barang_id'    => $item['barang_id'],
                'jumlah'       => $item['jumlah'],
                'harga_beli'   => $item['harga_beli'] ?? 0,
                'harga_satuan' => $item['harga'],
            ]);

            Barang::whereKey($item['barang_id'])->decrement('stok', $item['jumlah']);
        }

        return $transaksi;
    });

    Notification::make()->success()->title('Transaksi berhasil disimpan')->send();

    // Kosongkan keranjang, tapi jangan reset form dulu -- tampilkan modal nota dulu
    $this->cart = [];
    $this->lastTransaksiId = $transaksi->id;
    $this->showNotaModal = true;
}

public function closeNotaModal(): void
{
    $this->showNotaModal = false;
    $this->lastTransaksiId = null;
    $this->mount();
}
}
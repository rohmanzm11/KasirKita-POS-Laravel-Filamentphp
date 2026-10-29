<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransaksiResource\Pages;
use App\Models\Barang;
use App\Models\Diskon;
use App\Models\Transaksi;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

class TransaksiResource extends Resource
{
    protected static ?string $model = Transaksi::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?string $navigationLabel = 'Transaksi';
    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();

        return $user?->isSuperAdmin() ?? false;
    }
    
      public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // ==================== INFORMASI TRANSAKSI ====================
                Forms\Components\Section::make('Informasi Transaksi')
                    ->schema([
                        Forms\Components\Select::make('tenant_id')
                            ->label('Tenant')
                            ->relationship('tenant', 'nama')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $set('user_id', null);
                                $set('pelanggan_id', null);
                                $set('diskon_id', null);
                                $set('details', []);
                                self::hitungTotal($get, $set);
                            })
                            ->visible(fn () => auth()->user()?->isSuperAdmin()),

                        Forms\Components\TextInput::make('kode_transaksi')
                            ->label('Kode Transaksi')
                            ->placeholder('Otomatis saat disimpan')
                            ->helperText('Format: 0001/tanggal/bulan/tahun')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Select::make('user_id')
                            ->label('Kasir')
                            ->relationship('user', 'name', function (Builder $query, Get $get) {
                                $user = auth()->user();

                                if ($user->isSuperAdmin()) {
                                    return $get('tenant_id')
                                        ? $query->where('tenant_id', $get('tenant_id'))
                                        : $query;
                                }

                                return $query->where('tenant_id', $user->tenant_id);
                            })
                            ->default(fn () => auth()->id())
                            ->disabled(fn () => auth()->user()->isKasir())
                            ->dehydrated()
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('pelanggan_id')
                            ->label('Pelanggan')
                            ->relationship('pelanggan', 'nama', fn (Builder $query, Get $get) =>
                                $get('tenant_id')
                                    ? $query->where('tenant_id', $get('tenant_id'))
                                    : $query)
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        Forms\Components\Select::make('diskon_id')
                            ->label('Diskon')
                            ->relationship('diskon', 'nama_diskon', function (Builder $query, Get $get, string $operation) {
                                if ($get('tenant_id')) {
                                    $query->where('tenant_id', $get('tenant_id'));
                                }

                                // transaksi baru: hanya diskon aktif & masih berlaku
                                if ($operation === 'create') {
                                    $query->where('is_active', true)
                                        ->where(fn ($q) => $q->whereNull('tgl_mulai')->orWhereDate('tgl_mulai', '<=', today()))
                                        ->where(fn ($q) => $q->whereNull('tgl_selesai')->orWhereDate('tgl_selesai', '>=', today()));
                                }

                                return $query;
                            })
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->live()
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::hitungTotal($get, $set))
                            ->helperText('Berlaku jika subtotal mencapai minimal belanja diskon.'),

                        Forms\Components\Select::make('metode_pembayaran')
                            ->label('Metode Pembayaran')
                            ->options([
                                'tunai' => 'Tunai',
                                'qris'  => 'QRIS',
                                'debit' => 'Debit',
                            ])
                            ->required()
                            ->default('tunai')
                            ->live()
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::hitungTotal($get, $set)),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'selesai' => 'Selesai',
                                'batal'   => 'Batal',
                            ])
                            ->required()
                            ->default('selesai'),

                        Forms\Components\DateTimePicker::make('tgl_transaksi')
                            ->label('Tanggal Transaksi')
                            ->required()
                            ->default(now())
                            ->live()
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::hitungTotal($get, $set)),
                    ])->columns(2),

                // ==================== ITEM BARANG ====================
                Forms\Components\Section::make('Item Barang')
                    ->schema([
                        Forms\Components\Repeater::make('details')
                            ->relationship('details')
                            ->hiddenLabel()
                            ->schema([
                                Forms\Components\Select::make('barang_id')
                                    ->label('Barang')
                                    ->options(function (Get $get, string $operation) {
                                        $query = Barang::query();

                                        // barang non-aktif hanya disembunyikan saat transaksi baru
                                        if ($operation === 'create') {
                                            $query->where('is_active', true);
                                        }

                                        // super admin: ikuti tenant yang dipilih di form.
                                        // owner/kasir sudah dibatasi global scope.
                                        if (auth()->user()->isSuperAdmin()) {
                                            $query->where('tenant_id', $get('../../tenant_id'));
                                        }

                                        return $query->orderBy('nama_barang')->get()
                                            ->mapWithKeys(fn ($b) => [
                                                $b->id => "{$b->kode_barang} - {$b->nama_barang}",
                                            ]);
                                    })
                                    ->searchable()
                                    ->required()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                        $harga = (float) (Barang::find($state)?->harga_jual ?? 0);
                                        $jumlah = (float) ($get('jumlah') ?: 1);

                                        $set('jumlah', $jumlah);
                                        $set('harga_satuan', $harga);
                                        $set('subtotal_item', $jumlah * $harga);

                                        self::hitungTotal($get, $set, '../../');
                                    })
                                    ->columnSpan(2),

                                Forms\Components\Placeholder::make('info_barang')
                                    ->label('Info')
                                    ->content(function (Get $get) {
                                        $barang = $get('barang_id')
                                            ? Barang::with('gambarUtama')->find($get('barang_id'))
                                            : null;

                                        if (! $barang) {
                                            return '-';
                                        }

                                        $img = $barang->gambarUtama?->path
                                            ? '<img src="' . e(Storage::url($barang->gambarUtama->path)) . '" style="height:40px;border-radius:6px;display:inline-block;margin-right:8px;vertical-align:middle">'
                                            : '';

                                        return new HtmlString($img . 'Stok: ' . (int) $barang->stok . ' ' . e($barang->satuan));
                                    })
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('jumlah')
                                    ->label('Jumlah')
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(1)
                                    ->required()
                                    ->live(debounce: 500)
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        $set('subtotal_item', (float) $get('jumlah') * (float) $get('harga_satuan'));
                                        self::hitungTotal($get, $set, '../../');
                                    }),

                                Forms\Components\TextInput::make('harga_satuan')
                                    ->label('Harga Satuan')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->required()
                                    ->live(debounce: 500)
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        $set('subtotal_item', (float) $get('jumlah') * (float) $get('harga_satuan'));
                                        self::hitungTotal($get, $set, '../../');
                                    }),

                                Forms\Components\TextInput::make('subtotal_item')
                                    ->label('Subtotal')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->default(0)
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpan(2),
                            ])
                            ->columns(4)
                            ->minItems(1)
                            ->defaultItems(1)
                            ->reorderable(false)
                            ->addActionLabel('Tambah Barang')
                            ->live()
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::hitungTotal($get, $set))
                            ->deleteAction(fn (Forms\Components\Actions\Action $action) => $action
                                ->after(fn (Get $get, Set $set) => self::hitungTotal($get, $set)))
                            ->columnSpanFull(),
                    ]),

                // ==================== NILAI TRANSAKSI (OTOMATIS) ====================
                Forms\Components\Section::make('Nilai Transaksi')
                    ->schema([
                        Forms\Components\TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->numeric()->prefix('Rp')->default(0)
                            ->disabled()->dehydrated(),

                        Forms\Components\TextInput::make('total_diskon')
                            ->label('Total Diskon')
                            ->numeric()->prefix('Rp')->default(0)
                            ->disabled()->dehydrated(),

                        Forms\Components\TextInput::make('total_bayar')
                            ->label('Total Bayar')
                            ->numeric()->prefix('Rp')->default(0)
                            ->disabled()->dehydrated(),

                        Forms\Components\TextInput::make('nominal_uang')
                            ->label('Uang Diterima')
                            ->numeric()->prefix('Rp')->default(0)
                            ->required()
                            ->readOnly(fn (Get $get) => $get('metode_pembayaran') !== 'tunai')
                            ->live(debounce: 500)
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::hitungTotal($get, $set))
                            ->rules([
                                fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                                    if ($get('status') === 'selesai' && (float) $value < (float) $get('total_bayar')) {
                                        $fail('Uang diterima kurang dari total bayar.');
                                    }
                                },
                            ]),

                        Forms\Components\TextInput::make('kembalian')
                            ->label('Kembalian')
                            ->numeric()->prefix('Rp')->default(0)
                            ->disabled()->dehydrated(),

                        Forms\Components\Textarea::make('catatan')
                            ->label('Catatan')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])->columns(3),
            ]);
    }

    /**
     * Hitung subtotal, diskon, total bayar & kembalian di form.
     * $prefix = '../../' bila dipanggil dari dalam item repeater.
     */
    public static function hitungTotal(Get $get, Set $set, string $prefix = ''): void
    {
        $subtotal = round(collect($get($prefix . 'details') ?? [])
            ->sum(fn ($item) => (float) ($item['jumlah'] ?? 0) * (float) ($item['harga_satuan'] ?? 0)), 2);

        $diskonId = $get($prefix . 'diskon_id');
        $diskon = $diskonId ? Diskon::find($diskonId) : null;
        $totalDiskon = $diskon?->hitung($subtotal, $get($prefix . 'tgl_transaksi')) ?? 0;

        $totalBayar = max($subtotal - $totalDiskon, 0);

        $tunai = $get($prefix . 'metode_pembayaran') === 'tunai';
        $nominal = $tunai ? (float) $get($prefix . 'nominal_uang') : $totalBayar;

        $set($prefix . 'subtotal', $subtotal);
        $set($prefix . 'total_diskon', $totalDiskon);
        $set($prefix . 'total_bayar', $totalBayar);
        $set($prefix . 'nominal_uang', $nominal);
        $set($prefix . 'kembalian', $tunai ? max($nominal - $totalBayar, 0) : 0);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Kolom tenant, hanya untuk super admin
                Tables\Columns\TextColumn::make('tenant.nama')
                    ->label('Toko')
                    ->sortable()
                    ->searchable()
                    ->visible(fn () => auth()->user()?->isSuperAdmin()),

                Tables\Columns\TextColumn::make('kode_transaksi')
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary')
                    ->copyable(),

                Tables\Columns\TextColumn::make('tgl_transaksi')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Kasir')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('pelanggan.nama')
                    ->label('Pelanggan')
                    ->placeholder('Umum')
                    ->searchable(),

                Tables\Columns\TextColumn::make('total_bayar')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('metode_pembayaran')
                    ->label('Pembayaran')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'tunai' => 'Tunai',
                        'qris'  => 'QRIS',
                        'debit' => 'Debit',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'tunai' => 'success',
                        'qris'  => 'info',
                        'debit' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'selesai' => 'Selesai',
                        'batal'   => 'Batal',
                        default   => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'selesai' => 'success',
                        'batal'   => 'danger',
                        default   => 'gray',
                    }),
            ])
            ->defaultSort('tgl_transaksi', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'selesai' => 'Selesai',
                        'batal'   => 'Batal',
                    ]),

                Tables\Filters\SelectFilter::make('metode_pembayaran')
                    ->label('Metode Pembayaran')
                    ->options([
                        'tunai' => 'Tunai',
                        'qris'  => 'QRIS',
                        'debit' => 'Debit',
                    ]),

                Tables\Filters\Filter::make('tgl_transaksi')
                    ->form([
                        Forms\Components\DatePicker::make('dari'),
                        Forms\Components\DatePicker::make('sampai'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['dari'], fn ($q, $date) => $q->whereDate('tgl_transaksi', '>=', $date))
                            ->when($data['sampai'], fn ($q, $date) => $q->whereDate('tgl_transaksi', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    

    // Kasir hanya melihat transaksi miliknya sendiri.
    // Pembatasan per tenant sudah ditangani global scope di model Transaksi.
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        return $user && $user->isKasir()
            ? $query->where('user_id', $user->id)
            : $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransaksis::route('/'),
            'create' => Pages\CreateTransaksi::route('/create'),
            'edit' => Pages\EditTransaksi::route('/{record}/edit'),
        ];
    }
}
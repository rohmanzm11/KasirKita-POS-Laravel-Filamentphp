<?php

namespace App\Filament\Resources;

use App\Exports\BarangImportTemplateExport;
use App\Filament\Resources\BarangResource\Pages;
use App\Imports\BarangImport;
use App\Models\Barang;
use App\Models\Kategori;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Maatwebsite\Excel\Facades\Excel;

class BarangResource extends Resource
{
    protected static ?string $model = Barang::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Barang';
    protected static ?int $navigationSort = 4;

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
                // ==================== INFORMASI UTAMA ====================
                Forms\Components\Section::make('Informasi Barang')
                    ->schema([
                        Forms\Components\Hidden::make('tenant_id')
                            ->default(fn () => auth()->user()->tenant_id),

                        Forms\Components\Select::make('kategori_id')
                            ->label('Kategori')
                            ->options(fn () => Kategori::where('tenant_id', auth()->user()->tenant_id)
                                ->pluck('nama_kategori', 'id'))
                            ->searchable()
                            ->required(),

                       Forms\Components\TextInput::make('kode_barang')
                        ->label('Kode / SKU')
                        ->required()
                        ->maxLength(50)
                        ->unique(
                            ignoreRecord: true,
                            modifyRuleUsing: fn ($rule) => $rule->where('tenant_id', auth()->user()->tenant_id)
                        ),

                        Forms\Components\TextInput::make('nama_barang')
                            ->label('Nama Barang')
                            ->required()
                            ->maxLength(150),

                        Select::make('satuan')
                            ->label('Satuan')
                            ->options([
                                'pcs' => 'Pcs',
                                'dos' => 'Dos/Box',
                                'lusin' => 'Lusin',
                            ])
                            ->default('pcs')
                            ->required()
                            ->searchable(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true)
                            ->inline(false),
                    ])->columns(2),

                // ==================== HARGA & STOK ====================
                Forms\Components\Section::make('Harga & Stok')
                    ->schema([
                        Forms\Components\TextInput::make('harga_jual')
                            ->label('Harga Jual')
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),

                        Forms\Components\TextInput::make('stok')
                            ->label('Stok Saat Ini')
                            ->numeric()
                            ->default(0)
                            ->required(),

                        Forms\Components\TextInput::make('stok_minimal')
                            ->label('Stok Minimal')
                            ->numeric()
                            ->default(0)
                            ->required()
                            ->helperText('Peringatan jika stok di bawah nilai ini'),
                    ])->columns(3),

                // ==================== GAMBAR ====================
                Forms\Components\Section::make('Gambar Barang')
                    ->description('Upload satu atau lebih gambar. Tandai salah satu sebagai gambar utama.')
                    ->schema([
                        Forms\Components\Repeater::make('gambar')
                            ->relationship()
                            ->schema([
                                Forms\Components\FileUpload::make('path')
                                    ->label('Gambar')
                                    ->image()
                                    ->directory('barang')
                                    ->visibility('public')
                                    ->imagePreviewHeight('120')
                                    ->required()
                                    ->columnSpan(2),

                                Forms\Components\Group::make([
                                    Forms\Components\Toggle::make('is_utama')
                                        ->label('Gambar Utama')
                                        ->default(false)
                                        ->inline(false),

                                    Forms\Components\TextInput::make('urutan')
                                        ->label('Urutan')
                                        ->numeric()
                                        ->default(0)
                                        ->minValue(0),
                                ])->columnSpan(1),
                            ])
                            ->columns(3)
                            ->defaultItems(1)
                            ->collapsible()
                            ->cloneable()
                            ->itemLabel(fn (array $state): ?string => 
                                isset($state['is_utama']) && $state['is_utama'] 
                                    ? 'Gambar Utama' 
                                    : 'Gambar'
                            )
                            ->addActionLabel('Tambah Gambar'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('gambarUtama.path')
                    ->label('Gambar')
                    ->circular()
                    ->defaultImageUrl(url('/images/placeholder.png'))
                    ->height(40),

                Tables\Columns\TextColumn::make('kode_barang')
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('nama_barang')
                    ->label('Nama Barang')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(40),

                Tables\Columns\TextColumn::make('kategori.nama_kategori')
                    ->label('Kategori')
                    ->sortable()
                    ->badge(),

                Tables\Columns\TextColumn::make('harga_jual')
                    ->label('Harga')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('stok')
                    ->label('Stok')
                    ->sortable()
                    ->color(fn ($record) => $record->stok <= $record->stok_minimal ? 'danger' : 'success')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('satuan')
                    ->label('Satuan'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('kategori_id')
                    ->label('Kategori')
                    ->relationship('kategori', 'nama_kategori'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
           ->headerActions([
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
                    ->label('Import Excel')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('success')
                    ->modalHeading('Import Data Barang')
                    ->modalSubmitActionLabel('Import')
                    ->modalWidth(MaxWidth::FourExtraLarge)
                    ->form([
                        Forms\Components\Grid::make([
                            'default' => 1,
                            'md' => 5,
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
                                            <span>Belum punya filenya? Klik <strong>Download Template</strong> di atas tabel untuk mengunduh format Excel yang sudah siap pakai (lengkap dengan dropdown kategori &amp; satuan).</span>
                                        </div>'
                                    )),

                                FileUpload::make('file')
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
                                    ->helperText('Format: .xlsx, .xls, atau .csv (maks. ' . BarangImport::MAX_ROWS . ' baris)')
                                    ->extraAttributes(['class' => 'rounded-xl']),
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
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBarangs::route('/'),
            'create' => Pages\CreateBarang::route('/create'),
            'edit' => Pages\EditBarang::route('/{record}/edit'),
        ];
    }
}
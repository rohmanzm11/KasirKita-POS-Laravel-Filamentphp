<?php

namespace App\Filament\Pages;

use App\Models\Tenant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;

class SettingToko extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Setting Toko';
    protected static ?string $title = 'Setting Toko';
    // protected static ?string $navigationGroup = 'Tenant';
    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.setting-toko';

    public static function canViewAny(): bool
{
    /** @var \App\Models\User|null $user */
    $user = auth()->user();

    return $user?->isSuperAdmin() || $user?->isOwner();
}

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
{
    return '';
}

public function getSubheading(): string|\Illuminate\Contracts\Support\Htmlable|null
{
    return null;
}

    public ?array $data = [];

    public function mount(): void
    {
        $user = Auth::user();

        // Ambil tenant milik user yang login (bukan super admin)
        $tenant = $user->isSuperAdmin()
            ? Tenant::query()->first() // fallback jika superadmin
            : Tenant::find($user->tenant_id);

        if (! $tenant) {
            Notification::make()
                ->title('Tenant tidak ditemukan')
                ->danger()
                ->send();

            return;
        }

        $this->form->fill($tenant->toArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // ==================== INFORMASI UTAMA ====================
                Forms\Components\Section::make('Informasi Toko')
    ->description('Data utama toko / tenant')
    ->schema([
        Forms\Components\Grid::make(3)
            ->schema([
                // Kolom kiri (2/3) — field-field utama
                Forms\Components\Group::make([
                    Forms\Components\TextInput::make('nama')
                        ->label('Nama Toko')
                        ->required()
                        ->maxLength(150)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Forms\Set $set, ?string $state) {
                            if (filled($state)) {
                                $set('kode', \Illuminate\Support\Str::slug($state));
                            }
                        })
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('kode')
                        ->label('Kode Toko')
                        ->disabled()
                        ->dehydrated()
                        ->required()
                        ->maxLength(50)
                        ->helperText('Otomatis dibuat dari Nama Toko')
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('no_hp')
                        ->label('No. HP')
                        ->tel()
                        ->maxLength(20)
                        ->prefixIcon('heroicon-o-phone')
                        ->columnSpan(1),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Status Aktif')
                        ->helperText('Nonaktifkan jika toko sedang tutup')
                        ->default(true)
                        ->inline(false)
                        ->columnSpan(1),
                ])->columns(2)->columnSpan(2),

                // Kolom kanan (1/3) - Logo
                Forms\Components\FileUpload::make('logo')
                    ->label('Logo Toko')
                    ->image()
                    ->imageEditor()
                    ->directory('tenants/logos')
                    ->visibility('public')
                    ->maxSize(2048)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->helperText('JPG / PNG / WebP • Maks 2 MB')
                    ->imagePreviewHeight('150')
                    ->avatar()
                    ->columnSpan(1),
            ]),
    ]),
                    // ==================== PENGATURAN STRUK ====================
Forms\Components\Section::make('Pengaturan Struk')
    ->description('Digunakan saat mencetak nota transaksi')
    ->schema([
        Forms\Components\Select::make('ukuran_kertas_struk')
            ->label('Ukuran Kertas Struk')
            ->options([
                '58mm' => '58 mm (Thermal kecil)',
                '80mm' => '80 mm (Thermal standar)',
            ])
            ->default('80mm')
            ->required()
            ->native(false),

        Forms\Components\Textarea::make('catatan_struk')
            ->label('Catatan Kaki Struk')
            ->rows(2)
            ->placeholder('Contoh: Barang yang sudah dibeli tidak dapat dikembalikan')
            ->helperText('Opsional, ditampilkan di bagian bawah struk')
            ->columnSpanFull(),
    ]),

                // ==================== ALAMAT ====================
                Forms\Components\Section::make('Alamat')
                    ->schema([
                        Forms\Components\Textarea::make('alamat')
                            ->label('Alamat Lengkap')
                            ->rows(3)
                            ->placeholder('Masukkan alamat lengkap toko...')
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan Perubahan')
                ->color('primary')
                ->icon('heroicon-o-check')
                ->action('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $user = Auth::user();

        $tenant = $user->isSuperAdmin()
            ? Tenant::query()->first()
            : Tenant::find($user->tenant_id);

        if (! $tenant) {
            Notification::make()
                ->title('Gagal menyimpan')
                ->body('Tenant tidak ditemukan.')
                ->danger()
                ->send();

            return;
        }

        $tenant->update($data);

        Notification::make()
            ->title('Berhasil disimpan')
            ->body('Data toko berhasil diperbarui.')
            ->success()
            ->send();
    }

    public static function canAccess(): bool
    {
        // Hanya user yang punya tenant yang boleh akses (bukan superadmin saja)
        $user = Auth::user();

        return $user && ($user->isSuperAdmin() || filled($user->tenant_id));
    }
}
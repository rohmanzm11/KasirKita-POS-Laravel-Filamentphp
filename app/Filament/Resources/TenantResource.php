<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TenantResource\Pages;
use App\Models\Tenant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Tenant / Toko';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // ==================== INFORMASI UTAMA ====================
                Forms\Components\Section::make('Informasi Toko')
                    ->description('Data utama toko / tenant')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                // Kolom kiri (2/3)
                              Forms\Components\Group::make([
                                Forms\Components\TextInput::make('kode')
                                    ->label('Kode Toko')
                                    ->disabled()
                                    ->dehydrated()
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(50)
                                    ->helperText('Otomatis dibuat dari Nama Toko')
                                    ->columnSpan(1),

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

                                Forms\Components\TextInput::make('no_hp')
                                    ->label('No. HP')
                                    ->tel()
                                    ->maxLength(20)
                                    ->columnSpan(1),

                                Forms\Components\Toggle::make('is_active')
                                    ->label('Status Aktif')
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo')
                    ->label('Logo')
                    ->circular()
                    ->defaultImageUrl(url('/images/default-logo.png'))
                    ->height(40),

                Tables\Columns\TextColumn::make('kode')
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Toko')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('no_hp')
                    ->label('No. HP')
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
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

    public static function getEloquentQuery(): Builder
{
    $query = parent::getEloquentQuery();
    $user = auth()->user();

    return $user->isSuperAdmin()
        ? $query
        : $query->whereKey($user->tenant_id);
}
public static function canViewAny(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();

        return $user?->isSuperAdmin() ?? false;
    }
public static function canCreate(): bool
{
    return auth()->user()->isSuperAdmin();
}

public static function canDelete($record): bool
{
    return auth()->user()->isSuperAdmin();
}

public static function canDeleteAny(): bool
{
    return auth()->user()->isSuperAdmin();
}
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTenants::route('/'),
            'create' => Pages\CreateTenant::route('/create'),
            'edit' => Pages\EditTenant::route('/{record}/edit'),
        ];
    }
}
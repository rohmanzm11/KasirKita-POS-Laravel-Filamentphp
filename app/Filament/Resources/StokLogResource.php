<?php

namespace App\Filament\Resources;

use App\Enums\JenisMutasi;
use App\Filament\Resources\StokLogResource\Pages;
use App\Models\StokLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StokLogResource extends Resource
{
    protected static ?string $model = StokLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?string $navigationLabel = 'Riwayat Stok';
    protected static ?int $navigationSort = 1;

    // Biasanya hanya view, tidak create/edit manual
    public static function canCreate(): bool
    {
        return false;
    }

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
                // Hanya untuk view
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('barang.nama_barang')
                    ->label('Barang')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('jenis')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (JenisMutasi $state): string => match ($state) {
                        JenisMutasi::StokMasuk => 'success',
                        JenisMutasi::Penjualan => 'info',
                        JenisMutasi::Pembatalan => 'warning',
                        JenisMutasi::Penyesuaian => 'gray',
                    }),

                Tables\Columns\TextColumn::make('jumlah')
                    ->label('Jumlah')
                    ->color(fn ($state) => $state > 0 ? 'success' : 'danger')
                    ->weight('bold')
                    ->formatStateUsing(fn ($state) => $state > 0 ? "+$state" : $state),

                Tables\Columns\TextColumn::make('stok_sebelum')
                    ->label('Sebelum'),

                Tables\Columns\TextColumn::make('stok_sesudah')
                    ->label('Sesudah'),

                Tables\Columns\TextColumn::make('user.nama')
                    ->label('Oleh')
                    ->searchable(),

                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(30)
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('jenis')
                    ->options(JenisMutasi::class),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStokLogs::route('/'),
        ];
    }
}
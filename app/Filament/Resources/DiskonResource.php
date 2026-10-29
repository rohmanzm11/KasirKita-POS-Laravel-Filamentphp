<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DiskonResource\Pages;
use App\Models\Diskon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DiskonResource extends Resource
{
    protected static ?string $model = Diskon::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Diskon';
    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Diskon')
                    ->schema([
                        Forms\Components\Select::make('tenant_id')
                            ->visible(fn () => auth()->user()?->isSuperAdmin()),

                        Forms\Components\TextInput::make('nama_diskon')
                            ->label('Nama Diskon')
                            ->required()
                            ->maxLength(100),

                        Forms\Components\TextInput::make('persen')
                            ->label('Persen (%)')
                            ->numeric()
                            ->suffix('%')
                            ->helperText('Isi jika diskon berupa persen'),

                        Forms\Components\TextInput::make('potongan')
                            ->label('Potongan (Rp)')
                            ->numeric()
                            ->prefix('Rp')
                            ->helperText('Isi jika diskon berupa nominal'),

                        Forms\Components\TextInput::make('min_belanja')
                            ->label('Minimal Belanja')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0)
                            ->required(),

                        Forms\Components\DatePicker::make('tgl_mulai')
                            ->label('Tanggal Mulai'),

                        Forms\Components\DatePicker::make('tgl_selesai')
                            ->label('Tanggal Selesai'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->inline(false),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_diskon')
                    ->label('Nama Diskon')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('persen')
                    ->label('Persen')
                    ->suffix('%')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('potongan')
                    ->label('Potongan')
                    ->money('IDR')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('min_belanja')
                    ->label('Min. Belanja')
                    ->money('IDR'),

                Tables\Columns\TextColumn::make('tgl_mulai')
                    ->label('Mulai')
                    ->date('d M Y'),

                Tables\Columns\TextColumn::make('tgl_selesai')
                    ->label('Selesai')
                    ->date('d M Y'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean(),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDiskons::route('/'),
            'create' => Pages\CreateDiskon::route('/create'),
            'edit' => Pages\EditDiskon::route('/{record}/edit'),
        ];
    }
}
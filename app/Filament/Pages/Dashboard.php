<?php

namespace App\Filament\Pages;

use App\Models\Barang;
use App\Models\User;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?int $navigationSort     = -2;

    protected static string $view = 'filament.pages.dashboard';

    /** Cache hasil query supaya tidak dihitung berulang kali dalam 1 request */
    protected ?array $inventoryStatsCache = null;

    protected function currentUser(): ?User
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return $user;
    }

      public function getWidgets(): array
    {
        return [
            WelcomeWidget::class,
            InventoryStatsWidget::class,
            PemasukanHariIniWidget::class,
        ];
    }
}
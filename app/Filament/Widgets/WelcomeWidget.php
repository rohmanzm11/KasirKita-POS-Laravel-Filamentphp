<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class WelcomeWidget extends Widget
{
    protected static string $view = 'filament.widgets.welcome-widget';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = -3; // tampil paling atas

    public function getUser()
    {
        return Auth::user();
    }

    public function getTenant()
    {
        return Auth::user()?->tenant;
    }

    public function getSapaan(): string
    {
        $jam = now()->hour;

        return match (true) {
            $jam < 10 => 'Selamat pagi',
            $jam < 15 => 'Selamat siang',
            $jam < 18 => 'Selamat sore',
            default   => 'Selamat malam',
        };
    }
}
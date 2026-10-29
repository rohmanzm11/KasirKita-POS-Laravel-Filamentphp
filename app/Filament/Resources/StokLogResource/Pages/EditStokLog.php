<?php

namespace App\Filament\Resources\StokLogResource\Pages;

use App\Filament\Resources\StokLogResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStokLog extends EditRecord
{
    protected static string $resource = StokLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

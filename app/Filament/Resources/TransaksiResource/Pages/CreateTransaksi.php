<?php

namespace App\Filament\Resources\TransaksiResource\Pages;

use App\Filament\Resources\TransaksiResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTransaksi extends CreateRecord
{
    protected static string $resource = TransaksiResource::class;

   protected function mutateFormDataBeforeCreate(array $data): array
{
    if (auth()->user()->isKasir()) {
        $data['user_id'] = auth()->id();
    }

    return $data;
}

protected function afterCreate(): void
{
    $this->record->hitungUlang();
}
}


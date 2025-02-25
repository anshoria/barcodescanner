<?php

namespace App\Filament\Resources\ResiResource\Pages;

use App\Filament\Resources\ResiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditResi extends EditRecord
{
    protected static string $resource = ResiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

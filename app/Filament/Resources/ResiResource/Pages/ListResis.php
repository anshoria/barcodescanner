<?php

namespace App\Filament\Resources\ResiResource\Pages;

use App\Filament\Resources\ResiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListResis extends ListRecords
{
    protected static string $resource = ResiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

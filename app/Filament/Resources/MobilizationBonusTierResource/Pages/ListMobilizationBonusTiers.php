<?php

namespace App\Filament\Resources\MobilizationBonusTierResource\Pages;

use App\Filament\Resources\MobilizationBonusTierResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMobilizationBonusTiers extends ListRecords
{
    protected static string $resource = MobilizationBonusTierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

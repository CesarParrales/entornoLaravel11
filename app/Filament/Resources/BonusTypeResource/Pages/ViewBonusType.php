<?php

namespace App\Filament\Resources\BonusTypeResource\Pages;

use App\Filament\Resources\BonusTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord; // Corrected base class

class ViewBonusType extends ViewRecord
{
    protected static string $resource = BonusTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}

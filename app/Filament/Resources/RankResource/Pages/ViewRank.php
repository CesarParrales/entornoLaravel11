<?php

namespace App\Filament\Resources\RankResource\Pages;

use App\Filament\Resources\RankResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord; // Corrected base class

class ViewRank extends ViewRecord
{
    protected static string $resource = RankResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}

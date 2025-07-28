<?php

namespace App\Filament\Resources\RecognitionBonusTierResource\Pages;

use App\Filament\Resources\RecognitionBonusTierResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRecognitionBonusTiers extends ListRecords
{
    protected static string $resource = RecognitionBonusTierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

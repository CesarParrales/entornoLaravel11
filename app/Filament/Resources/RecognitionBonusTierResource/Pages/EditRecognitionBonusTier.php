<?php

namespace App\Filament\Resources\RecognitionBonusTierResource\Pages;

use App\Filament\Resources\RecognitionBonusTierResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRecognitionBonusTier extends EditRecord
{
    protected static string $resource = RecognitionBonusTierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

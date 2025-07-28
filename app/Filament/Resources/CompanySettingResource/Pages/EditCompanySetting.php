<?php

namespace App\Filament\Resources\CompanySettingResource\Pages;

use App\Filament\Resources\CompanySettingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord; // Corrected base class

class EditCompanySetting extends EditRecord
{
    protected static string $resource = CompanySettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(), // Usually not needed for a single settings record
        ];
    }

    // Optional: Redirect after saving if you want to go back to a specific page
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

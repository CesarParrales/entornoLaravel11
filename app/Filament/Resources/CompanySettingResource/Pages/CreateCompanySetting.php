<?php

namespace App\Filament\Resources\CompanySettingResource\Pages;

use App\Filament\Resources\CompanySettingResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord; // Corrected base class

class CreateCompanySetting extends CreateRecord
{
    protected static string $resource = CompanySettingResource::class;

    // Optional: Redirect after creation if you want to go back to a specific page
    // or to the edit page of the newly created (and only) record.
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
        // Or, if you want to go to edit:
        // return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }

    // Optional: If you want to ensure only one record can be created.
    // This can also be handled by the ManageCompanySettings page.
    // protected function beforeCreate(): void
    // {
    //     if (static::getResource()::getModel()::count() > 0) {
    //         \Filament\Notifications\Notification::make()
    //             ->title('Configuración Existente')
    //             ->body('Ya existe una configuración de empresa. Solo se permite un registro.')
    //             ->danger()
    //             ->send();
    //
    //         $this->halt(); // Stop the creation process
    //
    //         // Optionally redirect to the edit page of the existing record
    //         // $existingRecord = static::getResource()::getModel()::first();
    //         // if ($existingRecord) {
    //         //    $this->redirect(static::getResource()::getUrl('edit', ['record' => $existingRecord]));
    //         // }
    //     }
    // }
}

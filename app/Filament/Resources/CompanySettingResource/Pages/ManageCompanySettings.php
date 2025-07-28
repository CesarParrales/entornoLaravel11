<?php

namespace App\Filament\Resources\CompanySettingResource\Pages;

use App\Filament\Resources\CompanySettingResource;
use App\Models\CompanySetting;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Log;

class ManageCompanySettings extends Page
{
    protected static string $resource = CompanySettingResource::class;

    // Use a placeholder view to satisfy Filament if redirection fails or for type hinting
    protected static string $view = 'filament.resources.company-setting-resource.pages.redirect-placeholder';


    public function mount(): void
    {
        $setting = CompanySetting::first();

        if ($setting) {
            // Redirect to the edit page of the existing record
            $editUrl = static::getResource()::getUrl('edit', ['record' => $setting->id]);
            if ($editUrl) {
                redirect()->to($editUrl);
            } else {
                Log::error("Could not generate edit URL for CompanySetting.");
                // Handle error, maybe show a message or redirect to a safe page
            }
        } else {
            // Redirect to the create page if no record exists
            $createUrl = static::getResource()::getUrl('create');
            if ($createUrl) {
                redirect()->to($createUrl);
            } else {
                Log::error("Could not generate create URL for CompanySetting.");
                // Handle error
            }
        }
    }

    // If extending Page, getTitle might not be automatically set from resource
    public function getTitle(): string
    {
        return static::getResource()::getModelLabel();
    }
}
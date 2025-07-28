<?php

namespace App\Filament\Resources\FinancialFreedomCommissionTierResource\Pages;

use App\Filament\Resources\FinancialFreedomCommissionTierResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFinancialFreedomCommissionTiers extends ListRecords
{
    protected static string $resource = FinancialFreedomCommissionTierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

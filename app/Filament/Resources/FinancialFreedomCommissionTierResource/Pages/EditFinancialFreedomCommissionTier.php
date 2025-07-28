<?php

namespace App\Filament\Resources\FinancialFreedomCommissionTierResource\Pages;

use App\Filament\Resources\FinancialFreedomCommissionTierResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFinancialFreedomCommissionTier extends EditRecord
{
    protected static string $resource = FinancialFreedomCommissionTierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

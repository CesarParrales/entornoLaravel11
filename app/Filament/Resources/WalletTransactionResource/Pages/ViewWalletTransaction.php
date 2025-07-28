<?php

namespace App\Filament\Resources\WalletTransactionResource\Pages;

use App\Filament\Resources\WalletTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord; // Corrected base class

class ViewWalletTransaction extends ViewRecord
{
    protected static string $resource = WalletTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\EditAction::make(), // Transactions are typically not editable
        ];
    }
}

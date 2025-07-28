<?php

namespace App\Filament\Resources\WalletResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

    protected static ?string $recordTitleAttribute = 'transaction_uuid';
    protected static ?string $modelLabel = 'Transacción';
    protected static ?string $pluralModelLabel = 'Transacciones';

    public function form(Form $form): Form
    {
        // Transactions are typically not created/edited directly via admin UI
        // but through services. This form is for viewing mostly.
        return $form
            ->schema([
                Forms\Components\TextInput::make('transaction_uuid')
                    ->label('UUID de Transacción')
                    ->disabled(),
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->label('Socio')
                    ->disabled(),
                Forms\Components\TextInput::make('type')
                    ->label('Tipo')
                    ->disabled(),
                Forms\Components\TextInput::make('amount')
                    ->label('Monto')
                    ->numeric()
                    ->prefix('$') // Or use wallet currency
                    ->disabled(),
                Forms\Components\TextInput::make('balance_before_transaction')
                    ->label('Saldo Anterior')
                    ->numeric()
                    ->prefix('$')
                    ->disabled(),
                Forms\Components\TextInput::make('balance_after_transaction')
                    ->label('Saldo Posterior')
                    ->numeric()
                    ->prefix('$')
                    ->disabled(),
                Forms\Components\TextInput::make('currency_code')
                    ->label('Moneda')
                    ->disabled(),
                Forms\Components\Textarea::make('description')
                    ->label('Descripción')
                    ->columnSpanFull()
                    ->disabled(),
                Forms\Components\TextInput::make('sourceable_type')
                    ->label('Origen (Tipo)')
                    ->disabled(),
                Forms\Components\TextInput::make('sourceable_id')
                    ->label('Origen (ID)')
                    ->disabled(),
                Forms\Components\DateTimePicker::make('created_at')
                    ->label('Fecha de Creación')
                    ->disabled(),
                Forms\Components\TextInput::make('status')
                    ->label('Estado')
                    ->disabled(),
                Forms\Components\DateTimePicker::make('processed_at')
                    ->label('Fecha de Procesamiento')
                    ->disabled(),
                Forms\Components\KeyValue::make('metadata')
                    ->label('Metadata')
                    ->columnSpanFull()
                    ->disabled(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('type')
                    ->label('Tipo')
                    ->searchable()
                    ->badge()
                    ->color(fn (string $state): string => match (strtolower($state)) {
                        'credit' => 'success',
                        'debit' => 'danger',
                        'commission_payout' => 'info',
                        'bonus_payout' => 'primary',
                        'withdrawal_request' => 'warning',
                        'withdrawal_approved' => 'success',
                        'withdrawal_rejected' => 'danger',
                        'fee' => 'gray',
                        'refund' => 'success',
                        'adjustment_in' => 'info',
                        'adjustment_out' => 'warning',
                        default => 'secondary',
                    }),
                TextColumn::make('amount')
                    ->label('Monto')
                    ->money(fn ($record) => $record->currency_code)
                    ->sortable(),
                TextColumn::make('balance_after_transaction')
                    ->label('Saldo Resultante')
                    ->money(fn ($record) => $record->currency_code)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('description')
                    ->label('Descripción')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->description),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                     ->color(fn (string $state): string => match (strtolower($state)) {
                        'completed' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        'cancelled' => 'gray',
                        'reversed' => 'orange', // Example color for orange
                        default => 'secondary',
                    })
                    ->searchable(),
                TextColumn::make('sourceable_type')
                    ->label('Origen')
                    ->formatStateUsing(fn ($record) => class_basename($record->sourceable_type) . ' (ID: ' . $record->sourceable_id . ')')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(), // Transactions should be system-generated
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(), // Usually not editable
                // Tables\Actions\DeleteAction::make(), // Usually not deletable for audit trail
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}

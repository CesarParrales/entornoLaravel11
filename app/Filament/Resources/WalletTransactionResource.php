<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WalletTransactionResource\Pages;
use App\Filament\Resources\WalletTransactionResource\RelationManagers;
use App\Models\WalletTransaction;
use App\Models\User;
use App\Models\Wallet;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WalletTransactionResource extends Resource
{
    protected static ?string $model = WalletTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left'; // Icon for transactions
    protected static ?string $navigationGroup = 'Finanzas';
    protected static ?string $modelLabel = 'Transacción de Billetera';
    protected static ?string $pluralModelLabel = 'Transacciones de Billetera';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        // Form is mostly for viewing, as transactions are system-generated
        return $form
            ->schema([
                Forms\Components\TextInput::make('transaction_uuid')
                    ->label('UUID de Transacción')
                    ->columnSpanFull()
                    ->disabled(),
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->label('Socio')
                    ->disabled(),
                Forms\Components\Select::make('wallet_id')
                    ->relationship('wallet', 'id') // Displaying wallet ID, or a more descriptive field if Wallet model had one
                    ->label('ID de Billetera')
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
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('user.name') // Assumes user relationship is eager loaded or available
                    ->label('Socio')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('user', function (Builder $q) use ($search) {
                            $q->where('name', 'like', "%{$search}%")
                              ->orWhere('email', 'like', "%{$search}%")
                              ->orWhere('username', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(),
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
                TextColumn::make('description')
                    ->label('Descripción')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->description),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match (strtolower($state)) {
                        'completed' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        'cancelled' => 'gray',
                        'reversed' => 'orange',
                        default => 'secondary',
                    })
                    ->searchable(),
                TextColumn::make('transaction_uuid')
                    ->label('UUID')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Add filters for type, status, user, date range etc.
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                // Edit and Delete are usually not allowed for transactions to maintain audit trail
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWalletTransactions::route('/'),
            // 'create' => Pages\CreateWalletTransaction::route('/create'), // Not needed
            // 'edit' => Pages\EditWalletTransaction::route('/{record}/edit'), // Not needed
            'view' => Pages\ViewWalletTransaction::route('/{record}'), // Restored
        ];
    }

    // Eager load user and wallet for performance
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['user', 'wallet']);
    }

    // Disable creation from this resource directly
    public static function canCreate(): bool
    {
        return false;
    }
}

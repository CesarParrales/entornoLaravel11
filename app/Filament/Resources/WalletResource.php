<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WalletResource\Pages;
use App\Filament\Resources\WalletResource\RelationManagers;
use App\Models\Wallet;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WalletResource extends Resource
{
    protected static ?string $model = Wallet::class;

    protected static ?string $navigationIcon = 'heroicon-o-wallet';
    protected static ?string $navigationGroup = 'Finanzas'; // New group
    protected static ?string $modelLabel = 'Billetera de Socio';
    protected static ?string $pluralModelLabel = 'Billeteras de Socios';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información de la Billetera')
                    ->columns(2)
                    ->schema([
                        Select::make('user_id')
                            ->label('Socio')
                            ->relationship('user', 'name') // Assumes 'name' on User model
                            ->searchable(['name', 'email', 'username'])
                            ->preload()
                            ->required()
                            ->unique(ignoreRecord: true) // Each user should have only one wallet
                            ->disabledOn('edit'), // Cannot change user once wallet is created
                        TextInput::make('balance')
                            ->label('Saldo Actual')
                            ->numeric()
                            ->prefix('$') // Or use currency() method if available and configured
                            ->required()
                            ->default(0.00),
                        Select::make('currency_code')
                            ->label('Moneda')
                            ->options([
                                'USD' => 'USD - Dólar Americano',
                                // Add other currencies if needed
                            ])
                            ->default('USD')
                            ->required()
                            ->disabled(), // Usually fixed per system or user country
                        Select::make('status')
                            ->label('Estado de la Billetera')
                            ->options([
                                'active' => 'Activa',
                                'suspended' => 'Suspendida',
                                'frozen' => 'Congelada',
                                'closed' => 'Cerrada',
                            ])
                            ->required()
                            ->default('active'),
                        DateTimePicker::make('last_transaction_at')
                            ->label('Última Transacción En')
                            ->readOnly(),
                        Textarea::make('notes')
                            ->label('Notas Internas')
                            ->columnSpanFull(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Socio')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.email')
                    ->label('Email Socio')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('balance')
                    ->label('Saldo')
                    ->money('USD') // Or use the wallet's currency_code dynamically
                    ->sortable(),
                TextColumn::make('currency_code')->label('Moneda')->searchable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'suspended' => 'warning',
                        'frozen' => 'danger',
                        'closed' => 'gray',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),
                TextColumn::make('last_transaction_at')
                    ->label('Última Transacción')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Última Actualización')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(), // Deleting wallets might be risky
                ]),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TransactionsRelationManager::class, // Restored
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWallets::route('/'),
            'create' => Pages\CreateWallet::route('/create'),
            'edit' => Pages\EditWallet::route('/{record}/edit'),
            'view' => Pages\ViewWallet::route('/{record}'), // Restored
        ];
    }

    // Eager load user for performance
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('user');
    }
}

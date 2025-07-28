<?php

namespace App\Filament\Resources\CompanySettingResource\RelationManagers;

use App\Models\Bank; // Import Bank model
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BankAccountsRelationManager extends RelationManager
{
    protected static string $relationship = 'bankAccounts';

    protected static ?string $recordTitleAttribute = 'account_number'; // Or a more descriptive combination

    protected static ?string $modelLabel = 'Cuenta Bancaria';
    protected static ?string $pluralModelLabel = 'Cuentas Bancarias';


    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('bank_id')
                    ->label('Banco')
                    ->options(Bank::where('is_active', true)->pluck('name', 'id')) // Use Bank model
                    ->searchable()
                    ->required(),
                Select::make('account_type')
                    ->label('Tipo de Cuenta')
                    ->options([
                        'corriente' => 'Corriente',
                        'ahorros' => 'Ahorros',
                        // Add other types if necessary
                    ])
                    ->required(),
                TextInput::make('account_number')
                    ->label('Número de Cuenta')
                    ->required()
                    ->maxLength(255),
                TextInput::make('account_holder_name')
                    ->label('Nombre del Titular')
                    ->required()
                    ->maxLength(255),
                Toggle::make('is_active')
                    ->label('Activa')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('bank.name') // Access related Bank's name
                    ->label('Banco')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('account_type')
                    ->label('Tipo de Cuenta')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('account_number')
                    ->label('Número de Cuenta')
                    ->searchable(),
                TextColumn::make('account_holder_name')
                    ->label('Titular')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Activa')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}

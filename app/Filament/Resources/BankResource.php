<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BankResource\Pages;
use App\Filament\Resources\BankResource\RelationManagers;
use App\Models\Bank;
use App\Imports\BanksImport; // Lo dejamos por si se usa la ImportAction original en el futuro
// use App\Imports\SimpleTestImport; // Eliminado
use Filament\Forms;
// Imports para la acción personalizada eliminados:
// use Filament\Forms\Components\FileUpload;
// use Filament\Forms\Components\Placeholder;
// use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Log;
// use Maatwebsite\Excel\Facades\Excel;
// use Filament\Forms\Components\Livewire as FilamentLivewireComponent;
// use Illuminate\Support\Facades\Cache;
// use Illuminate\Support\Facades\Auth;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\ImportAction; // Added for ImportAction
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BankResource extends Resource
{
    protected static ?string $model = Bank::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';
    protected static ?string $navigationGroup = 'Configuración de Empresa';
    protected static ?int $navigationSort = 1; 
    protected static ?string $recordTitleAttribute = 'name';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->unique(Bank::class, 'name', ignoreRecord: true)
                    ->label('Nombre del Banco'),
                Forms\Components\TextInput::make('code')
                    ->maxLength(255)
                    ->nullable()
                    ->unique(Bank::class, 'code', ignoreRecord: true)
                    ->label('Código del Banco'),
                Forms\Components\Toggle::make('is_active')
                    ->required()
                    ->default(true)
                    ->label('Activo'),
            ]);
    }

    public static function table(Table $table): Table
    {
        Log::channel('import')->info('[BankResource] table() INVOCADO. Configurando tabla y acciones.');
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre del Banco')
                    ->searchable()->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->label('Código')
                    ->searchable()->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean()->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Se elimina la acción de importación personalizada.
                // Si se desea la ImportAction original de Filament, se puede descomentar y configurar.
                // ImportAction::make()
                //     ->importer(BanksImport::class) // Asegurarse que BanksImport está bien configurado si se usa
                //     ->label('Importar Bancos (CSV/Excel)')
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(), 
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListBanks::route('/'),
            'create' => Pages\CreateBank::route('/create'),
            'edit' => Pages\EditBank::route('/{record}/edit'),
            'view' => Pages\ViewBank::route('/{record}'), 
        ];
    }    
}

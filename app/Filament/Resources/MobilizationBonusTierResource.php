<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MobilizationBonusTierResource\Pages;
use App\Filament\Resources\MobilizationBonusTierResource\RelationManagers;
use App\Models\MobilizationBonusTier;
use App\Models\Rank; // Importar Rank
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model; // Importar Model

class MobilizationBonusTierResource extends Resource
{
    protected static ?string $model = MobilizationBonusTier::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'Configuraciones MLM';
    protected static ?string $modelLabel = 'Nivel de Bono Movilización';
    protected static ?string $pluralModelLabel = 'Niveles de Bono Movilización';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('rank_id')
                    ->label('Rango Mínimo para este Nivel')
                    ->options(Rank::orderBy('rank_order')->pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->unique(ignoreRecord: true, table: 'mobilization_bonus_tiers'),
                TextInput::make('required_consecutive_periods')
                    ->label('Periodos Consecutivos Requeridos')
                    ->numeric()
                    ->default(2)
                    ->minValue(1)
                    ->required(),
                TextInput::make('bonus_amount')
                    ->label('Monto del Bono')
                    ->numeric()
                    ->prefix('$')
                    ->step(0.01)
                    ->required(),
                Toggle::make('is_active')
                    ->label('Activo')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('rank.name')
                    ->label('Rango Mínimo')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('required_consecutive_periods')
                    ->label('Periodos Requeridos')
                    ->sortable(),
                TextColumn::make('bonus_amount')
                    ->label('Monto Bono')
                    ->money('usd')
                    ->sortable(),
                IconColumn::make('is_active')->label('Activo')->boolean(),
                TextColumn::make('created_at')->label('Creado')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->label('Actualizado')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('rank_rank_order', 'asc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->join('ranks', 'mobilization_bonus_tiers.rank_id', '=', 'ranks.id')
            ->select(
                'mobilization_bonus_tiers.id', 
                'mobilization_bonus_tiers.rank_id',
                'mobilization_bonus_tiers.required_consecutive_periods',
                'mobilization_bonus_tiers.bonus_amount',
                'mobilization_bonus_tiers.is_active',
                'mobilization_bonus_tiers.created_at',
                'mobilization_bonus_tiers.updated_at',
                'ranks.name as rank_name', 
                'ranks.rank_order as rank_rank_order'
            );
    }
    
    /**
     * Resolve the model for the route binding.
     * Qualify the 'id' column to avoid ambiguity with joined tables.
     */
    public static function resolveRecordRouteBinding($key): ?Model // Cambiado a static
    {
        $query = static::getModel()::query();
        return $query->where((new (static::getModel()))->getQualifiedKeyName(), $key)->first();
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
            'index' => Pages\ListMobilizationBonusTiers::route('/'),
            'create' => Pages\CreateMobilizationBonusTier::route('/create'),
            'edit' => Pages\EditMobilizationBonusTier::route('/{record}/edit'),
        ];
    }    
}

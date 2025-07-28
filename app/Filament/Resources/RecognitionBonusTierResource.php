<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RecognitionBonusTierResource\Pages;
use App\Filament\Resources\RecognitionBonusTierResource\RelationManagers;
use App\Models\RecognitionBonusTier;
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

class RecognitionBonusTierResource extends Resource
{
    protected static ?string $model = RecognitionBonusTier::class;

    protected static ?string $navigationIcon = 'heroicon-o-star'; // Icono sugerido
    protected static ?string $navigationGroup = 'Configuraciones MLM';
    protected static ?string $modelLabel = 'Nivel de Bono Reconocimiento';
    protected static ?string $pluralModelLabel = 'Niveles de Bono Reconocimiento';
    protected static ?int $navigationSort = 5; // Ajustar según necesidad

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('rank_id')
                    ->label('Rango')
                    ->options(Rank::orderBy('rank_order')->pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->unique(ignoreRecord: true, table: 'recognition_bonus_tiers'),
                TextInput::make('annual_periods_required')
                    ->label('Periodos Anuales Consecutivos Requeridos')
                    ->numeric()
                    ->minValue(1)
                    ->required()
                    ->helperText('Número de periodos de cierre (ej. quincenales) en el año que se debe mantener el rango.'),
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
                    ->label('Rango')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('annual_periods_required')
                    ->label('Periodos Anuales Requeridos')
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
            ->defaultSort('rank_rank_order_for_sort', 'asc');
    }

    // Si se necesita el join para el defaultSort por rank.rank_order, como en MobilizationBonusTierResource
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->join('ranks', 'recognition_bonus_tiers.rank_id', '=', 'ranks.id')
            ->select('recognition_bonus_tiers.*', 'ranks.rank_order as rank_rank_order_for_sort'); 
            // Usar un alias diferente si 'rank_rank_order' ya está en uso por otro join en una vista más compleja
    }
    
/**
     * Resolve the model for the route binding.
     * Qualify the 'id' column to avoid ambiguity with joined tables.
     */
    public static function resolveRecordRouteBinding($key): ?Model
    {
        // Construir la consulta base del modelo sin los joins para la búsqueda del registro.
        $query = static::getModel()::query();
        
        // Aplicar la condición WHERE usando el nombre calificado de la clave primaria del modelo.
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
            'index' => Pages\ListRecognitionBonusTiers::route('/'),
            'create' => Pages\CreateRecognitionBonusTier::route('/create'),
            'edit' => Pages\EditRecognitionBonusTier::route('/{record}/edit'),
            // 'view' => Pages\ViewRecognitionBonusTier::route('/{record}'), // No generada por defecto
        ];
    }    
}

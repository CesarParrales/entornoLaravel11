<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ConfigurableBundleOptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'configurableBundleOptions';

    // protected static ?string $recordTitleAttribute = 'name';

    public function form(Form $form): Form
    {
        // Formulario para EditAction (para editar default_quantity de una opción ya vinculada)
        return $form
            ->schema([
                Forms\Components\Select::make('option_product_id') // Columna en la tabla pivote
                    ->label('Producto Opcional')
                    ->options(Product::where('product_type', 'simple')->pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->disabledOn('edit'), // No permitir cambiar el producto, solo su cantidad por defecto
                Forms\Components\TextInput::make('default_quantity') // Atributo pivote
                    ->label('Cantidad por Defecto (si aplica)')
                    ->numeric()
                    ->nullable()
                    ->default(1)
                    ->minValue(1),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name') // 'name' del producto opcional
                    ->label('Producto Opcional'),
                Tables\Columns\TextColumn::make('default_quantity') // Atributo pivote
                    ->label('Cantidad por Defecto'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect()
                            ->label('Seleccionar Producto Opcional')
                            ->options(Product::where('product_type', 'simple')->pluck('name', 'id'))
                            ->searchable(),
                        Forms\Components\TextInput::make('default_quantity')
                            ->label('Cantidad por Defecto (si aplica)')
                            ->numeric()
                            ->nullable()
                            ->default(1)
                            ->minValue(1),
                    ])
                    ->preloadRecordSelect()
                    ->label('Añadir Producto Opcional Existente'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}

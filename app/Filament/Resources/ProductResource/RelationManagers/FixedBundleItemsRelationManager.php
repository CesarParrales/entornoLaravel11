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

class FixedBundleItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'fixedBundleItems';

    public function form(Form $form): Form
    {
        // Este formulario se usa para la acción de Edición (EditAction)
        // y permitiría cambiar la cantidad o incluso el producto componente de una entrada existente.
        return $form
            ->schema([
                Forms\Components\Select::make('component_product_id') 
                    ->label('Producto Componente')
                    ->options(Product::where('product_type', 'simple')->pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->disabledOn('edit') // Deshabilitar cambiar el producto al editar, solo permitir cambiar la cantidad
                    ->validationMessages([
                        // 'unique' => 'Este producto ya ha sido añadido como componente.', // Considerar validación de unicidad si es necesario
                    ]),
                Forms\Components\TextInput::make('quantity') // Atributo pivote
                    ->required()
                    ->numeric()
                    ->default(1)
                    ->minValue(1),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name') // Muestra el atributo 'name' del Product componente relacionado
                    ->label('Producto Componente'), 
                Tables\Columns\TextColumn::make('quantity') // Muestra el atributo pivote 'quantity'
                    ->label('Cantidad'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(), // Reemplazado por AttachAction
                Tables\Actions\AttachAction::make()
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect() // Campo para seleccionar el Product existente a adjuntar
                            ->label('Seleccionar Producto Componente')
                            ->options(Product::where('product_type', 'simple')->pluck('name', 'id')) // Asegurar que las opciones sean consistentes
                            ->searchable(),
                        Forms\Components\TextInput::make('quantity') // Campo para el atributo pivote 'quantity'
                            ->required()
                            ->numeric()
                            ->default(1)
                            ->minValue(1),
                    ])
                    ->preloadRecordSelect() // Mejora la UX precargando opciones del select
                    ->label('Añadir Componente Existente'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(), // Permite editar la 'quantity' (y 'component_product_id' si no está disabledOn('edit'))
                Tables\Actions\DetachAction::make(), 
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}

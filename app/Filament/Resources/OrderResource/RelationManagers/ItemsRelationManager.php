<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString; // Para formateo HTML en columnas

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';
    protected static ?string $recordTitleAttribute = 'product_name';

    public function form(Form $form): Form
    {
        // Este formulario se usaría si se habilita una acción de View o Edit en la tabla de ítems.
        // Generalmente, los ítems de un pedido son de solo lectura una vez creado el pedido.
        return $form
            ->schema([
                Forms\Components\TextInput::make('product_name')
                    ->label('Nombre del Producto')
                    ->disabled()
                    ->columnSpan(2),
                Forms\Components\TextInput::make('product.sku') // Asumiendo que la relación 'product' está cargada o es accesible
                    ->label('SKU')
                    ->disabled(),
                Forms\Components\TextInput::make('quantity')
                    ->label('Cantidad')
                    ->numeric()
                    ->disabled(),
                Forms\Components\TextInput::make('price')
                    ->label('Precio Unitario')
                    ->money(config('app.currency_symbol', '$')) // Usar símbolo de moneda de config o default
                    ->disabled(),
                Forms\Components\TextInput::make('subtotal')
                    ->label('Subtotal del Ítem')
                    ->money(config('app.currency_symbol', '$'))
                    ->disabled(),
                Forms\Components\KeyValue::make('options')
                    ->label('Opciones / Configuración')
                    ->columnSpanFull()
                    ->disabled()
                    ->dehydrated(false), // No intentar guardar esto al ver/editar
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('product.main_image_url')
                    ->label('') // Sin etiqueta para que sea solo la imagen
                    ->defaultImageUrl(url('/images/default_product_placeholder.png'))
                    ->circular()
                    ->size(40),
                Tables\Columns\TextColumn::make('product_name')
                    ->label('Producto')
                    ->searchable()
                    ->description(function ($record) {
                        if (is_array($record->options) && !empty($record->options)) {
                            $details = collect($record->options)->map(function ($value, $key) {
                                // Asumiendo que $value es el nombre del producto opción y $key su ID
                                // O si $value es un array con más detalles, ajusta aquí.
                                return e($value); // Solo mostrar el nombre de la opción
                            })->implode(', ');
                            return new HtmlString("<small class='text-gray-500'><em>Config: " . $details . "</em></small>");
                        }
                        return '';
                    }),
                Tables\Columns\TextColumn::make('product.sku')->label('SKU')->searchable(),
                Tables\Columns\TextColumn::make('quantity')->label('Cant.')->numeric()->alignCenter(),
                Tables\Columns\TextColumn::make('price')->label('Precio U.')->money(config('app.currency_symbol', '$'))->sortable(),
                Tables\Columns\TextColumn::make('subtotal')->label('Subtotal')->money(config('app.currency_symbol', '$'))->sortable(),
            ])
            ->filters([
                // No se suelen necesitar filtros aquí
            ])
            ->headerActions([
                // No permitir crear ítems directamente en un pedido existente
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                // Generalmente no se editan ítems de pedidos directamente
                // Tables\Actions\ViewAction::make(), 
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(), // No permitir borrar ítems directamente
                // ]),
            ])
            ->emptyStateActions([
                // No permitir crear ítems si la tabla está vacía
            ]);
    }
}

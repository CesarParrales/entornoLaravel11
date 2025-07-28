<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use App\Models\Category; // Import Category
use App\Models\Country; // Import Country
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload; // Added FileUpload
use Filament\Forms\Form;
use Filament\Forms\Set; // Import Set
use Illuminate\Support\Str; // Import Str
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn; // Added ImageColumn
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationGroup = 'E-commerce';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Product Information')->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),
                    TextInput::make('slug')
                        ->required()
                        ->maxLength(255)
                        ->unique(Product::class, 'slug', ignoreRecord: true),
                    TextInput::make('sku')
                        ->label('SKU')
                        ->maxLength(255)
                        ->unique(Product::class, 'sku', ignoreRecord: true),
                    Select::make('product_type')
                        ->options([
                            'simple' => 'Simple',
                            'bundle_fixed' => 'Bundle Fijo',
                            'bundle_configurable' => 'Bundle Configurable',
                        ])
                        ->required()
                        ->default('simple')
                        ->live() // Changed to live() for immediate conditional visibility
                        ->reactive(),
                    Textarea::make('short_description')
                        ->columnSpanFull(),
                    Textarea::make('description')
                        ->label('Full Description')
                        ->columnSpanFull(),
                    Textarea::make('ingredients')
                        ->columnSpanFull(),
                    Textarea::make('properties')
                        ->columnSpanFull(),
                    Textarea::make('content_details')
                        ->label('Content & Specifications')
                        ->columnSpanFull(),
                    FileUpload::make('main_image_path')
                        ->label('Main Image')
                        ->image()
                        ->disk('public')
                        ->directory('products/main_images')
                        ->visibility('public')
                        ->columnSpanFull(),
                ])->columns(2),

                Section::make('Pricing & Points')->schema([
                    TextInput::make('base_price')
                        ->label('Base Price (USD)')
                        ->required()
                        ->numeric()
                        ->prefix('$'),
                    TextInput::make('points_value')
                        ->label('Points Value')
                        ->required()
                        ->numeric()
                        ->default(0),
                ])->columns(2),
                
                Section::make('Organization')->schema([
                    Select::make('category_id')
                        ->relationship('category', 'name')
                        ->searchable()
                        ->preload(),
                ])->columns(2),

                Section::make('Status & Visibility')->schema([
                    Toggle::make('is_active')
                        ->required()
                        ->default(true),
                    Toggle::make('is_featured')
                        ->required()
                        ->default(false),
                ])->columns(2),

                Section::make('Promotions')->schema([
                    Toggle::make('is_on_promotion')
                        ->reactive(),
                    TextInput::make('promotional_price')
                        ->label('Promotional Price (USD)')
                        ->numeric()
                        ->prefix('$')
                        ->visible(fn (Forms\Get $get) => $get('is_on_promotion')),
                    DateTimePicker::make('promotion_start_date')
                        ->visible(fn (Forms\Get $get) => $get('is_on_promotion')),
                    DateTimePicker::make('promotion_end_date')
                        ->visible(fn (Forms\Get $get) => $get('is_on_promotion')),
                ])->columns(2),

                Section::make('Country Specific Prices')
                    ->description('Define precios específicos para diferentes países. Si no se define un precio para un país, se usará el Precio Base.')
                    ->collapsible()
                    ->schema([
                        Repeater::make('countryPrices')
                            ->relationship()
                            ->schema([
                                Select::make('country_id')
                                    ->label('Country')
                                    ->options(Country::pluck('name', 'id')->all())
                                    ->searchable()
                                    ->required()
                                    ->reactive()
                                    ->distinct()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                                TextInput::make('price')
                                    ->label('Price (USD)')
                                    ->numeric()
                                    ->required()
                                    ->prefix('$'),
                            ])
                            ->columns(2)
                            ->defaultItems(0),
                    ]),

                Section::make('Bonus Information')
                    ->description('Configuración de bonos para este producto (si aplica).')
                    ->collapsible()
                    ->visible(fn (Forms\Get $get) => in_array($get('product_type'), ['bundle_fixed', 'bundle_configurable']))
                    ->schema([
                        Toggle::make('pays_bonus')
                            ->label('¿Este paquete otorga un bono?')
                            ->reactive()
                            ->default(false),
                        TextInput::make('bonus_amount')
                            ->label('Monto del Bono (USD)')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->visible(fn (Forms\Get $get) => $get('pays_bonus')),
                    ])->columns(2),

                // Section for Fixed Bundle Items removed, will be handled by RelationManager
                // Section for Configurable Bundle Options (including min/max fields and Repeater) removed, will be handled by RelationManager
                // The min_configurable_items and max_configurable_items fields will be moved to the ConfigurableBundleOptionsRelationManager if needed,
                // or managed differently, as they are properties of the bundle product itself, not the relation.
                // For now, we keep them in the main form, but their Repeater is removed.
                 Section::make('Configuración del Bundle Personalizable (Límites)')
                    ->description('Define los límites de ítems para este paquete personalizable. Las opciones se gestionan abajo.')
                    ->collapsible()
                    ->visible(fn (Forms\Get $get) => $get('product_type') === 'bundle_configurable')
                    ->schema([
                        TextInput::make('min_configurable_items')
                            ->label('Mínimo de ítems a seleccionar')
                            ->numeric()
                            ->default(1)
                            ->minValue(1),
                        TextInput::make('max_configurable_items')
                            ->label('Máximo de ítems a seleccionar')
                            ->numeric()
                            ->default(1)
                            ->minValue(1),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('main_image_path')
                    ->label('Image')
                    ->disk('public')
                    ->defaultImageUrl(url('/images/default_product_placeholder.png')), // Optional: placeholder
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),
                TextColumn::make('product_type')
                    ->badge()
                    ->searchable(),
                TextColumn::make('base_price')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('points_value')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean(),
                IconColumn::make('is_featured')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('category.name')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_on_promotion')
                    ->boolean()
                    ->label('On Promo')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('promotional_price')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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

    public static function getRelations(): array
    {
        return [
            RelationManagers\FixedBundleItemsRelationManager::class,
            RelationManagers\ConfigurableBundleOptionsRelationManager::class, // Added
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}

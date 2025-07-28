<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CountryResource\Pages;
use App\Filament\Resources\CountryResource\RelationManagers;
use App\Models\Country;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CountryResource extends Resource
{
    protected static ?string $model = Country::class;

    protected static ?string $navigationIcon = 'heroicon-o-flag';
    protected static ?string $navigationGroup = 'Geografía'; // Changed group
    protected static ?int $navigationSort = 1; // First in Geografía group


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información Principal')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('iso_code_2')
                            ->required()
                            ->length(2)
                            ->label('ISO Code (2 letras)')
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('iso_code_3')
                            ->length(3)
                            ->label('ISO Code (3 letras)')
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('geoname_id')
                            ->numeric()
                            ->nullable()
                            ->unique(Country::class, 'geoname_id', ignoreRecord: true)
                            ->label('GeoNames ID (Opcional)')
                            ->helperText('ID numérico de GeoNames para este país.')
                            ->columnSpan(1),
                        Forms\Components\Toggle::make('is_active')
                            ->required()
                            ->default(true)
                            ->columnSpanFull(), // Adjusted span
                    ]),
                
                Forms\Components\Section::make('Moneda y Tasas')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('currency_code')
                            ->required()
                            ->maxLength(3)
                            ->label('Código de Moneda'),
                        Forms\Components\TextInput::make('currency_symbol')
                            ->maxLength(5)
                            ->label('Símbolo de Moneda'),
                        Forms\Components\TextInput::make('usd_exchange_rate')
                            ->numeric()
                            ->label('Tasa de Cambio (vs USD)')
                            ->helperText('Tasa de cambio contra USD, para fines informativos.'),
                        Forms\Components\TextInput::make('vat_rate')
                            ->numeric()
                            ->step(0.0001)
                            ->label('Tasa de IVA (ej. 0.15 para 15%)')
                            ->helperText('Dejar vacío si no aplica o se usa configuración global.'),
                        Forms\Components\TextInput::make('vat_label')
                            ->maxLength(50)
                            ->label('Etiqueta de IVA (ej. IVA, GST)')
                            ->default('IVA'),
                    ]),

                Forms\Components\Section::make('Formatos y Códigos Regionales')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('phone_country_code')
                            ->maxLength(10)
                            ->label('Código Telefónico del País (ej. +593)'),
                        Forms\Components\TextInput::make('dni_label')
                            ->maxLength(50)
                            ->label('Etiqueta DNI/ID (ej. Cédula, RUC)'),
                        Forms\Components\TextInput::make('dni_format_regex')
                            ->maxLength(255)
                            ->label('Regex Formato DNI'),
                        Forms\Components\TextInput::make('dni_fixed_length')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->label('Longitud Fija DNI'),
                        Forms\Components\TextInput::make('phone_national_format_regex')
                            ->maxLength(255)
                            ->label('Regex Formato Teléfono Nacional'),
                        Forms\Components\TextInput::make('phone_national_fixed_length')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->label('Longitud Fija Teléfono Nacional'),
                    ]),
                
                Forms\Components\Section::make('Etiquetas Divisiones Administrativas')
                    ->columns(3)
                    ->description('Define cómo se llamarán las divisiones administrativas en este país. Ej: Provincia, Ciudad, Parroquia.')
                    ->schema([
                        Forms\Components\TextInput::make('administrative_division_label_1')
                            ->label('Etiqueta Nivel 1 (ej. Provincia)')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('administrative_division_label_2')
                            ->label('Etiqueta Nivel 2 (ej. Ciudad)')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('administrative_division_label_3')
                            ->label('Etiqueta Nivel 3 (ej. Parroquia)')
                            ->maxLength(100),
                    ]),

                Forms\Components\Section::make('Configuraciones Regionales por Defecto')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('default_language_code')
                            ->maxLength(5)
                            ->label('Código de Idioma por Defecto (ej. es, en)'),
                        Forms\Components\TextInput::make('default_timezone')
                            ->maxLength(255)
                            ->label('Zona Horaria por Defecto (ej. America/Guayaquil)'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()->sortable(),
                Tables\Columns\TextColumn::make('iso_code_2')
                    ->label('ISO 2')
                    ->searchable()->sortable(),
                Tables\Columns\TextColumn::make('geoname_id')
                    ->label('GeoNames ID')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('currency_code')
                    ->label('Moneda')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone_country_code')
                    ->label('Prefijo Tel.')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('vat_rate')
                    ->label('Tasa IVA')
                    ->numeric(4) 
                    ->formatStateUsing(fn (Country $record) => $record->vat_rate ? ($record->vat_rate * 100) . '%' : '-')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(), // Consider ViewAction as well
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array // Added RelationManagers
    {
        return [
            RelationManagers\ProvincesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCountries::route('/'),
            // Consider adding Create and Edit pages if ManageCountries doesn't suit all needs
            // 'create' => Pages\CreateCountry::route('/create'),
            // 'edit' => Pages\EditCountry::route('/{record}/edit'),
        ];
    }
}

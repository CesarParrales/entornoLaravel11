<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CityResource\Pages;
use App\Filament\Resources\CityResource\RelationManagers;
use App\Models\City;
use App\Models\Country;
use App\Models\Province;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
// use Filament\Forms\Components\Livewire as FilamentLivewireComponent; // Eliminado, ya no se usa
use Filament\Notifications\Notification; // Mantener para acciones estándar
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action; // Mantener para EditAction, ViewAction, DeleteAction
// use Illuminate\Support\Facades\DB; // Eliminado, no se usa sin la acción masiva
use Illuminate\Support\Facades\Log;   // Mantener para logs generales si es necesario
// use Illuminate\Support\Str; // Eliminado, no se usa sin la acción masiva
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;

class CityResource extends Resource
{
    protected static ?string $model = City::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationGroup = 'Geografía';
    protected static ?int $navigationSort = 3;
    protected static ?string $recordTitleAttribute = 'name';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información General')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('country_id')
                            ->relationship('country', 'name')
                            ->searchable()
                            ->preload()
                            ->live() // Para actualizar dinámicamente las provincias
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('province_id', null))
                            ->required()
                            ->label('País'),
                        Forms\Components\Select::make('province_id')
                            ->label('Provincia/Estado')
                            ->options(function (Get $get): Collection {
                                $countryId = $get('country_id');
                                if (!$countryId) {
                                    return collect();
                                }
                                // Asegurarse que solo se muestren provincias del país seleccionado
                                return Province::where('country_id', $countryId)->orderBy('name')->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->live() // Para que el modo de creación pueda depender de si hay una provincia seleccionada
                            ->required(),
                        Forms\Components\Toggle::make('is_active')
                            ->required()
                            ->default(true)
                            ->label('Activar todas las ciudades a crear')
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Modo de Creación de Ciudades')
                    ->schema([
                        Forms\Components\Radio::make('creation_mode_city')
                            ->label('Seleccione el modo de creación')
                            ->options([
                                'single' => 'Crear una única ciudad',
                                'bulk' => 'Crear múltiples ciudades para la provincia seleccionada (separadas por coma)',
                            ])
                            ->default('single')
                            ->live()
                            ->required()
                            ->disabled(fn (Get $get) => !$get('province_id')) // Deshabilitar si no hay provincia
                            ->helperText(fn (Get $get) => !$get('province_id') ? 'Seleccione un país y una provincia primero.' : ''),
                    ]),
                Forms\Components\Section::make('Detalle de Ciudad (Única)')
                    ->columns(1)
                    ->visible(fn (Get $get) => $get('creation_mode_city') === 'single' && $get('province_id'))
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre de la Ciudad')
                            ->required(fn (Get $get) => $get('creation_mode_city') === 'single')
                            ->maxLength(255),
                    ]),
                Forms\Components\Section::make('Detalle de Ciudades (Masiva)')
                    ->columns(1)
                    ->visible(fn (Get $get) => $get('creation_mode_city') === 'bulk' && $get('province_id'))
                    ->schema([
                        Forms\Components\Textarea::make('city_names_list')
                            ->label('Nombres de Ciudades (separadas por coma)')
                            ->helperText('Ejemplo: Quito, Guayaquil, Cuenca')
                            ->required(fn (Get $get) => $get('creation_mode_city') === 'bulk')
                            ->rows(5),
                        Forms\Components\Placeholder::make('info_masiva_city')
                            ->content('Las ciudades listadas se crearán para la provincia seleccionada, dentro del país seleccionado y con el estado "Activa" indicado arriba.')
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Ciudad')
                    ->searchable()->sortable(),
                Tables\Columns\TextColumn::make('province.name')
                    ->label('Provincia/Estado')
                    ->searchable()->sortable(),
                Tables\Columns\TextColumn::make('country.name')
                    ->label('País')
                    ->searchable()->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activa')
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
                SelectFilter::make('country_id')
                    ->label('País')
                    ->relationship('country', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('province_id')
                    ->label('Provincia/Estado')
                    ->relationship('province', 'name')
                    ->searchable()
                    ->preload()
            ])
            ->headerActions([
                // Acción de creación masiva eliminada
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
            'index' => Pages\ListCities::route('/'),
            'create' => Pages\CreateCity::route('/create'),
            'edit' => Pages\EditCity::route('/{record}/edit'),
            'view' => Pages\ViewCity::route('/{record}'),
        ];
    }
}

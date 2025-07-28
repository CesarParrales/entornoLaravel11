<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProvinceResource\Pages;
use App\Filament\Resources\ProvinceResource\RelationManagers;
use App\Models\Province;
use App\Models\Country;
use App\Imports\ProvincesImport; // Se mantiene por si se reactiva la ImportAction original
use Filament\Forms;
use Illuminate\Support\Facades\Log;
// Se eliminan los uses de la importación personalizada con vista previa
// use Filament\Forms\Components\FileUpload;
// use Filament\Forms\Components\Placeholder;
// use Filament\Forms\Components\Livewire as FilamentLivewireComponent;
// use Illuminate\Support\Facades\Cache;
// use Illuminate\Support\Facades\Auth;
// use Maatwebsite\Excel\Facades\Excel;
// use Filament\Notifications\Notification;
// use App\Imports\ProvincesMaatwebsiteImport;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\ImportAction; // Para la ImportAction original de Filament
use Filament\Tables\Filters\SelectFilter;
// Se elimina el use de Action si solo se usó para la acción personalizada
// use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProvinceResource extends Resource
{
    protected static ?string $model = Province::class;

    protected static ?string $navigationIcon = 'heroicon-o-map'; 
    protected static ?string $navigationGroup = 'Geografía'; 
    protected static ?int $navigationSort = 2; 
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
                            ->required()
                            ->label('País'),
                        Forms\Components\Toggle::make('is_active')
                            ->required()
                            ->default(true)
                            ->label('Estado Activo para Provincias') // Label más corto
                            ->inline(false), // Label encima del toggle
                    ]),
                Forms\Components\Section::make('Modo de Creación')
                    ->schema([
                        Forms\Components\Radio::make('creation_mode')
                            ->label('Seleccione el modo de creación')
                            ->options([
                                'single' => 'Crear una única provincia',
                                'bulk' => 'Crear múltiples provincias (separadas por coma)',
                            ])
                            ->default('single')
                            ->live()
                            ->required(),
                    ]),
                Forms\Components\Section::make('Detalle de Provincia')
                    ->columns(1)
                    ->visible(fn (Forms\Get $get) => $get('creation_mode') === 'single')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre de Provincia/Estado (Única)')
                            ->required(fn (Forms\Get $get) => $get('creation_mode') === 'single')
                            ->maxLength(255),
                    ]),
                Forms\Components\Section::make('Detalle de Provincias (Masiva)')
                    ->columns(1)
                    ->visible(fn (Forms\Get $get) => $get('creation_mode') === 'bulk')
                    ->schema([
                        Forms\Components\Textarea::make('province_names_list')
                            ->label('Nombres de Provincias (separadas por coma)')
                            ->helperText('Ejemplo: Pichincha, Guayas, Azuay')
                            ->required(fn (Forms\Get $get) => $get('creation_mode') === 'bulk')
                            ->rows(5),
                        Forms\Components\Placeholder::make('info_masiva')
                            ->content('Las provincias listadas se crearán para el país seleccionado y con el estado "Activa" indicado arriba.')
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre Provincia/Estado')
                    ->searchable()->sortable(),
                Tables\Columns\TextColumn::make('country.name')
                    ->label('País')
                    ->searchable()->sortable(),
                // Tables\Columns\TextColumn::make('code')
                //     ->label('Código')
                //     ->searchable()->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activa')
                    ->boolean()->sortable(),
                // Tables\Columns\TextColumn::make('geoname_id')
                //     ->label('GeoNames ID')
                //     ->numeric()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),
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
                    ->preload()
            ])
            ->headerActions([
                // Se elimina la acción de importación personalizada con vista previa.
                // Se puede restaurar la ImportAction original de Filament si se desea.
                // ImportAction::make()
                //     ->importer(ProvincesImport::class) // Asegurarse que ProvincesImport está simplificado y funciona
                //     ->label('Importar Provincias (CSV/Excel)')
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
            RelationManagers\CitiesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProvinces::route('/'),
            'create' => Pages\CreateProvince::route('/create'),
            'edit' => Pages\EditProvince::route('/{record}/edit'),
            'view' => Pages\ViewProvince::route('/{record}'),
        ];
    }
}

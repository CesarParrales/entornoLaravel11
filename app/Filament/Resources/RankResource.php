<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RankResource\Pages;
use App\Filament\Resources\RankResource\RelationManagers;
use App\Models\Rank;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str; // Importar Str
use Filament\Forms\Set; // Importar Set

class RankResource extends Resource
{
    protected static ?string $model = Rank::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-trending-up';
    protected static ?string $navigationGroup = 'Configuraciones MLM';
    protected static ?string $modelLabel = 'Rango';
    protected static ?string $pluralModelLabel = 'Configuración de Rangos';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información del Rango')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre del Rango')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state)))
                            ->columnSpan(1), // Ajustar a 1 para hacer espacio al slug
                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->disabled() // Se genera a partir del nombre
                            ->dehydrated() // Asegura que se guarde aunque esté disabled
                            ->maxLength(255)
                            ->columnSpan(1),
                        Textarea::make('description')
                            ->label('Descripción')
                            ->columnSpanFull(),
                        TextInput::make('rank_order')
                            ->label('Orden del Rango')
                            ->required()
                            ->numeric()
                            ->unique(ignoreRecord: true)
                            ->helperText('Define la jerarquía. Menor número = menor rango.'),
                        ColorPicker::make('color_badge')
                            ->label('Color del Badge')
                            ->helperText('Color para identificar visualmente el rango.'),
                        Toggle::make('is_active')
                            ->label('Rango Activo')
                            ->default(true),
                    ]),
                Section::make('Requisitos de Calificación')
                    ->columns(2)
                    ->schema([
                        TextInput::make('required_group_volume')
                            ->label('Volumen Grupal Requerido (VG)')
                            ->numeric()
                            ->default(0),
                        TextInput::make('instant_qualification_personal_points')
                            ->label('Puntos Personales para Calificación Instantánea')
                            ->numeric()
                            ->nullable(),
                        TextInput::make('required_direct_sponsors_count')
                            ->label('Cantidad de Patrocinados Directos Requeridos')
                            ->numeric()
                            ->default(0),
                        Select::make('required_direct_sponsor_rank_id')
                            ->label('Rango Mínimo de Patrocinados Directos')
                            ->relationship('requiredDirectSponsorRank', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->helperText('Rango que deben tener los patrocinados directos.'),
                        TextInput::make('compression_depth_level')
                            ->label('Profundidad de Compresión para Patrocinados')
                            ->numeric()
                            ->nullable()
                            ->helperText('Niveles hacia abajo para buscar patrocinados calificados.'),
                    ]),
                Section::make('Reglas de Piernas (Lados Alfa/Beta)')
                    ->description('Define los requisitos de volumen para las piernas Alfa y Beta, como porcentaje del VG del rango.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('leg_alpha_min_percentage_vg')
                            ->label('Porcentaje Mínimo VG de Pierna Alfa')
                            ->numeric()
                            ->nullable()
                            ->minValue(0)
                            ->maxValue(1) // Assuming 0.00 to 1.00 for percentage
                            ->step(0.01)
                            ->suffix('% (ej. 0.50 para 50%)')
                            ->helperText('Dejar vacío si no aplica.'),
                        TextInput::make('leg_beta_min_percentage_vg')
                            ->label('Porcentaje Mínimo VG de Pierna Beta')
                            ->numeric()
                            ->nullable()
                            ->minValue(0)
                            ->maxValue(1)
                            ->step(0.01)
                            ->suffix('% (ej. 0.50 para 50%)')
                            ->helperText('Dejar vacío si no aplica.'),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('rank_order')->label('Orden')->sortable(),
                TextColumn::make('name')->label('Nombre del Rango')->searchable()->sortable(),
                TextColumn::make('slug')->label('Slug')->searchable()->toggleable(isToggledHiddenByDefault: true),
                ColorColumn::make('color_badge')->label('Color'),
                TextColumn::make('required_group_volume')->label('VG Requerido')->sortable(),
                TextColumn::make('required_direct_sponsors_count')->label('Pat. Directos'),
                TextColumn::make('requiredDirectSponsorRank.name')->label('Rango Pat. Directos'),
                IconColumn::make('is_active')->label('Activo')->boolean(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('rank_order', 'asc');
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
            'index' => Pages\ListRanks::route('/'),
            'create' => Pages\CreateRank::route('/create'),
            'edit' => Pages\EditRank::route('/{record}/edit'),
            'view' => Pages\ViewRank::route('/{record}'), // Restored
        ];
    }
}

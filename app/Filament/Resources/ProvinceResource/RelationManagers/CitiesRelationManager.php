<?php

namespace App\Filament\Resources\ProvinceResource\RelationManagers;

use App\Models\City; 
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model; 
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'cities';
    protected static ?string $recordTitleAttribute = 'name'; // Added

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Nombre de la Ciudad'),
                Forms\Components\TextInput::make('geoname_id')
                    ->numeric()
                    ->nullable()
                    ->unique(City::class, 'geoname_id', ignoreRecord: true) 
                    ->label('GeoNames ID (Opcional)'),
                Forms\Components\TextInput::make('latitude')
                    ->numeric()
                    ->nullable()
                    ->label('Latitud'),
                Forms\Components\TextInput::make('longitude')
                    ->numeric()
                    ->nullable()
                    ->label('Longitud'),
                Forms\Components\Toggle::make('is_active')
                    ->required()
                    ->default(true)
                    ->label('Activa'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            // ->recordTitleAttribute('name') // Already set as static property
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Ciudad')
                    ->searchable()->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activa')
                    ->boolean()->sortable(),
                Tables\Columns\TextColumn::make('geoname_id')
                    ->label('GeoNames ID')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        if ($this->ownerRecord instanceof \App\Models\Province) {
                            $data['country_id'] = $this->ownerRecord->country_id;
                        }
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        if ($this->ownerRecord instanceof \App\Models\Province) {
                            $data['country_id'] = $this->ownerRecord->country_id;
                        }
                        return $data;
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}

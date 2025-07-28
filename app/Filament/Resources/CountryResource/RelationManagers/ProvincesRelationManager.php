<?php

namespace App\Filament\Resources\CountryResource\RelationManagers;

use App\Models\Province; 
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProvincesRelationManager extends RelationManager
{
    protected static string $relationship = 'provinces';
    protected static ?string $recordTitleAttribute = 'name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Nombre de Provincia/Estado'),
                Forms\Components\TextInput::make('code')
                    ->maxLength(255)
                    ->nullable()
                    ->label('Código (ISO, etc.)'),
                Forms\Components\TextInput::make('geoname_id')
                    ->numeric()
                    ->nullable()
                    ->unique(Province::class, 'geoname_id', ignoreRecord: true) 
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
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre Provincia/Estado')
                    ->searchable()->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->label('Código')
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
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ViewAction::make()->url(fn (Province $record): string => \App\Filament\Resources\ProvinceResource::getUrl('view', ['record' => $record])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}

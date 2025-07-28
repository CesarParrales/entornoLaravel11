<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanySettingResource\Pages;
use App\Filament\Resources\CompanySettingResource\RelationManagers; // Added this line
use App\Models\CompanySetting;
use App\Models\Country;
use App\Models\Province;
use App\Models\City;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use Illuminate\Support\Collection; // Required for dependent selects
use Filament\Forms\Get; // Required for dependent selects
use Filament\Forms\Set; // Required for dependent selects

class CompanySettingResource extends Resource
{
    protected static ?string $model = CompanySetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Configuración de Empresa'; // Added this line
    protected static ?string $navigationLabel = 'Datos de la Empresa'; // Changed for clarity within the group
    protected static ?string $modelLabel = 'Configuración de Empresa';
    protected static ?string $pluralModelLabel = 'Configuración de Empresa';
    protected static ?int $navigationSort = 1; // Sort order within the group

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Datos Generales de la Empresa')
                    ->columns(2)
                    ->schema([
                        TextInput::make('ruc')
                            ->label('RUC')
                            ->unique(ignoreRecord: true)
                            ->maxLength(20),
                        TextInput::make('legal_name')
                            ->label('Razón Social')
                            ->maxLength(255),
                        TextInput::make('commercial_name')
                            ->label('Nombre Comercial')
                            ->required()
                            ->maxLength(255),
                    ]),
                Section::make('Logotipos y Favicon')
                    ->columns(2)
                    ->schema([
                        FileUpload::make('logo_platform_light_path')
                            ->label('Logotipo Plataforma (Claro)')
                            ->image()
                            ->directory('company_logos')
                            ->visibility('public')
                            ->columnSpanFull(),
                        FileUpload::make('logo_platform_dark_path')
                            ->label('Logotipo Plataforma (Obscuro)')
                            ->image()
                            ->directory('company_logos')
                            ->visibility('public')
                            ->columnSpanFull(),
                        FileUpload::make('logo_invoicing_path')
                            ->label('Logotipo para Facturación')
                            ->image()
                            ->directory('company_logos')
                            ->visibility('public')
                            ->columnSpanFull(),
                        FileUpload::make('favicon_path')
                            ->label('Favicon')
                            ->image()
                            ->directory('company_logos')
                            ->visibility('public')
                            ->acceptedFileTypes(['image/vnd.microsoft.icon', 'image/x-icon', 'image/png', 'image/svg+xml'])
                            ->columnSpanFull(),
                    ]),
                Section::make('Ubicación')
                    ->columns(2)
                    ->schema([
                        Select::make('country_id')
                            ->label('País')
                            ->options(Country::query()->pluck('name', 'id')->all())
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('province_id', null);
                                $set('city_id', null);
                            }),
                        Select::make('province_id')
                            ->label('Provincia')
                            ->options(function (Get $get): Collection {
                                $countryId = $get('country_id');
                                if (!$countryId) {
                                    return collect();
                                }
                                return Province::query()->where('country_id', $countryId)->pluck('name', 'id');
                            })
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('city_id', null))
                            ->disabled(fn (Get $get): bool => !$get('country_id')),
                        Select::make('city_id')
                            ->label('Ciudad')
                             ->options(function (Get $get): Collection {
                                $provinceId = $get('province_id');
                                if (!$provinceId) {
                                    return collect();
                                }
                                return City::query()->where('province_id', $provinceId)->pluck('name', 'id');
                            })
                            ->searchable()
                            ->live()
                            ->disabled(fn (Get $get): bool => !$get('province_id')),
                        Textarea::make('address')
                            ->label('Dirección Completa')
                            ->columnSpanFull(),
                    ]),
                Section::make('Información de Facturación')
                    ->columns(2)
                    ->schema([
                        Toggle::make('is_special_taxpayer')
                            ->label('¿Es Contribuyente Especial?')
                            ->default(false),
                        TextInput::make('invoice_sequence_start')
                            ->label('Secuencia Inicial de Factura')
                            ->numeric()
                            ->default(1)
                            ->minValue(1),
                        TextInput::make('vat_rate')
                            ->label('Tasa de IVA (%)')
                            ->numeric()
                            ->default(15.00) // Ejemplo Ecuador
                            ->step(0.01)
                            ->suffix('%'),
                        TextInput::make('invoice_establishment_code')
                            ->label('Código de Establecimiento (Factura)')
                            ->default('001')
                            ->maxLength(3),
                        TextInput::make('invoice_emission_point_code')
                            ->label('Punto de Emisión (Factura)')
                            ->default('001')
                            ->maxLength(3),
                    ]),
                Section::make('Información de Contacto')
                    ->columns(2)
                    ->schema([
                        TextInput::make('phone_fixed')
                            ->label('Teléfono Fijo')
                            ->tel(),
                        TextInput::make('phone_mobile')
                            ->label('Teléfono Móvil')
                            ->tel(),
                        TextInput::make('email_primary')
                            ->label('Correo Electrónico Primario')
                            ->email(),
                        TextInput::make('email_secondary')
                            ->label('Correo Electrónico Secundario')
                            ->email(),
                        KeyValue::make('social_media_links')
                            ->label('Redes Sociales')
                            ->keyLabel('Red Social (ej. Facebook, Instagram)')
                            ->valueLabel('Enlace URL')
                            ->addActionLabel('Añadir Enlace de Red Social')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        // Since we expect only one record, the table is more for viewing/editing that record.
        // This table will likely not be shown due to the ManageCompanySettings page redirecting.
        return $table
            ->columns([
                TextColumn::make('commercial_name')->label('Nombre Comercial'),
                TextColumn::make('ruc')->label('RUC'),
                TextColumn::make('email_primary')->label('Email Principal'),
                IconColumn::make('is_special_taxpayer')
                    ->label('Contribuyente Especial')
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->label('Última Modificación')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(), // Keep view action if needed
            ])
            ->paginated(false); // No pagination needed for a single record
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCompanySettings::route('/'),
            'create' => Pages\CreateCompanySetting::route('/create'),
            'edit' => Pages\EditCompanySetting::route('/{record}/edit'),
            // 'view' => Pages\ViewCompanySetting::route('/{record}'), // If you have a view page
        ];
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\BankAccountsRelationManager::class,
        ];
    }
}

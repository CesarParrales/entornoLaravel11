<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use App\Models\Country; // Added
use Filament\Forms;
use Filament\Forms\Components\DatePicker; // Added
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle; // Added
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn; // Added
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as PasswordRule;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?int $navigationSort = 1; // Added to sort navigation items

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información de Cuenta')
                    ->description('Datos básicos de la cuenta y roles.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre Completo (Referencial)')
                            ->helperText('Este nombre es referencial, use los campos de Nombre y Apellido abajo.')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        DateTimePicker::make('email_verified_at')
                            ->label('Email Verificado En')
                            ->readOnly()
                            ->visibleOn('edit'),
                        TextInput::make('password')
                            ->password()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn ($state) => filled($state))
                            ->rule(PasswordRule::defaults())
                            ->confirmed()
                            ->maxLength(255)
                            ->visibleOn('create'),
                        TextInput::make('password_confirmation')
                            ->password()
                            ->dehydrated(false)
                            ->visibleOn('create'),
                        Select::make('roles')
                            ->multiple()
                            ->relationship('roles', 'name')
                            ->preload()
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Información Personal')
                    ->description('Detalles personales del usuario.')
                    ->schema([
                        TextInput::make('first_name')
                            ->label('Nombres')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('last_name')
                            ->label('Apellidos')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('username')
                            ->label('Nombre de Usuario (Nick)')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),
                        DatePicker::make('birth_date')
                            ->label('Fecha de Nacimiento')
                            ->native(false) // Use a more modern date picker
                            ->displayFormat('d/m/Y'),
                        Select::make('gender')
                            ->label('Género')
                            ->options([
                                'male' => 'Masculino',
                                'female' => 'Femenino',
                                'other' => 'Otro',
                                'prefer_not_to_say' => 'Prefiero no decirlo',
                            ]),
                        TextInput::make('dni')
                            ->label('DNI/Cédula/Identificación')
                            ->maxLength(50),
                    ])->columns(2),

                Section::make('Información de Contacto y Dirección')
                    ->description('Datos para contactar y ubicar al usuario.')
                    ->schema([
                        TextInput::make('phone')
                            ->label('Teléfono Principal')
                            ->tel()
                            ->maxLength(50),
                        TextInput::make('address_street')
                            ->label('Calle y Número')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('address_city')
                            ->label('Ciudad')
                            ->maxLength(100),
                        TextInput::make('address_state')
                            ->label('Provincia/Estado')
                            ->maxLength(100),
                        TextInput::make('address_postal_code')
                            ->label('Código Postal')
                            ->maxLength(20),
                        Select::make('address_country_id')
                            ->label('País de Residencia')
                            ->relationship('country', 'name') // Assumes Country model has 'name'
                            ->searchable()
                            ->preload(),
                    ])->columns(2),

                Section::make('Información MLM')
                    ->description('Detalles relacionados con la red multinivel.')
                    ->schema([
                        TextInput::make('mlm_level')
                            ->label('Nivel MLM (Calculado)')
                            ->readOnly()
                            ->numeric()
                            ->default(0), // Default to 0 or handle in model
                        Select::make('sponsor_id')
                            ->label('Patrocinador (Sponsor)')
                            ->relationship('sponsor', 'name') // Assumes User model has 'name' for display
                            ->searchable(['name', 'email', 'username'])
                            ->preload()
                            ->nullable(),
                        Select::make('referrer_id')
                            ->label('Referidor')
                            ->relationship('referrer', 'name')
                            ->searchable(['name', 'email', 'username'])
                            ->preload()
                            ->nullable(),
                        Select::make('placement_id')
                            ->label('Ubicación (Placement)')
                            ->helperText('Para Unilevel, usualmente es el mismo que el Patrocinador.')
                            ->relationship('placement', 'name')
                            ->searchable(['name', 'email', 'username'])
                            ->preload()
                            ->nullable(),
                        // TextInput::make('binary_position') // Placeholder for future binary plan
                        //     ->label('Posición Binaria')
                        //     ->disabled() // For now
                        //     ->helperText('No aplicable actualmente.'),
                    ])->columns(2),

                Section::make('Estado y Acuerdos')
                    ->schema([
                        Select::make('status')
                            ->label('Estado de la Cuenta')
                            ->options([
                                'active' => 'Activa',
                                'inactive' => 'Inactiva',
                                'suspended' => 'Suspendida',
                                'pending_approval' => 'Pendiente de Aprobación',
                            ])
                            ->required()
                            ->default('pending_approval'),
                        Toggle::make('profile_completed')
                            ->label('Perfil Completado (Calculado)')
                            ->disabled()
                            ->helperText('Se marca automáticamente si los campos requeridos del perfil están llenos.'),
                        Toggle::make('agreed_to_terms')
                            ->label('Aceptó Términos y Condiciones')
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->visibleOn('create'),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('first_name')
                    ->label('Nombres')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('last_name')
                    ->label('Apellidos')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('username')
                    ->label('Usuario')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'gray',
                        'suspended' => 'danger',
                        'pending_approval' => 'warning',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('country.name') // Display country name
                    ->label('País')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('mlm_level')
                    ->label('Nivel MLM')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('sponsor.name')
                    ->label('Patrocinador')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('profile_completed')
                    ->label('Perfil Completo')
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('agreed_to_terms')
                    ->label('Aceptó Términos')
                    ->boolean()
                    ->visibleOn('index') // Only show on index, not really editable here
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false), // Show by default
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(), // Added ViewAction
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
            // RelationManagers\OrdersRelationManager::class, // Example
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
            'view' => Pages\ViewUser::route('/{record}'), // Added ViewUser page
        ];
    }

    // Optional: Add a method to eager load relationships for performance
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['roles', 'country', 'sponsor', 'referrer', 'placement']);
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BonusTypeResource\Pages;
use App\Filament\Resources\BonusTypeResource\RelationManagers;
use App\Models\BonusType;
use App\Models\Rank; 
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class BonusTypeResource extends Resource
{
    protected static ?string $model = BonusType::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';
    protected static ?string $navigationGroup = 'Configuraciones MLM';
    protected static ?string $modelLabel = 'Tipo de Bono';
    protected static ?string $pluralModelLabel = 'Tipos de Bono';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información General del Bono')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre del Bono')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state)))
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->label('Slug (Identificador)')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->disabledOn('edit') 
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label('Descripción')
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->label('Bono Activo')
                            ->default(true),
                        Select::make('trigger_event')
                            ->label('Evento Disparador')
                            ->options([
                                'order_paid_by_self' => 'Pedido Propio Pagado (Reconsumo)',
                                'first_order_paid_by_referred_user' => 'Primer Pedido Pagado por Referido (Inicio Rápido)',
                                'order_payment_confirmed' => 'Confirmación de Pago de Orden (General para Referidos)',
                                'period_closed' => 'Cierre de Periodo (Fidelización, Libertad Financiera, Liderazgo, Movilización)',
                                'user_annual_review' => 'Revisión Anual de Usuario (Reconocimiento, Viaje)',
                                'monthly_bonus_review' => 'Revisión Mensual de Bonos (Auto)',
                            ])
                            ->required()
                            ->searchable(),
                        TextInput::make('wallet_transaction_description_template')
                            ->label('Plantilla Descripción Transacción Billetera')
                            ->helperText('Variables: {BONUS_NAME}, {USER_NAME}, {ORDER_ID}, {ORDER_POINTS}, {CALCULATED_AMOUNT}, {RANK_NAME}, {YEAR_NUMBER}, {PAYMENT_NUMBER}, {TOTAL_PAYMENTS}, {CYCLE_NUMBER}, {MONTH_YEAR}, {PERIOD_ID_OR_NAME}, {AWARD_DESCRIPTION}')
                            ->columnSpanFull(),
                    ]),
                Section::make('Configuración de Cálculo General')
                    ->columns(2)
                    ->schema([
                        Select::make('calculation_type')
                            ->label('Tipo de Cálculo')
                            ->options([
                                'fixed_amount' => 'Monto Fijo',
                                'percentage_of_purchase' => 'Porcentaje de Compra',
                                'points_to_currency' => 'Puntos a Moneda',
                                'product_bonus_from_order_items' => 'Bono de Producto desde Items de Orden (Referido)',
                                'rank_permanence_award_products' => 'Premio Productos por Permanencia de Rango (Fidelización)',
                                'rank_permanence_fixed_monetary_award' => 'Premio Monetario Fijo por Permanencia de Rango (Movilización)',
                                'commission_from_table_by_rank_and_volume' => 'Comisión de Tabla por Rango y Volumen (Libertad Financiera)',
                                'percentage_of_direct_downline_earnings' => 'Porcentaje Ganancias Directos (Liderazgo)',
                                'annual_rank_permanence_award' => 'Premio Anual por Permanencia de Rango (Reconocimiento)',
                                'monthly_rank_maintenance_installment' => 'Cuota Mensual por Mantenimiento de Rango (Auto)',
                                'annual_consecutive_rank_permanence_non_monetary' => 'Premio Anual No Monetario por Permanencia Consecutiva (Viaje)',
                            ])
                            ->required()
                            ->live() 
                            ->searchable(),
                        TextInput::make('amount_fixed')
                            ->label('Monto Fijo')
                            ->numeric()
                            ->prefix('$')
                            ->visible(fn (Get $get): bool => $get('calculation_type') === 'fixed_amount')
                            ->requiredIf('calculation_type', 'fixed_amount'),
                        TextInput::make('percentage_value')
                            ->label('Valor del Porcentaje')
                            ->numeric()
                            ->minValue(0)->maxValue(1)->step(0.0001) 
                            ->suffix('% (ej. 0.05 para 5%)')
                            ->visible(fn (Get $get): bool => $get('calculation_type') === 'percentage_of_purchase')
                            ->requiredIf('calculation_type', 'percentage_of_purchase'),
                        TextInput::make('points_to_currency_conversion_factor')
                            ->label('Factor Conversión Puntos a Moneda')
                            ->numeric()
                            ->step(0.0001)
                            ->helperText('Ej: 1.00 (1 punto = $1), 0.10 (1 punto = $0.10)')
                            ->visible(fn (Get $get): bool => $get('calculation_type') === 'points_to_currency')
                            ->requiredIf('calculation_type', 'points_to_currency'),
                    ]),
                Section::make('Configuración Específica: Bono Referido')
                    ->description('Aplica si el tipo de cálculo es "Bono de Producto desde Items de Orden (Referido)"')
                    ->statePath('configuration_details') 
                    ->visible(fn (Get $get): bool => $get('calculation_type') === 'product_bonus_from_order_items')
                    ->columns(1)
                    ->schema([
                        Toggle::make('check_buyer_status')
                            ->label('Verificar Estado del Socio Comprador')
                            ->helperText('Si está activo, se verificará el estado del comprador.')
                            ->default(true)->reactive(),
                        Select::make('required_buyer_status')
                            ->label('Estado Requerido del Comprador (si se verifica)')
                            ->options(['active' => 'Activo', 'inactive' => 'Inactivo', 'pending_approval' => 'Pendiente Aprobación', 'suspended' => 'Suspendido'])
                            ->default('active')
                            ->visible(fn (Get $get): bool => $get('check_buyer_status') === true),
                        Toggle::make('allow_multiple_product_bonuses_per_order')
                            ->label('Permitir Sumar Bonos de Múltiples Productos en la Misma Orden')
                            ->helperText('Si activo, suma bonos de todos los productos calificables. Si no, solo el primero.')
                            ->default(false),
                    ]),
                Section::make('Configuración Específica: Bono Liderazgo')
                    ->description('Aplica si el tipo de cálculo es "Porcentaje Ganancias Directos (Liderazgo)"')
                    ->statePath('configuration_details')
                    ->visible(fn (Get $get): bool => $get('calculation_type') === 'percentage_of_direct_downline_earnings')
                    ->columns(2)
                    ->schema([
                        TextInput::make('percentage_of_earnings')
                            ->label('Porcentaje de Ganancias de Directos')
                            ->numeric()->step(0.001)->minValue(0)->maxValue(1)->default(0.10)
                            ->helperText('Ej: 0.10 para 10%.')->required(),
                        Select::make('min_sponsor_rank_slug')
                            ->label('Rango Mínimo del Patrocinador para Recibir')
                            ->options(Rank::pluck('name', 'slug'))
                            ->searchable()->nullable(),
                    ]),
                Section::make('Configuración Específica: Bono Auto')
                    ->description('Aplica si el slug es "bono-auto". Los valores se guardan en configuration_details.')
                    ->statePath('configuration_details')
                    ->visible(fn (Get $get): bool => $get('slug') === 'bono-auto')
                    ->columns(2)
                    ->schema([
                        Select::make('qualifying_rank_slug')
                            ->label('Rango Calificable para Bono Auto')
                            ->options(Rank::pluck('name', 'slug'))->default('diamante')->searchable()->required(),
                        TextInput::make('bonus_amount_per_month')
                            ->label('Monto Mensual del Bono Auto')
                            ->numeric()->prefix('$')->default(400.00)->required(),
                        TextInput::make('total_payments_per_cycle')
                            ->label('Total de Pagos por Ciclo (Bono Auto)')
                            ->numeric()->integer()->default(48)->required(),
                    ]),
                Section::make('Configuración Específica: Bono Viaje Anual')
                    ->description('Aplica si el slug es "bono-viaje-anual". Los valores se guardan en configuration_details.')
                    ->statePath('configuration_details')
                    ->visible(fn (Get $get): bool => $get('slug') === 'bono-viaje-anual')
                    ->columns(2)
                    ->schema([
                        Select::make('qualifying_rank_slug')
                            ->label('Rango Calificable para Bono Viaje')
                            ->options(Rank::pluck('name', 'slug'))->default('master')->searchable()->required(),
                        TextInput::make('required_consecutive_periods')
                            ->label('Periodos Consecutivos Requeridos en el Año')
                            ->numeric()->integer()->default(4)->minValue(1)->required(),
                        Textarea::make('award_description')
                            ->label('Descripción del Premio/Viaje')
                            ->default('Viaje Anual con Gastos Pagados (Destino por definir)')
                            ->columnSpanFull()->required(),
                    ]),
                Section::make('Detalles de Configuración Adicional (JSON Genérico)')
                    ->description('Usar para configuraciones no cubiertas por campos específicos arriba.')
                    ->collapsible()->collapsed() 
                    ->visible(fn (Get $get): bool => !in_array($get('slug'), [
                        'bono-auto', 
                        'bono-viaje-anual' // Añadido aquí
                    ]) && !in_array($get('calculation_type'), [
                        'product_bonus_from_order_items',
                        'percentage_of_direct_downline_earnings'
                    ]))
                    ->schema([
                        KeyValue::make('configuration_details')
                            ->label('Reglas y Condiciones Específicas (JSON)')
                            ->keyLabel('Clave de Configuración')->valueLabel('Valor')->addActionLabel('Añadir Condición')->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nombre del Bono')->searchable()->sortable(),
                TextColumn::make('slug')->label('Slug')->searchable(),
                IconColumn::make('is_active')->label('Activo')->boolean(),
                TextColumn::make('calculation_type')->label('Tipo Cálculo')->searchable(),
                TextColumn::make('trigger_event')->label('Evento Disparador')->searchable(),
            ])
            ->filters([])
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBonusTypes::route('/'),
            'create' => Pages\CreateBonusType::route('/create'),
            'edit' => Pages\EditBonusType::route('/{record}/edit'),
            'view' => Pages\ViewBonusType::route('/{record}'),
        ];
    }
}

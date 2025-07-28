<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Events\OrderPaymentConfirmed; // Importar el evento
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationGroup = 'Shop';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        // El formulario se usará principalmente para la vista de detalle y edición limitada.
        // La creación de pedidos se realiza a través del checkout del frontend.
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Detalles del Pedido')
                            ->schema([
                                Forms\Components\TextInput::make('id')
                                    ->label('ID Pedido')
                                    ->disabled()
                                    ->dehydrated(false) // No se guarda al editar
                                    ->columnSpan(1),
                                Forms\Components\Select::make('user_id')
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->label('Cliente (Registrado)')
                                    ->disabled() // No se cambia el cliente de un pedido existente
                                    ->columnSpan(1),
                                Forms\Components\TextInput::make('customer_name')
                                    ->label('Nombre Cliente')
                                    ->required()
                                    ->disabled()
                                    ->columnSpan(1),
                                Forms\Components\TextInput::make('customer_email')
                                    ->label('Email Cliente')
                                    ->email()
                                    ->required()
                                    ->disabled()
                                    ->columnSpan(1),
                                Forms\Components\TextInput::make('customer_phone')
                                    ->label('Teléfono Cliente')
                                    ->tel()
                                    ->disabled()
                                    ->columnSpan(1),
                                Forms\Components\Select::make('status')
                                    ->options([
                                        'pending' => 'Pendiente',
                                        'pending_payment' => 'Pendiente de Pago',
                                        'processing' => 'Procesando',
                                        'shipped' => 'Enviado',
                                        'delivered' => 'Entregado',
                                        'completed' => 'Completado',
                                        'cancelled' => 'Cancelado',
                                        'payment_failed' => 'Pago Fallido',
                                    ])
                                    ->required()
                                    ->columnSpan(1),
                                Forms\Components\DateTimePicker::make('created_at')
                                     ->label('Fecha del Pedido')
                                     ->disabled()
                                     ->columnSpan(1),
                                Forms\Components\DateTimePicker::make('paid_at')
                                     ->label('Fecha de Pago')
                                     ->columnSpan(1),
                            ])->columns(2),
                        
                        Forms\Components\Section::make('Detalles de Pago')
                            ->schema([
                                Forms\Components\TextInput::make('payment_method')->label('Método de Pago')->columnSpan(1),
                                Forms\Components\TextInput::make('payment_gateway')->label('Pasarela de Pago')->columnSpan(1),
                                Forms\Components\TextInput::make('payment_gateway_transaction_id')->label('ID Transacción Pasarela')->columnSpan(1),
                            ])->columns(3),
                    ])->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Dirección de Envío')
                            ->schema([
                                Forms\Components\TextInput::make('shipping_address_line1')->label('Dirección Línea 1'),
                                Forms\Components\TextInput::make('shipping_address_line2')->label('Dirección Línea 2'),
                                Forms\Components\TextInput::make('shipping_city')->label('Ciudad'),
                                Forms\Components\TextInput::make('shipping_state')->label('Estado/Provincia'),
                                Forms\Components\TextInput::make('shipping_postal_code')->label('Código Postal'),
                                Forms\Components\TextInput::make('shipping_country_code')->label('Código País (ISO)'),
                            ])->columns(1), // Para que los campos de dirección ocupen todo el ancho de esta sección
                        Forms\Components\Section::make('Totales del Pedido')
                            ->schema([
                                Forms\Components\TextInput::make('subtotal')->numeric()->prefix('$')->disabled(),
                                Forms\Components\TextInput::make('shipping_cost')->label('Costo Envío')->numeric()->prefix('$')->disabled(),
                                Forms\Components\TextInput::make('taxes')->label('Impuestos')->numeric()->prefix('$')->disabled(),
                                Forms\Components\TextInput::make('discount_amount')->label('Descuento')->numeric()->prefix('$')->disabled(),
                                Forms\Components\TextInput::make('total')->label('TOTAL')->numeric()->prefix('$')->disabled()->extraAttributes(['class' => 'font-bold text-lg']),
                            ]),
                        Forms\Components\Textarea::make('notes')
                            ->label('Notas Adicionales')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columnSpan(['lg' => 1]),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('customer_name')->label('Cliente')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('user.email')->label('Email Usuario Registrado')->searchable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('total')->money('USD')->sortable(), // Ajustar moneda si es necesario
                Tables\Columns\TextColumn::make('status')->badge()->sortable()
                    ->color(fn (string $state): string => match ($state) {
                        'pending', 'pending_payment' => 'warning',
                        'processing' => 'info',
                        'shipped', 'delivered' => 'primary',
                        'completed' => 'success',
                        'cancelled', 'payment_failed' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst(str_replace('_', ' ', $state))),
                Tables\Columns\TextColumn::make('payment_method')->label('Método Pago')->searchable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')->label('Fecha Pedido')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('updated_at')->label('Última Actualización')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pendiente',
                        'pending_payment' => 'Pendiente de Pago',
                        'processing' => 'Procesando',
                        'shipped' => 'Enviado',
                        'delivered' => 'Entregado',
                        'completed' => 'Completado',
                        'cancelled' => 'Cancelado',
                        'payment_failed' => 'Pago Fallido',
                    ]),
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Cliente Registrado')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('confirmPayment')
                    ->label('Confirmar Pago Offline')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Order $record): bool => $record->status === 'pending_payment')
                    ->action(function (Order $record, array $data): void {
                        $record->status = 'processing';
                        $record->paid_at = now();
                        
                        // Guardar detalles del pago (Tarea 4.1.4)
                        $record->payment_details = [
                            'method_type' => $data['payment_offline_method'], // Renombrado para claridad
                            'reference' => $data['payment_reference'],
                            'amount_confirmed' => $data['amount_confirmed'],
                            'payment_date' => $data['payment_date'],
                            'notes' => $data['payment_notes'],
                        ];
                        $record->save();

                        event(new OrderPaymentConfirmed($record));

                        \Filament\Notifications\Notification::make()
                            ->title('Pago confirmado')
                            ->success()
                            ->send();
                    })
                    ->form([
                        Forms\Components\Select::make('payment_offline_method')
                            ->label('Método de Pago Offline Utilizado')
                            ->options([
                                'offline_pos' => 'Tarjeta (Punto de Venta)',
                                'bank_deposit' => 'Depósito Bancario',
                                'bank_transfer' => 'Transferencia Bancaria',
                                'cash_pos' => 'Efectivo (Punto de Venta)',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('payment_reference')
                            ->label('Referencia/Voucher/Nro. Transacción')
                            ->required(),
                        Forms\Components\DatePicker::make('payment_date')
                            ->label('Fecha de Pago')
                            ->default(now())
                            ->required(),
                        Forms\Components\TextInput::make('amount_confirmed')
                            ->label('Monto Confirmado')
                            ->numeric()
                            ->prefix('$') // Ajustar según moneda
                            ->required(),
                        Forms\Components\Textarea::make('payment_notes')
                            ->label('Notas Adicionales del Pago')
                            ->columnSpanFull(),
                    ])
                    ->modalHeading('Confirmar Pago Offline')
                    ->modalSubmitActionLabel('Confirmar Pago'),
                Tables\Actions\Action::make('uploadInvoiceNumber')
                    ->label('Cargar Nro. Factura')
                    ->icon('heroicon-o-document-text')
                    ->visible(fn (Order $record): bool => in_array($record->status, ['processing', 'shipped', 'delivered', 'completed'])) // Visible para pedidos pagados/procesados
                    ->action(function (Order $record, array $data): void {
                        $record->invoice_number = $data['invoice_number'];
                        // $record->invoice_date = $data['invoice_date']; // Opcional
                        $record->save();
                        \Filament\Notifications\Notification::make()
                            ->title('Número de factura guardado')
                            ->success()
                            ->send();
                    })
                    ->form([
                        Forms\Components\TextInput::make('invoice_number')
                            ->label('Número de Factura')
                            ->required(),
                        // Forms\Components\DatePicker::make('invoice_date')
                        //     ->label('Fecha de Factura')
                        //     ->default(now()),
                    ])
                    ->modalHeading('Cargar Número de Factura')
                    ->modalSubmitActionLabel('Guardar Factura'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(), // Considerar si se permite borrado masivo de pedidos
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
    public static function getRelations(): array
    {
        return [
            RelationManagers\ItemsRelationManager::class,
        ];
    }
    
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            // No se habilita 'create' ya que los pedidos vienen del frontend/checkout
            // 'create' => Pages\CreateOrder::route('/create'),
            // 'view' => Pages\ViewOrder::route('/{record}'), // Comentado temporalmente
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}

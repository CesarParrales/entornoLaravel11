<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BonusType;
use Illuminate\Support\Str;

class BonusTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        BonusType::updateOrCreate(
            ['slug' => 'bono-reconsumo-puntos'],
            [
                'name' => 'Bono Reconsumo',
                'description' => 'Bono otorgado al socio basado en el total de puntos de sus compras propias, convertido a valor monetario (1 punto = $1).',
                'is_active' => true,
                'calculation_type' => 'points_to_currency',
                'amount_fixed' => null,
                'percentage_value' => null,
                'points_to_currency_conversion_factor' => 1.00, // 1 punto = $1
                'trigger_event' => 'order_paid_by_self',
                'configuration_details' => null, // No conditions specified for now
                'wallet_transaction_description_template' => 'Bono Reconsumo por {ORDER_POINTS} puntos de pedido #{ORDER_ID}',
            ]
        );

        BonusType::updateOrCreate(
            ['slug' => 'bono-inicio-rapido'],
            [
                'name' => 'Bono de Inicio Rápido',
                'description' => 'Paga al referidor el 100% de los puntos del primer pedido pagado de un nuevo socio, convertidos a moneda (1 punto = $1). Se otorga una única vez por cada nuevo socio referido.',
                'is_active' => true,
                'calculation_type' => 'points_to_currency',
                'amount_fixed' => null,
                'percentage_value' => null,
                'points_to_currency_conversion_factor' => 1.00, // 1 punto = $1
                'trigger_event' => 'first_order_paid_by_referred_user',
                'configuration_details' => null, // Lógica de "primer pedido" y "pago único" en BonusService
                'wallet_transaction_description_template' => 'Bono Inicio Rápido por {ORDER_POINTS} puntos del primer pedido #{ORDER_ID} de tu referido {NEW_USER_NAME}',
            ]
        );

        BonusType::updateOrCreate(
            ['slug' => 'bono-referido'],
            [
                'name' => 'Bono Referido',
                'description' => 'Paga un monto al referidor basado en la compra de productos específicos (con pays_bonus activo) por parte de un socio referido. El monto puede ser la suma de los bonos de los productos calificados o el bono del primer producto calificado, según configuración.',
                'is_active' => true,
                'calculation_type' => 'product_bonus_from_order_items', // Nuevo tipo de cálculo
                'amount_fixed' => null,
                'percentage_value' => null,
                'points_to_currency_conversion_factor' => null,
                'trigger_event' => 'order_paid_by_referred_user',
                'configuration_details' => json_encode([
                    'check_buyer_status' => true, // Por defecto, verificar estado del comprador
                    'required_buyer_status' => 'active', // Estado requerido si check_buyer_status es true
                    'allow_multiple_product_bonuses_per_order' => false, // Por defecto, solo el primer producto calificado
                ]),
                'wallet_transaction_description_template' => 'Bono Referido por compra de producto(s) en pedido #{ORDER_ID} de tu referido {BUYER_NAME}',
            ]
        );

        BonusType::updateOrCreate(
            ['slug' => 'bono-fidelizacion-rango'],
            [
                'name' => 'Bono Fidelización por Rango',
                'description' => 'Otorga una cantidad de productos canjeables al socio por mantener un rango calificado durante dos periodos de cierre consecutivos.',
                'is_active' => true,
                'calculation_type' => 'rank_loyalty_product_award', // Nuevo tipo de cálculo
                'amount_fixed' => null,
                'percentage_value' => null,
                'points_to_currency_conversion_factor' => null,
                'trigger_event' => 'period_closed', // Nuevo evento disparador
                'configuration_details' => json_encode([
                    'min_qualifying_rank_slug' => 'bronce', // Slug del rango Bronce (o el ID/slug que uses)
                    'rank_hierarchy_for_comparison' => [ // Debe incluir todos los rangos relevantes y su nivel jerárquico
                        // Asegúrate que los 'slug' coincidan con los slugs de tus rangos en la tabla 'ranks'
                        ['slug' => 'bronce', 'level' => 1],
                        ['slug' => 'lider', 'level' => 2],
                        ['slug' => 'lider-plata', 'level' => 3],
                        ['slug' => 'oro', 'level' => 4], // Ejemplo, añade todos tus rangos
                        // ... más rangos y sus niveles
                    ],
                    'loyalty_award_tiers' => [ // Ordenados de MAYOR a MENOR nivel de premio (según el nivel del rango)
                        // El 'min_rank_slug_for_tier' es el slug del rango mínimo para este nivel de premio
                        ['award_tier_name' => 'Lider Plata y Superior', 'min_rank_slug_for_tier' => 'lider-plata', 'products_to_award' => 3],
                        ['award_tier_name' => 'Lider', 'min_rank_slug_for_tier' => 'lider', 'products_to_award' => 2],
                        ['award_tier_name' => 'Bronce', 'min_rank_slug_for_tier' => 'bronce', 'products_to_award' => 1],
                    ],
                ]),
                'wallet_transaction_description_template' => 'Bono Fidelización: {QUANTITY} producto(s) ganado(s) por mantener rango {RANK_NAME}',
            ]
        );

        BonusType::updateOrCreate(
            ['slug' => 'bono-libertad-financiera'],
            [
                'name' => 'Bono Libertad Financiera',
                'description' => 'Comisión basada en el rango alcanzado y el volumen de puntos comisionables del periodo.',
                'is_active' => true,
                'calculation_type' => 'rank_volume_commission', // Nuevo tipo de cálculo
                'amount_fixed' => null,
                'percentage_value' => null,
                'points_to_currency_conversion_factor' => null, // La conversión 1:1 está implícita en el cálculo
                'trigger_event' => 'period_closed',
                'configuration_details' => json_encode([
                    // 'commission_tiers' ya no se necesita aquí, se leerá de la tabla financial_freedom_commission_tiers
                    'points_reset_after_commission' => true,
                    'rank_reset_target_slug_if_subscription_active' => 'activo', // Slug del rango base "Activo"
                    // Podríamos añadir un flag aquí si este bono específico debe usar la tabla externa, ej:
                    // 'uses_external_commission_table' => true, // Opcional, si queremos ser explícitos
                ]),
                'wallet_transaction_description_template' => 'Comisión Libertad Financiera por Rango {RANK_NAME} en Periodo',
            ]
        );

        BonusType::updateOrCreate(
            ['slug' => 'bono-liderazgo'],
            [
                'name' => 'Bono Liderazgo',
                'description' => 'Comisión sobre las ganancias de los patrocinados directos en el periodo de cierre.',
                'is_active' => true,
                'calculation_type' => 'percentage_of_direct_downline_earnings', // Nuevo tipo de cálculo
                'amount_fixed' => null,
                'percentage_value' => null, // El porcentaje está en configuration_details
                'points_to_currency_conversion_factor' => null,
                'trigger_event' => 'period_closed_for_leadership', // Evento específico o manejado en flujo de cierre
                'configuration_details' => json_encode([
                    'percentage_of_earnings' => 0.10, // 10% configurable
                    // 'source_commission_types' => ['bono-libertad-financiera', 'bono-referido'], // Opcional si sumamos todo de wallet_transactions
                    'min_sponsor_rank_slug' => 'empresario', // Rango mínimo del patrocinador para ganar este bono
                ]),
                'wallet_transaction_description_template' => 'Bono Liderazgo por comisiones de directos en periodo {PERIOD_ID_OR_NAME}',
            ]
        );

        // Bono Movilización (configuración simplificada, los tiers están en su propia tabla)
        BonusType::updateOrCreate(
            ['slug' => 'bono-movilizacion'],
            [
                'name' => 'Bono Movilización',
                'description' => 'Otorga un monto monetario por mantener un rango calificado (desde Líder) por dos periodos consecutivos.',
                'is_active' => true,
                'calculation_type' => 'rank_permanence_fixed_monetary_award', // Nuevo o adaptar uno existente
                'trigger_event' => 'period_closed',
                'configuration_details' => json_encode([
                    'min_qualifying_rank_slug' => 'lider', // Rango mínimo para este bono
                    // La jerarquía de rangos y los montos por tier se leen de MobilizationBonusTier y Ranks
                    // 'rank_hierarchy_for_comparison' podría seguir aquí si es diferente a la del Bono Fidelización
                    // o si queremos que sea configurable por bono. Por ahora, asumimos que el servicio puede
                    // obtener los niveles de los rangos directamente o usar una jerarquía global si es necesario.
                ]),
                'wallet_transaction_description_template' => 'Bono Movilización por permanencia en Rango {RANK_NAME} en Periodo {PERIOD_ID_OR_NAME}',
            ]
        );
        
        // Bono de Reconocimiento Anual
        BonusType::updateOrCreate(
            ['slug' => 'bono-reconocimiento-anual'],
            [
                'name' => 'Bono de Reconocimiento Anual',
                'description' => 'Otorga un monto monetario por mantener un rango calificado (desde Diamante) por un número específico de periodos consecutivos dentro del año calendario del socio (contado desde su primera activación).',
                'is_active' => true, // Se puede activar cuando esté listo
                'calculation_type' => 'annual_rank_permanence_award', // Nuevo tipo de cálculo
                'trigger_event' => 'user_annual_review', // Nuevo evento disparado por cron
                'configuration_details' => json_encode([
                    'min_qualifying_rank_slug' => 'diamante', // Rango mínimo para que este bono aplique
                    // Los tiers (Rango, Periodos Requeridos, Monto) se leen de RecognitionBonusTier
                ]),
                'wallet_transaction_description_template' => 'Bono de Reconocimiento Anual por {RANK_NAME} ({YEAR_NUMBER}º año)',
            ]
        );

        // Bono Auto
        BonusType::updateOrCreate(
            ['slug' => 'bono-auto'],
            [
                'name' => 'Bono Auto',
                'description' => 'Otorga un monto monetario mensual por mantener el rango Diamante o superior, hasta 48 cuotas por ciclo.',
                'is_active' => true, // Se puede activar cuando esté listo
                'calculation_type' => 'monthly_rank_maintenance_installment', // Nuevo tipo de cálculo
                'trigger_event' => 'monthly_bonus_review', // Nuevo evento disparado por cron mensual
                'configuration_details' => json_encode([
                    'qualifying_rank_slug' => 'diamante', // Rango mínimo requerido
                    'bonus_amount_per_month' => 400.00,   // Monto mensual del bono
                    'total_payments_per_cycle' => 48,      // Número de cuotas por ciclo
                ]),
                'wallet_transaction_description_template' => 'Bono Auto - Cuota {PAYMENT_NUMBER}/{TOTAL_PAYMENTS} (Ciclo {CYCLE_NUMBER})',
            ]
        );

        // Bono Viaje Anual
        BonusType::updateOrCreate(
            ['slug' => 'bono-viaje-anual'],
            [
                'name' => 'Bono Viaje Anual',
                'description' => 'Otorga un viaje por mantener el rango Master o superior por 4 periodos consecutivos dentro del año calendario del socio.',
                'is_active' => true,
                'calculation_type' => 'annual_consecutive_rank_permanence_non_monetary',
                'trigger_event' => 'user_annual_review',
                'configuration_details' => json_encode([
                    'qualifying_rank_slug' => 'master',
                    'required_consecutive_periods' => 4,
                    'award_description' => 'Viaje Anual Internacional con Gastos Pagados',
                    // 'rank_hierarchy_for_comparison' => [...] // Opcional, si se necesita una jerarquía específica
                ]),
                'wallet_transaction_description_template' => null, // No genera transacción monetaria directa
            ]
        );
        
        $this->command->info('Seeder de Tipos de Bono ejecutado. Todos los bonos configurados.');
    }
}

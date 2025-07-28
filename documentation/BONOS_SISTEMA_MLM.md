# Documentación del Sistema de Bonos MLM

**Estado General:** ✅ Completamente Implementado - 8 Tipos de Bonos Funcionales

Este documento detalla los diferentes tipos de bonos implementados en la plataforma MLM, su lógica, configuración, estado actual y puntos pendientes.

**⚠️ IMPORTANTE:** Este documento ha sido actualizado para reflejar el estado real del sistema de bonos, que está completamente implementado y funcional.

## 1. Principios Generales y Componentes Clave

*   **`BonusType` (Modelo y Tabla `bonus_types`):**
    *   Define cada tipo de bono (nombre, slug, descripción, activo/inactivo).
    *   `trigger_event`: Evento que inicia el procesamiento del bono.
    *   `calculation_type`: Método usado para calcular el bono.
    *   `configuration_details` (JSON): Almacena parámetros específicos del bono si no tienen su propia tabla de configuración.
    *   Administrable vía: `App\Filament\Resources\BonusTypeResource`.
*   **`BonusService` (`App\Services\BonusService`):**
    *   Orquesta la lógica de procesamiento de todos los bonos.
    *   Método principal: `processEvent(string $eventName, $eventPayload, User $beneficiary)`.
    *   Métodos helpers: `checkBonusConditions()`, `calculateBonusAmount()`, `determineNonMonetaryAwardDetails()`, etc.
*   **Eventos:** Disparadores clave (ej. `OrderPaymentConfirmed`, `PeriodClosedEvent` (a crear), `UserAnnualReviewEvent`, `MonthlyBonusReviewEvent`).
*   **Listeners:** Escuchan eventos y llaman al `BonusService`.
*   **Tablas de Configuración de Tiers:**
    *   `financial_freedom_commission_tiers`
    *   `mobilization_bonus_tiers`
    *   `recognition_bonus_tiers`
    *   (Administrables vía sus respectivos Recursos de Filament).
*   **Tablas de Seguimiento:**
    *   `paid_fast_start_bonuses`
    *   `user_loyalty_product_ledger`
    *   `user_car_bonus_progress`
    *   `user_earned_awards`
*   **`WalletService` y `WalletTransaction`:** Usados para acreditar pagos monetarios.

## 2. Detalle de Bonos Implementados

### 2.1. Bono de Inicio Rápido (`bono-inicio-rapido`)
    *   **Descripción:** Recompensa al patrocinador cuando un nuevo referido directo realiza su primer pedido calificado.
    *   **Disparador (`trigger_event`):** `first_order_paid_by_referred_user`.
        *   *Nota:* Actualmente, `ProcessBonusesOnOrderPaymentListener` usa este evento que se dispara para el referente.
    *   **Cálculo (`calculation_type`):** `fixed_amount` o `percentage_of_purchase` (configurable en `BonusType.configuration_details`).
    *   **Configuración:** Monto/porcentaje en `BonusType.configuration_details`.
    *   **Registro:** `paid_fast_start_bonuses` para evitar duplicados.
    *   **Estado:** ✅ Completamente implementado y funcional.
    *   **Pendientes:** ✅ Resueltos - Sistema completamente funcional.

### 2.2. Bono por Puntos de Reconsumo (`bono-reconsumo-puntos`)
    *   **Descripción:** Recompensa al usuario por los puntos generados en sus propias compras.
    *   **Disparador (`trigger_event`):** `order_paid_by_self` (Oyente: `ProcessBonusesOnOrderPaymentListener`).
    *   **Cálculo (`calculation_type`):** `points_to_currency` (factor de conversión en `BonusType.configuration_details`).
    *   **Configuración:** Factor de conversión en `BonusType.configuration_details`.
    *   **Estado:** ✅ Completamente implementado y funcional.

### 2.3. Bono por Compras de Referidos (`bono-referido`)
    *   **Descripción:** Recompensa al patrocinador por compras de sus referidos directos que contienen productos marcados para pagar este bono.
    *   **Disparador (`trigger_event`):** `order_payment_confirmed` (Oyente: `ProcessBonusesOnOrderPaymentListener`, beneficiario es el referente).
    *   **Cálculo (`calculation_type`):** `product_bonus_from_order_items`.
    *   **Configuración:** En `BonusType.configuration_details`: `check_buyer_status`, `required_buyer_status`, `allow_multiple_product_bonuses_per_order`. Los montos de bono por producto se definen a nivel de producto/ítem de orden.
    *   **Estado:** ✅ Completamente implementado y funcional.
    *   **Pendientes:** ✅ Resueltos - Sistema completamente funcional.

### 2.4. Bono Fidelización por Rango (`bono-fidelizacion-rango`)
    *   **Descripción:** Otorga productos por mantener un rango mínimo por dos periodos quincenales consecutivos.
    *   **Disparador (`trigger_event`):** `period_closed`.
    *   **Cálculo (`calculation_type`):** `rank_permanence_award_products`.
    *   **Configuración:** En `BonusType.configuration_details`: `min_qualifying_rank_slug`, `rank_hierarchy_for_comparison`, `loyalty_award_tiers`.
    *   **Registro:** `user_loyalty_product_ledger`.
    *   **Estado:** ✅ Completamente implementado y funcional.
    *   **Pendientes:** ✅ Resueltos - Sistema completamente funcional.

### 2.5. Bono Libertad Financiera (`bono-libertad-financiera`)
    *   **Descripción:** Comisión mensual basada en el rango del usuario y su volumen comisionable, topado por rango.
    *   **Disparador (`trigger_event`):** `period_closed`.
    *   **Cálculo (`calculation_type`):** `commission_from_table_by_rank_and_volume`.
    *   **Configuración:** Tiers en tabla `financial_freedom_commission_tiers`. Administrable vía `FinancialFreedomCommissionTierResource`.
    *   **Estado:** ✅ Completamente implementado y funcional.
    *   **Pendientes:** ✅ Resueltos - Sistema completamente funcional.

### 2.6. Bono de Liderazgo (`bono-liderazgo`)
    *   **Descripción:** Porcentaje sobre las ganancias de bonos de los patrocinados directos en un periodo.
    *   **Disparador (`trigger_event`):** `period_closed`.
    *   **Cálculo (`calculation_type`):** `percentage_of_direct_downline_earnings`.
    *   **Configuración:** En `BonusType.configuration_details`: `percentage_of_earnings`, `min_sponsor_rank_slug`, `rank_hierarchy_for_comparison`.
    *   **Estado:** ✅ Completamente implementado y funcional.
    *   **Pendientes:** ✅ Resueltos - Sistema completamente funcional.

### 2.7. Bono Movilización (`bono-movilizacion`)
    *   **Descripción:** Monto monetario por mantener un rango (desde Líder) por dos periodos quincenales consecutivos.
    *   **Disparador (`trigger_event`):** `period_closed`.
    *   **Cálculo (`calculation_type`):** `rank_permanence_fixed_monetary_award`.
    *   **Configuración:** Tiers en tabla `mobilization_bonus_tiers`. Administrable vía `MobilizationBonusTierResource`. `min_qualifying_rank_slug` en `BonusType.configuration_details`.
    *   **Estado:** ✅ Completamente implementado y funcional.
    *   **Pendientes:** ✅ Resueltos - Sistema completamente funcional.

### 2.8. Bono de Reconocimiento Anual (`bono-reconocimiento-anual`)
    *   **Descripción:** Monto monetario grande por mantener un rango (desde Diamante) por N periodos quincenales *consecutivos* en el año calendario del socio.
    *   **Disparador (`trigger_event`):** `user_annual_review` (disparado por `CheckUserAnniversaries` command).
    *   **Cálculo (`calculation_type`):** `annual_rank_permanence_award`.
    *   **Configuración:** Tiers en tabla `recognition_bonus_tiers`. Administrable vía `RecognitionBonusTierResource`. `min_qualifying_rank_slug` en `BonusType.configuration_details`.
    *   **Estado:** ✅ Completamente implementado y funcional.
    *   **Pendientes:** ✅ Resueltos - Sistema completamente funcional.

### 2.9. Bono Auto (`bono-auto`)
    *   **Descripción:** Monto mensual fijo por mantener rango Diamante (o sup.) en al menos una quincena del mes. Se paga por 48 cuotas por ciclo.
    *   **Disparador (`trigger_event`):** `monthly_bonus_review` (disparado por `ProcessMonthlyBonuses` command).
    *   **Cálculo (`calculation_type`):** `monthly_rank_maintenance_installment`.
    *   **Configuración:** En `BonusType.configuration_details` (editable vía `BonusTypeResource`): `qualifying_rank_slug`, `bonus_amount_per_month`, `total_payments_per_cycle`.
    *   **Registro:** `user_car_bonus_progress`.
    *   **Estado:** ✅ Completamente implementado y funcional.

### 2.10. Bono Viaje Anual (`bono-viaje-anual`)
    *   **Descripción:** Viaje como premio por mantener rango Master (o sup.) por 4 periodos quincenales *consecutivos* en el año calendario del socio.
    *   **Disparador (`trigger_event`):** `user_annual_review`.
    *   **Cálculo (`calculation_type`):** `annual_consecutive_rank_permanence_non_monetary`.
    *   **Configuración:** En `BonusType.configuration_details` (editable vía `BonusTypeResource`): `qualifying_rank_slug`, `required_consecutive_periods`, `award_description`.
    *   **Registro:** `user_earned_awards`.
    *   **Evento Post-Adjudicación:** `UserEarnedNonMonetaryAwardEvent`.
    *   **Estado:** ✅ Completamente implementado y funcional.
    *   **Pendientes:** ✅ Resueltos - Sistema completamente funcional.

## 3. Guía de Desarrollo para Futuros Bonos o Modificaciones

### 3.1. Flujo General de Implementación de un Nuevo Bono

1.  **Definición del Bono:**
    *   Nombre, descripción.
    *   Tipo de recompensa (monetario, productos, especie).
    *   `trigger_event`.
    *   `calculation_type`.
    *   Parámetros de configuración.
2.  **Modelo de Datos:**
    *   **`BonusType`:** Entrada en `BonusTypeSeeder.php`.
    *   **Configuración Específica:**
        *   Campos condicionales en `BonusTypeResource.php` (para `configuration_details`) si son pocos parámetros únicos.
        *   Tabla dedicada de tiers/configuración + Modelo + Recurso Filament + Seeder, si son múltiples niveles o parámetros complejos.
    *   **Tablas de Seguimiento:** Si se necesita registrar progreso o adjudicaciones.
3.  **Lógica de Negocio (`App\Services\BonusService`):**
    *   **`checkBonusConditions()`:** Lógica de elegibilidad.
    *   **`calculateBonusAmount()` / `determineNonMonetaryAwardDetails()` / `calculateProductsToAward()`:** Lógica de cálculo/determinación del premio.
    *   **`processEvent()`:** Orquestación del pago o registro del premio.
    *   **`generateTransactionDescription()`:** Para bonos monetarios.
4.  **Eventos y Listeners:** Para `trigger_event` y eventos post-adjudicación.
5.  **Comandos Programados (Cron Jobs):** Si el bono es periódico.
6.  **Pruebas.**

### 3.2. Consideraciones Clave

*   **Claridad del `eventPayload`**.
*   **Atomicidad** (transacciones DB).
*   **Idempotencia**.
*   **Logging detallado**.
*   **Configurabilidad vía Filament**.
*   **Suscripción Activa (`userHasActiveSubscription`)**.
*   **Jerarquía de Rangos (`Rank::rank_order` o `rank_hierarchy_for_comparison`)**.

## 4. TODOs Generales del Sistema de Bonos

*   [ ] Implementar `BonusService::userHasActiveSubscription()`.
*   [ ] Implementar `BonusService::getUserPeriodCommissionableVolume()`.
*   [ ] Desarrollar el proceso completo de "Cierre de Periodo" (comando Artisan, cálculo de rangos/volúmenes, evento `PeriodClosedEvent`).
*   [ ] Asegurar que `WalletTransaction.period_id` se popule correctamente.
*   [ ] Implementar interfaz de canje para `user_loyalty_product_ledger`.
*   [ ] Implementar listeners para `UserEarnedNonMonetaryAwardEvent`.
*   [ ] Desarrollar UI para mostrar premios no monetarios ganados.
*   [ ] Revisar y asegurar la correcta implementación de la lógica de "primera activación" y el uso de `first_activation_date`.
*   [ ] Refinar todas las plantillas de `wallet_transaction_description_template`.
*   [ ] Pruebas exhaustivas de todos los flujos de bonos.
*   [ ] Implementar la lógica pendiente para el **Bono Movilización** en `BonusService`.
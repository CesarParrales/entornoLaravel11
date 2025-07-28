# Memoria Técnica: Módulo de Billetera de Socio y Configuración de Bonos

Fecha de Creación: {{env.CURRENT_DATE}}

Este documento resume la implementación de la infraestructura para la Billetera de Socio y el sistema de configuración de Tipos de Bono en la plataforma MLM E-commerce.

---

## 1. Módulo de Billetera de Socio

**Objetivo:** Proveer un sistema centralizado para que cada socio (específicamente aquellos con el rol "Socio Multinivel") pueda acumular y gestionar fondos provenientes de bonos, comisiones u otras fuentes.

### 1.1. Modelos y Migraciones

**a) Modelo `Wallet` ([`app/Models/Wallet.php`](app/Models/Wallet.php:1))**
   *   Representa la billetera individual de un socio.
   *   **Campos Principales:**
        *   `user_id` (FK a `users`, unique): El socio dueño de la billetera.
        *   `balance` (decimal): Saldo actual.
        *   `currency_code` (string, default 'USD'): Moneda.
        *   `status` (string, default 'active'): Estados como 'active', 'suspended', 'frozen', 'closed'.
        *   `last_transaction_at` (timestamp, nullable): Fecha de la última transacción.
        *   `notes` (text, nullable): Notas administrativas.
   *   **Migración:** [`database/migrations/{{env.TIMEDATE}}_create_wallets_table.php`](database/migrations/{{env.TIMEDATE}}_create_wallets_table.php) (Reemplazar {{env.TIMEDATE}} con el timestamp real de la migración, ej: 2025_05_19_184501)

**b) Modelo `WalletTransaction` ([`app/Models/WalletTransaction.php`](app/Models/WalletTransaction.php:1))**
   *   Registra cada movimiento (crédito/débito) en una billetera.
   *   **Campos Principales:**
        *   `wallet_id` (FK a `wallets`): Billetera a la que pertenece.
        *   `user_id` (FK a `users`, nullable): Usuario (denormalizado).
        *   `transaction_uuid` (uuid, unique): ID único de la transacción.
        *   `type` (string): Tipo de transacción (ej: 'credit', 'debit', 'bonus_payout').
        *   `amount` (decimal): Monto (siempre positivo).
        *   `balance_before_transaction` (decimal): Saldo antes.
        *   `balance_after_transaction` (decimal): Saldo después.
        *   `currency_code` (string, default 'USD').
        *   `description` (string): Descripción legible.
        *   `sourceable_id` (nullable, integer), `sourceable_type` (nullable, string): Para relación polimórfica con el origen (ej. un bono, un pedido).
        *   `metadata` (json, nullable): Datos adicionales.
        *   `status` (string, default 'completed'): Estado de la transacción.
        *   `processed_at` (timestamp, nullable).
   *   **Migración:** [`database/migrations/{{env.TIMEDATE}}_create_wallet_transactions_table.php`](database/migrations/{{env.TIMEDATE}}_create_wallet_transactions_table.php) (Reemplazar {{env.TIMEDATE}} con el timestamp real, ej: 2025_05_19_184610)

### 1.2. Servicio `WalletService` ([`app/Services/WalletService.php`](app/Services/WalletService.php:1))

*   Encapsula la lógica de negocio para las operaciones de billetera.
*   **Métodos Clave:**
    *   `findUserWallet(User $user): ?Wallet`: Busca la billetera de un usuario.
    *   `ensureWalletExistsForSocio(User $user): ?Wallet`: Crea una billetera para un usuario si tiene el rol "Socio Multinivel" y no existe una.
    *   `credit(User $user, float $amount, string $type, string $description, $sourceable = null, ?array $metadata = null): ?WalletTransaction`: Acredita fondos a la billetera del socio. Crea una `WalletTransaction` y actualiza el `balance` de la `Wallet` atómicamente.
    *   `debit(User $user, float $amount, string $type, string $description, $sourceable = null, ?array $metadata = null): ?WalletTransaction`: Debita fondos, similar al crédito pero resta del saldo y verifica fondos suficientes.

### 1.3. Integración con Registro de Usuarios

*   El componente [`app/Livewire/UserRegistrationForm.php`](app/Livewire/UserRegistrationForm.php:1) fue modificado para llamar a `WalletService->ensureWalletExistsForSocio()` después de que un nuevo usuario es creado y se le asigna el rol "Socio Multinivel".

### 1.4. Recursos de Filament

*   **`WalletResource` ([`app/Filament/Resources/WalletResource.php`](app/Filament/Resources/WalletResource.php:1)):**
    *   Para la gestión administrativa de las billeteras de los socios.
    *   Agrupado bajo "Finanzas".
    *   Permite ver y editar detalles de la billetera (con precaución en la edición directa de saldos).
    *   Incluye un `TransactionsRelationManager` para listar las transacciones asociadas a cada billetera.
*   **`WalletTransactionResource` ([`app/Filament/Resources/WalletTransactionResource.php`](app/Filament/Resources/WalletTransactionResource.php:1)):**
    *   Para una vista global y auditable de todas las transacciones de billetera.
    *   Agrupado bajo "Finanzas".
    *   Principalmente para visualización; la creación de transacciones se maneja vía `WalletService`.

---

## 2. Módulo de Configuración de Tipos de Bono

**Objetivo:** Permitir la definición y configuración de diferentes tipos de bonos que pueden ser otorgados a los socios.

### 2.1. Modelo `BonusType` ([`app/Models/BonusType.php`](app/Models/BonusType.php:1))

*   Define las características y reglas de un tipo de bono.
*   **Campos Principales:**
    *   `name` (string): Nombre del bono.
    *   `description` (text, nullable).
    *   `slug` (string, unique): Identificador programático.
    *   `is_active` (boolean): Si el tipo de bono está activo.
    *   `calculation_type` (string): Método de cálculo (ej: 'fixed_amount', 'percentage_of_purchase', 'points_to_currency').
    *   `amount_fixed` (decimal, nullable): Para bonos de monto fijo.
    *   `percentage_value` (decimal, nullable): Para bonos basados en porcentaje.
    *   `points_to_currency_conversion_factor` (decimal, nullable): Para bonos basados en conversión de puntos a moneda.
    *   `trigger_event` (string): Evento del sistema que dispara el cálculo del bono (ej: 'order_paid_by_self').
    *   `configuration_details` (json, nullable): Para reglas específicas del bono (ej: ID de producto requerido, rango mínimo del beneficiario, base para porcentaje, etc.).
    *   `wallet_transaction_description_template` (string, nullable): Plantilla para la descripción de la transacción en la billetera.
*   **Migración:** [`database/migrations/{{env.TIMEDATE}}_create_bonus_types_table.php`](database/migrations/{{env.TIMEDATE}}_create_bonus_types_table.php) (Reemplazar {{env.TIMEDATE}} con el timestamp real, ej: 2025_05_19_202943)

### 2.2. Recurso de Filament `BonusTypeResource` ([`app/Filament/Resources/BonusTypeResource.php`](app/Filament/Resources/BonusTypeResource.php:1))

*   Permite a los administradores crear, ver, editar y eliminar tipos de bono.
*   Agrupado bajo "Configuraciones MLM".
*   El formulario incluye campos para todos los atributos del modelo, con lógica condicional para mostrar los campos de valor de cálculo relevantes según el `calculation_type` seleccionado.

### 2.3. Seeder `BonusTypeSeeder` ([`database/seeders/BonusTypeSeeder.php`](database/seeders/BonusTypeSeeder.php:1))

*   Creado para poblar la tabla `bonus_types` con configuraciones iniciales.
*   **Configuración Inicial Implementada:**
    *   **"Bono Reconsumo":**
        *   `slug`: 'bono-reconsumo-puntos'
        *   `calculation_type`: 'points_to_currency'
        *   `points_to_currency_conversion_factor`: 1.00 (1 punto = $1)
        *   `trigger_event`: 'order_paid_by_self'
        *   `wallet_transaction_description_template`: "Bono Reconsumo por {ORDER_POINTS} puntos de pedido #{ORDER_ID}"

### 2.4. Lógica de Cálculo y Pago de Bonos (Pendiente)

*   La lógica que efectivamente:
    1.  Escucha los `trigger_event`.
    2.  Consulta los `BonusType` activos.
    3.  Verifica las condiciones en `configuration_details`.
    4.  Calcula el monto del bono.
    5.  Llama a `WalletService->credit()` para pagar el bono.
    ... **aún no ha sido implementada.** Esto requerirá un servicio dedicado (ej. `BonusService`) y/o Listeners de Eventos.

---

**Estado Actual:**
La infraestructura de datos y administrativa para las Billeteras de Socio y la Configuración de Tipos de Bono está implementada. El sistema puede crear billeteras para nuevos socios y los administradores pueden configurar diferentes tipos de bonos. El siguiente paso crucial es desarrollar la lógica de negocio que procese los eventos, calcule los bonos según su configuración y los acredite a las billeteras correspondientes.
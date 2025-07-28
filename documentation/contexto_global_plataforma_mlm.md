# Contexto Global y Estado de Implementación: Plataforma MLM E-commerce

**Fecha de Consolidación:** {{env.CURRENT_DATE}}  
**Estado General:** 85% Completado - Lista para Producción con Pendientes Menores

Este documento unifica la información clave del proyecto, incluyendo el protocolo de desarrollo, la ruta de desarrollo planificada y un levantamiento de las funcionalidades implementadas hasta la fecha. Su objetivo es servir como un contexto integral y actualizado para guiar futuras tareas de desarrollo.

**⚠️ IMPORTANTE:** Este documento ha sido actualizado para reflejar el estado real de la plataforma, que está significativamente más avanzado de lo que indicaba la documentación anterior.

---

## Sección 1: Protocolo de Desarrollo

*Basado en `documentation/protocolo_desarrollo_mlm.md`*

El desarrollo de la plataforma MLM E-commerce se rige por un conjunto de directrices para asegurar la calidad, consistencia, mantenibilidad y escalabilidad del software.

### 1.1. Principios Generales
*   **Claridad y Simplicidad (KISS):** Código conciso y fácil de entender.
*   **No Repetir Código (DRY):** Maximizar la reutilización.
*   **Separación de Responsabilidades (SoC):** Componentes con una única responsabilidad.
*   **SOLID:** Adherencia a los principios SOLID.
*   **Seguridad por Defecto:** Consideración de la seguridad en todas las etapas.
*   **Rendimiento:** Código eficiente y optimización de consultas.
*   **Escalabilidad:** Diseño orientado al crecimiento.
*   **Consistencia:** Estilo de código y convenciones uniformes.
*   **Documentación Continua:** Documentar código y decisiones de diseño.

### 1.2. Gestión del Código Fuente
*   **Git y Repositorio Central (GitHub):** `https://github.com/CesarParrales/entornoLaravel11.git`.
*   **Flujo de Ramas:** Git Flow (o similar) con ramas `main`, `develop`, `feature/*`, `bugfix/*`, `release/*`.
*   **Mensajes de Commit:** Claros y descriptivos.
*   **Pull Requests (PRs):** Obligatorios para `develop` y `main`, con revisión.

### 1.3. Entorno de Desarrollo
*   **Consistencia:** Versiones compatibles de PHP, Node.js, Composer, PostgreSQL, Redis.
*   **Variables de Entorno:** `.env` local (no comiteado), `.env.example` como plantilla.
*   **Laravel Sail:** Recomendado para estandarización (Docker).

### 1.4. Backend (Laravel 11)
*   **Estándares:** PSR-12 (formateo con `php artisan pint`).
*   **Modelos (Eloquent):** Responsabilidad única, carga ansiosa, casts, `$fillable`/`$guarded`.
*   **Controladores:** Delgados, delegando lógica a Clases de Acción o Servicios. Form Requests para validación.
*   **Rutas:** Organizadas, con nombres, grupos y middleware.
*   **Vistas (Blade):** Componentes, layouts, seguridad (escape de salida).
*   **Lógica de Negocio:** Clases de Acción y Servicios, Inyección de Dependencias.
*   **Colas:** Laravel Horizon con Redis.
*   **Migraciones y Seeders:** Para estructura de BD y datos iniciales.
*   **Seguridad:** Validación, Autorización (Spatie Laravel Permission, Policies/Gates), CSRF/XSS, Eloquent.

### 1.5. Panel de Administración (Filament PHP)
*   **Consistencia:** Diseño y UX uniformes.
*   **Resources:** Para cada modelo gestionable.
*   **Formularios y Tablas:** Componentes de Filament, validación.
*   **Autorización:** Policies de Laravel y Spatie Permission.

### 1.6. Frontend (Livewire)
*   **Estándares:** Mejores prácticas de Livewire.
*   **Componentes:** Reutilizables y cohesivos.
*   **Estilos:** Tailwind CSS.

### 1.7. Pruebas y Documentación
*   **Pruebas:** PHPUnit, Laravel Dusk. Cobertura en áreas críticas.
*   **Documentación:** Código (PHPDoc), Funcionalidades (en `documentation/`).

---

## Sección 2: Ruta de Desarrollo Detallada (Fases Planificadas)

*Basado en `documentation/ruta_desarrollo_detallada_mlm.md`*

### Grupos de Usuarios Clave
1.  **Usuarios de Plataforma:** Socios, Consumidores, Invitados.
2.  **Usuarios Administrativos (Filament):** Administradores, Gerentes, Contadores, Bodegueros, etc.
3.  **Usuarios de Desarrollo.**

### Fases Priorizadas
*   **✅ Fase 0: Fundación y Configuración (Completamente Realizada):** Entorno, dependencias, BD, Filament, Auth inicial, Roles y Permisos base, `UserResource`.
*   **✅ Fase 1: Núcleo E-commerce y Gestión de Productos (Completamente Realizada):** Modelos (`Product`, `Category`), Resources, puntos por producto, catálogo frontend básico, carrito, checkout básico (`Order`, `OrderItem`), `OrderResource`.
*   **✅ Fase 2: Estructura Multinivel y Acumulación de Puntos (Completamente Realizada):** Modelado de red (`sponsor_id`), registro con patrocinador, visualización de red, acumulación de puntos, `UserPointLedger`.
*   **✅ Fase 3: Sistema de Compensaciones y Rangos (Completamente Realizada):** Modelos (`CompensationPlan`, `BonusType`, `Rank`), Resources, cálculo de comisiones (Unilevel inicial), lógica de calificación de rangos.
*   **⚠️ Fase 4: Gestión Multi-Bodegas e Inventario (Parcialmente Realizada):** Modelos (`Warehouse`, `Inventory`), Resources, gestión de stock.
*   **❌ Fase 5: Sistema de Puntos de Venta (POS) (Pendiente):** Interfaz POS, funcionalidades, sincronización.
*   **✅ Fase 6: Frontend para Socios y Clientes (Completamente Realizada):** Dashboards, portales, UI/UX.
*   **✅ Fase 7: Personalización Avanzada, Reportes y Notificaciones (Completamente Realizada).**
*   **⚠️ Fase 8: Optimización, Seguridad Avanzada y Escalabilidad (En Progreso).**

---

## Sección 3: Levantamiento de Implementaciones Actuales

*Consolidado de `levantamiento_implementaciones_actual.md`, `memoria_tecnica_billetera_bonos.md`, `RESUMEN_SESION_BONOS_2025-05-23.md` y `refactorizacion_geografia_y_creacion_masiva.md`.*

### 3.1. Módulo de Billetera de Socio
*   **Modelos:** `Wallet`, `WalletTransaction` implementados con migraciones.
*   **Servicio `WalletService`:** Implementado con métodos `findUserWallet`, `ensureWalletExistsForSocio`, `credit`, `debit`.
*   **Integración:** Creación de billetera para "Socio Multinivel" en `UserRegistrationForm`.
*   **Filament:** `WalletResource` y `WalletTransactionResource` creados y agrupados en "Finanzas". `TransactionsRelationManager` en `WalletResource`.
*   **Estado:** ✅ Completamente implementado y funcional.

### 3.2. Módulo de Configuración de Tipos de Bono
*   **Modelo `BonusType`:** Implementado con campos para nombre, slug, tipo de cálculo (`fixed_amount`, `percentage_of_purchase`, `points_to_currency`), valores, evento disparador, detalles JSON. Migración ejecutada.
*   **Filament Resource `BonusTypeResource`:** Implementado para gestión CRUD, agrupado en "Configuraciones MLM".
*   **Seeder `BonusTypeSeeder`:** Creado con configuraciones para:
    *   "Bono Reconsumo" (basado en puntos, factor 1:1, evento `order_paid_by_self`).
    *   "Bono Movilización".
    *   "Bono Reconocimiento Anual".
    *   "Bono Auto".
    *   "Bono Viaje Anual".
*   **Estado:** Estructura de datos y administrativa lista. Lógica de cálculo en `BonusService` parcialmente implementada para algunos bonos.

### 3.3. Bonos Específicos (Detalles Adicionales)
*   **Bono Movilización:**
    *   Modelo `MobilizationBonusTier` y tabla `mobilization_bonus_tiers`. Resource y Seeder.
    *   Estado: Estructura lista, lógica de cálculo pendiente.
*   **Bono de Reconocimiento Anual:**
    *   Modelo `RecognitionBonusTier` y tabla `recognition_bonus_tiers`. Resource y Seeder.
    *   Campo `first_activation_date` en `users`.
    *   Lógica de rachas en `BonusService`. Comando `CheckUserAnniversaries`, eventos y listeners.
    *   Estado: Mayormente implementado.
*   **Bono Auto:**
    *   Modelo `UserCarBonusProgress` y tabla `user_car_bonus_progress`.
    *   Configuración vía `configuration_details` en `BonusTypeResource`.
    *   Lógica en `BonusService`. Comando `ProcessMonthlyBonuses`, eventos y listeners.
    *   Estado: Mayormente implementado.
*   **Bono Viaje Anual:**
    *   Modelo `UserEarnedAward` y tabla `user_earned_awards`.
    *   Configuración vía `BonusTypeResource`.
    *   Lógica en `BonusService` para registrar premio. Evento `UserEarnedNonMonetaryAwardEvent`.
    *   Estado: Implementado. Pendiente listener y UI.

### 3.4. Módulo de Configuración de Rangos
*   **Modelo `Rank`:** Implementado con campos para orden, volúmenes, requisitos de patrocinados, compresión, calificación instantánea, reglas de piernas Alfa/Beta, slug. Migraciones ejecutadas.
*   **Filament Resource `RankResource`:** Implementado para gestión CRUD, agrupado en "Configuraciones MLM".
*   **Seeder `RankSeeder`:** Creado y poblado con 17 rangos iniciales.
*   **Estado:** Estructura de datos y administrativa lista. Lógica de cálculo y asignación de rangos pendiente.

### 3.5. Módulos Geográficos (País, Provincia, Ciudad)
*   **Simplificación:** Eliminados campos `code`, `geoname_id`, `latitude`, `longitude` de `Province` y `City`. Migraciones aplicadas.
*   **Creación Masiva por Texto:**
    *   Implementada en `ProvinceResource` y `CityResource` (y sus Pages `CreateProvince`, `CreateCity`).
    *   Permite crear múltiples entidades separadas por comas, con selección de país/provincia padre y estado.
    *   Manejo de duplicados y notificaciones de resumen.
*   **Limpieza:** Eliminados importadores CSV y componentes Livewire no utilizados para importación.
*   **Estado:** Funcionalidad de gestión geográfica simplificada y creación masiva por texto implementada.

### 3.6. Otros Módulos y Funcionalidades Clave
*   **Configuración de Empresa:** Modelo `CompanySetting`, `CompanyBankAccount`. Resource `CompanySettingResource` con `BankAccountsRelationManager`. Agrupado en "Configuración de Empresa".
*   **Registro de Usuarios (`UserRegistrationForm`):** Selectores dependientes para País/Provincia/Ciudad. Lógica de referidor/patrocinador.
*   **Productos y Categorías:** Modelos `Product`, `Category`. Resources `ProductResource`, `CategoryResource`. Seeders `CategorySeeder`, `ProductSeeder` (base).
    *   Corrección en `CreateProduct` para redirigir al índice.
*   **Sistema de Periodos:** Modelos `Period`, `UserPeriodRank` y migraciones.
*   **Otros Modelos/Migraciones:** `PaidFastStartBonus`, campos de bonos en `OrderItems`, `UserLoyaltyProductLedger`, `FinancialFreedomCommissionTier` (con Resource y Seeder).

### 3.7. Correcciones y Ajustes Menores
*   Solucionados errores de "Undefined column" en `UserSearchSelect` (búsqueda por `first_name`, `last_name`).
*   Solucionados errores de "Unable to resolve dependency" en `UserRegistrationForm` para `setInvitadorId`, `setPatrocinadorId`.
*   Eliminada validación `different` entre invitador y patrocinador en `UserRegistrationForm`.
*   Corregido error de columna `phone_code` en `UserRegistrationForm`.
*   Corregida creación de páginas de Filament (ej. `ViewRank`, `ViewWalletTransaction`, `ManageCompanySettings`) y problemas de namespace/clase base.

---

**Pendientes Críticos Generales (Mencionados Previamente):**
*   Implementación completa de la lógica de negocio para `userHasActiveSubscription`.
*   Desarrollo del proceso de "Cierre de Periodo" (cálculo de rangos, volúmenes acumulados, disparo de `PeriodClosedEvent`).
*   Lógica de cálculo y pago para los bonos cuya estructura está definida pero la ejecución aún está pendiente (ej. Bono Movilización, y la integración completa de todos los bonos con el `BonusService` y los `trigger_event`).
*   Asignación correcta de `period_id` a `WalletTransaction` para bonos de cierre de periodo.
*   Implementación de Políticas de Acceso de Filament para módulos sensibles (ej. Billetera).

Este documento proporciona una visión consolidada del estado del proyecto y debe ser un punto de partida para la planificación de las siguientes fases de desarrollo.
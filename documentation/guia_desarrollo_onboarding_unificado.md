# Guía de Desarrollo: Flujo de Onboarding Unificado

**Fecha de Creación:** {{env.CURRENT_DATE}}

Este documento detalla la implementación del nuevo flujo de registro y activación de socios "todo en uno", donde el formulario de registro, la selección de productos (incluyendo bundles de activación opcionales) y el carrito de compras se presentan en una única página interactiva.

---

## 1. Visión General del Flujo

1.  **Selección de País (Modal Inicial):** El usuario selecciona su país de una lista.
2.  **Selección de Paquete Promocional (Modal Opcional):**
    *   Aparece tras seleccionar el país.
    *   El usuario puede elegir un "bundle de activación" predefinido que se añade al carrito.
    *   Puede omitir este paso y seleccionar productos individualmente.
3.  **Página Principal de Registro (Todo en Uno):**
    *   **Formulario de Datos del Socio:** Campos para información personal, de contacto y credenciales.
    *   **Catálogo de Productos:** Permite al usuario navegar y añadir productos al carrito.
    *   **Carrito de Compras:** Muestra ítems, cantidades, precios, puntos totales, total descuento (PVP-PVS), IVA, total a pagar, y un selector de método de entrega.
4.  **Validación y Envío:**
    *   Se valida que el carrito cumpla con el mínimo de puntos (ej. 20 puntos para "Registrado") si no se seleccionó un bundle de activación que ya lo haga.
    *   Aceptación de Términos y Condiciones.
    *   Botón "Registrarse".
5.  **Proceso de Creación de Usuario y Pedido (Pre-Activación):**
    *   Al enviar, se crea el `User` con un estado inicial como `pending_first_payment`. **En este punto crucial, el usuario está registrado en el sistema pero aún no es considerado un socio activo para el plan MLM y, por lo tanto, no se le asigna ningún rango MLM.**
    *   Se crea el `Order` asociado al nuevo usuario, reflejando la compra inicial necesaria para la activación.
6.  **Proceso de Pago y Activación (Post-Confirmación de Pago):**
    *   **Pago Online (Tarjeta):** Redirección a pasarela. Si la transacción es exitosa:
        *   El `Order` se marca como pagado.
        *   **Activación del Usuario:** Se cambia el estado del `User` a `active`.
        *   **Asignación de Rango Inicial:** Se calcula y asigna el primer rango MLM al usuario basado en los puntos de esta compra.
        *   Se crea la billetera del socio.
        *   Admin carga Nro. de factura posteriormente (esto no afecta la activación).
    *   **Pago Offline (Otros métodos):** Pedido queda con estado pendiente hasta que un administrador confirme la recepción del pago.
        *   Admin confirma el pago en el backend.
        *   **Activación del Usuario:** El `Order` se marca como pagado, se cambia el estado del `User` a `active`.
        *   **Asignación de Rango Inicial:** Se calcula y asigna el primer rango MLM.
        *   Se crea la billetera del socio.
7.  **Notificaciones por Email:**
    *   Email de bienvenida al registrarse (indicando estado `pending_first_payment` y próximos pasos).
    *   Email de confirmación de pedido realizado.
    *   Email de confirmación de pago y activación de cuenta (este email debe incluir el rango MLM obtenido).

---

## 2. Fases de Desarrollo Propuestas

### Fase 1: Preparación y Componente Livewire Principal

**Objetivo:** Crear la estructura de la página de registro unificada y la lógica inicial de selección de país y bundles.

1.  **Ajustes al Modelo `Product` (si es necesario):**
    - [x] Considerar añadir `is_registration_bundle` (boolean, default `false`).
    - [x] Considerar `registration_bundle_price` (decimal, nullable) si el precio es especial para el registro.
    - [x] **Acción:** Revisar [`app/Models/Product.php`](app/Models/Product.php:1) y crear/aplicar migración si se añaden campos.

2.  **Componente Livewire `EnhancedUserRegistrationPage` (Nuevo o Refactorización de `UserRegistrationForm`):**
    - [x] **Archivo:** [`app/Livewire/EnhancedUserRegistrationPage.php`](app/Livewire/EnhancedUserRegistrationPage.php:1) (o renombrar/modificar `UserRegistrationForm.php`).
    - [x] **Propiedades del Estado:**
        - [x] `selectedCountryId` (nullable, int)
        - [x] `availableActivationBundles` (Collection)
        - [x] `selectedActivationBundleId` (nullable, int)
        - [x] `showCountryModal` (boolean, default `true`)
        - [x] `showActivationBundleModal` (boolean, default `false`)
        - [x] Propiedades existentes del formulario de usuario (nombre, email, etc.).
        - [x] Propiedades para el carrito (ítems, totales, etc., integrando `CartService`).
        - [x] Propiedad para el método de entrega seleccionado.
    - [x] **Método `mount()`:**
        - [x] Cargar lista de países para el modal.
        - [x] No mostrar modal de bundles aún (se cargan después de seleccionar país).
    - [x] **Método `selectCountry(int $countryId)`:**
        - [x] Establecer `selectedCountryId`.
        - [x] `$this->showCountryModal = false;`
        - [x] Cargar `availableActivationBundles` (filtrados por país si es necesario).
        - [x] Si hay bundles, `$this->showActivationBundleModal = true;` sino, proceder a mostrar la página principal.
    - [x] **Método `selectActivationBundle(int $productId)`:**
        - [x] Obtener el producto bundle.
        - [x] Añadirlo al carrito (usando `CartService`).
        - [x] `$this->selectedActivationBundleId = $productId;`
        - [x] `$this->showActivationBundleModal = false;`
    - [x] **Método `skipActivationBundle()`:**
        - [x] `$this->showActivationBundleModal = false;`
    - [x] **Vista Blade (`enhanced-user-registration-page.blade.php`):**
        - [x] Modal para selección de país (controlado por `showCountryModal`).
        - [x] Modal para selección de bundle promocional (controlado por `showActivationBundleModal`).
        - [x] Layout principal (ej. grid de 2 o 3 columnas) visible cuando los modales están cerrados:
            - [x] Columna 1: Formulario de datos del socio (campos de `UserRegistrationForm` actualizados para selectores dependientes de País/Provincia/Ciudad).
            - [x] Columna 2: Catálogo de productos (puede ser un componente Livewire anidado o lógica dentro de esta página).
            - [x] Columna 3 (o parte de Columna 2): Carrito de compras.
                - [x] Mostrar ítems, cantidad, precio unitario (PVS para socio), subtotal por ítem.
                - [x] **Nuevos Totales a Mostrar:**
                    - [x] `Puntos Totales del Carrito`: Suma de `points_value` de los ítems.
                    - [x] `Total Descuento`: `SUM(PVP de ítems) - SUM(PVS de ítems)`.
                    - [x] `IVA`: Calculado sobre el subtotal PVS.
                    - [x] `Total a Pagar`: Subtotal PVS + IVA + Costo de Envío (si aplica).
                - [x] **Selector de Método de Entrega:**
                    - [x] Opciones: "Retiro en Tienda", "Courier".
                    - [x] Propiedad Livewire: `delivery_method`.
    - [x] **Lógica del Carrito (Integración con `CartService`):**
        - [x] Métodos para añadir/actualizar/eliminar ítems del carrito.
        - [x] Recalcular totales del carrito (incluyendo puntos y descuento) con cada cambio.
        - [ ] Validación en tiempo real (o al intentar registrarse) del mínimo de 20 puntos si no se seleccionó un bundle de activación que cumpla. (Se implementará en Fase 2 con `attemptRegistration`)
    - [x] **Términos y Condiciones y Botón de Registro.**

### Fase 2: Lógica de Registro, Creación de Pedido y Flujo de Pago

**Objetivo:** Implementar la acción principal del botón "Registrarse", incluyendo la creación de entidades y el manejo inicial de pagos.

1.  **Método `registerAndPlaceOrder()` en `EnhancedUserRegistrationPage`:**
    - [x] **Validaciones:**
        - [x] Datos del formulario del usuario.
        - [x] Carrito: Mínimo 20 puntos (si aplica).
        - [x] Aceptación de términos.
        - [x] Método de entrega seleccionado.
        - [x] Método de pago seleccionado.
    - [x] **Creación de Entidades (dentro de una transacción de BD):**
        - [x] Crear `User` con estado inicial (`pending_first_payment`).
        - [x] Asignar rol "Socio Multinivel" o "Consumidor Registrado".
        - [x] Crear `Order` asociado al nuevo `User`, con los ítems del carrito.
            - [x] Almacenar `delivery_method` y costo de envío (si aplica) en el pedido.
            - [x] Almacenar Puntos Totales del pedido (calculado), Total Descuento, IVA, Total Pagado.
            - [x] Estado inicial del pedido (`pending_payment`).
    - [x] **Manejo de Pago:**
        - [x] Añadir selector de método de pago en la vista.
            - [x] "Tarjeta de Crédito/Débito (Online)"
            - [x] "Tarjeta de Crédito/Débito (Offline - en POS)"
            - [x] "Depósito Bancario"
            - [x] "Transferencia Bancaria"
            - [x] "Efectivo (en Punto de Venta)"
        - [x] Propiedad Livewire: `payment_method_selected`.
        - [ ] **Si es Online:** (Placeholder para redirección, lógica completa en Fase 4)
            - [ ] Redirigir al usuario a la URL de la pasarela de pago con los datos del pedido.
            - [ ] (La lógica de callback/webhook de la pasarela se implementará en Fase 4).
        - [x] **Si es Offline:**
            - [x] Mostrar mensaje de "Pedido recibido. Pendiente de confirmación de pago. Instrucciones para el pago: ...".
            - [x] Enviar email de registro (`WelcomePendingPaymentMail`) y email de pedido realizado con instrucciones (`OrderPlacedOfflinePaymentMail`).
    - [x] Limpiar carrito de la sesión.
    - [x] Resetear estado del formulario.

### Fase 3: Activación de Cuenta, Asignación de Rango y Notificaciones

**Objetivo:** Implementar la lógica que activa la cuenta del socio y le asigna su primer rango una vez que el pago de su primer pedido es confirmado.

1.  **Evento `OrderPaymentConfirmed` (Existente o a Refinar):**
    - [x] Este evento debe ser despachado cuando un pago es confirmado.
    - [x] Debe llevar el `Order $order` como payload. (Asumido como existente y correcto).

2.  **Listener `ActivateUserAndAssignInitialRankListener` (Nuevo):**
    - [x] Escuchar el evento `OrderPaymentConfirmed`.
    - [x] **Lógica Principal:**
        - [x] Obtener el `User` del `Order`.
        - [x] Verificar si es el *primer pedido confirmado* del usuario y si su `User->status` es `pending_first_payment`.
        - [x] Si ambas condiciones son verdaderas:
            - [x] Cambiar `User->status` a `active`.
            - [x] Establecer `User->activated_at = now()`.
            - [x] Establecer `User->first_activation_date = now()` (si no existe).
            - [x] **Asignar Rango MLM Inicial:**
                - [x] Obtener el total de puntos del `Order`.
                - [x] Consultar la tabla `ranks`.
                - [x] Encontrar el rango calificable.
                - [x] Actualizar el `rank_id` del usuario.
                - [x] Crear un registro en `user_period_ranks` (lógica básica implementada, pendiente de modelo `Period` completo).
            - [x] Llamar a `WalletService->ensureWalletExistsForSocio($user)`.
            - [x] Enviar email de "Pago Confirmado y Cuenta Activada" (`AccountActivatedMail`).
            - [ ] (Futuro) Disparar eventos para bonos de primera compra o activación.

3.  **Campo `rank_id` en Modelo `User`:**
    - [x] Añadir `rank_id` (nullable, FK a `ranks`) a la tabla `users` mediante una nueva migración.
    - [x] Actualizar el modelo `User` con la relación `rank()` y añadir `rank_id` a `$fillable`.

### Fase 4: Mejoras en Panel de Administración y Pasarela de Pago

1.  **`OrderResource` en Filament:**
    - [x] **Acción "Confirmar Pago Offline":**
        - [x] Permitir al admin seleccionar un pedido con estado "pending_payment".
        - [x] Mostrar un modal/formulario para ingresar detalles del pago (campos básicos implementados).
            - [ ] **Tarjeta Offline:** (Campos específicos pendientes de definición detallada)
            - [x] **Depósito/Transferencia:** (Campos básicos implementados)
            - [ ] **Efectivo POS:** (Campos específicos pendientes de definición detallada)
        - [x] Al confirmar, cambiar estado del `Order` a "processing" y **despachar `OrderPaymentConfirmed` event**.
        - [x] Guardar los detalles del pago (Tarea 4.1.4 completada con campo JSON `payment_details` en `Order`).
    - [x] **Acción "Cargar Número de Factura":**
        - [x] Para pedidos pagados/procesados, permitir al admin ingresar el Nro. de factura manual (asume campo `invoice_number` en `Order`).

2.  **Integración de Pasarela de Pago Online (Desarrollo Mayor):**
    - [ ] Seleccionar la pasarela (ej. Datafast, Kushki, Paymentez para Ecuador). (Tarea Externa)
    - [x] Implementar la redirección (placeholder) desde `EnhancedUserRegistrationPage`.
    - [x] Implementar el controlador y ruta para el callback/webhook de la pasarela (estructura base creada).
        - [ ] Verificar la firma y estado de la transacción. (Pendiente - específico de pasarela)
        - [ ] Si es exitosa, actualizar estado del `Order` a "pagado" y **despachar `OrderPaymentConfirmed` event**. (Lógica base implementada, pendiente de datos de pasarela)
        - [ ] Manejar errores de pago. (Lógica base implementada, pendiente de datos de pasarela)

---

**Tarea Actual en Curso (Depuración):**
*   **Resolver fallo en el flujo de Onboarding Unificado (`/registro`):** Actualmente, el registro no crea la cuenta ni el pedido. Se está investigando la causa raíz mediante el análisis de `storage/logs/laravel.log` después de que el usuario intente un registro. La última corrección fue añadir la columna `order_number` a la tabla `orders`. Se está a la espera de que el usuario pruebe nuevamente y proporcione los logs si el problema persiste.

**Consideraciones Adicionales:**

*   **Estado del Usuario:** Definir claramente los estados del usuario (`pending_first_payment`, `active`, `inactive`, `suspended`) y cómo transicionan.
*   **Roles:** Asegurar que el rol "Socio Multinivel" se asigne correctamente.
*   **Puntos de Producto:** El campo `points_value` en el modelo `Product` es fundamental.
*   **Configuración de Marcas de Tarjeta:** Para el ingreso manual de pagos con tarjeta offline, las "marcas" (Visa, MC) podrían ser un Enum, una tabla de configuración simple, o un campo de texto libre inicialmente.
*   **Seguridad:** Todas las entradas del usuario y los procesos de pago deben ser seguros.

Esta guía proporciona una hoja de ruta detallada. Se recomienda implementar por fases y probar exhaustivamente cada componente.
# Ruta de Desarrollo: Mini-Proyecto de Onboarding de Usuarios

## Introducción

Este documento describe la ruta de desarrollo propuesta para el "mini-proyecto de onboarding de usuarios". El objetivo es implementar un flujo de registro robusto que incluya la diferenciación entre socios y consumidores, la vinculación a la red multinivel, la gestión de precios específica por tipo de usuario (PVP/PVS), el manejo de impuestos (IVA), y una compra inicial obligatoria para la activación de la cuenta.

Se seguirá un enfoque iterativo para gestionar la complejidad y entregar valor funcional de manera progresiva.

## Protocolos Generales de Desarrollo

*   **Seguir Protocolos Establecidos:** Todas las implementaciones deben adherirse al [`protocolo_desarrollo_mlm.md`](protocolo_desarrollo_mlm.md:1) y a las mejores prácticas de desarrollo de Laravel y Livewire.
*   **Comunicación Continua:** Si durante la implementación de cualquier tarea se requiere más información, aclaraciones sobre los requisitos, o surgen impedimentos, se realizarán las preguntas necesarias antes de continuar.
*   **Seguimiento de Tareas:** Marcar las casillas de verificación (`[x]`) a medida que se completan las tareas.

---

## Iteración 1: Fundación del Usuario, Red, Precios y Registro Básico

**Objetivo:** Establecer los modelos de datos fundamentales, la lógica de precios base, el primer usuario de la red (Socio 0), y un formulario de registro inicial que cree usuarios en un estado pendiente de activación.

*   **[x] Tarea 1.1: Extensión del Modelo `User` y Migraciones**
    *   [x] Definir y añadir campos de control al modelo `User`:
        *   `sponsor_id` (FK a `users`, nullable)
        *   `invitador_id` (FK a `users`, nullable)
        *   `user_type` (enum/string: 'socio', 'consumidor', o usar roles Spatie)
        *   `status` (enum/string: 'pending_initial_payment', 'active', 'inactive', 'archived' - valor inicial: 'pending_initial_payment')
        *   `country_id` (FK a `countries` - asegurar que el modelo `Country` exista y esté poblado)
        *   `dni_ruc` (string, considerar unicidad y validaciones específicas por país)
        *   `username` (string, unique, considerar estrategia de autogeneración)
        *   `activated_at` (timestamp, nullable)
        *   `archived_at` (timestamp, nullable)
        *   (Otros campos de control como `Estado` (suscripción), `Activado en` (renovación), `Desactivado en` se planificarán con el módulo de suscripciones/membresías).
    *   [x] Definir y añadir campos de perfil básicos al modelo `User` (o un modelo de perfil separado si se prefiere):
        *   [x] `avatar_path` (string, nullable)
        *   [x] `birth_date` (date, nullable)
        *   [x] `gender` (string, nullable)
        *   [x] `civil_status` (string, nullable)
    *   [x] Crear/actualizar migración para la tabla `users`. (Cubre las dos migraciones realizadas para campos de control, perfil y dirección)
    *   [x] Actualizar `$fillable` y `$casts` en el modelo `App\Models\User`. (Cubre las actualizaciones para todos los campos añadidos)

*   **[x] Tarea 1.2: Creación del "Socio 0" (Cabeza de Red)**
    *   [x] **Decisión del Cliente:** Confirmar método de creación (manual por admin vía seeder/comando, o directamente en BD). (Decidido: Seeder)
    *   [x] Implementar método de creación del Socio 0 con `user_type`='socio', `status`='active', y sin `sponsor_id`. (Seeder `SocioZeroSeeder.php` creado y añadido a `DatabaseSeeder.php`)

*   **[x] Tarea 1.3: Políticas de Precios Base e IVA**
    *   [x] **Definición PVS (Precio Venta Socio):**
        *   [x] **Decisión del Cliente:** ¿Cómo se define/calcula el PVS (ej. campo `partner_price` en `Product`, descuento % sobre PVP para rol "Socio")? (Decidido: 25% descuento sobre PVP, configurable)
        *   [x] Implementar la opción elegida en el modelo `Product` y lógica asociada. (Métodos `current_price`, `partner_price` añadidos a `Product.php`)
    *   [x] **Configuración y Cálculo de IVA:**
        *   [x] Configurar tasa de IVA (ej. en `config/custom.php` o similar). (Hecho en `config/custom_settings.php` para 15%)
        *   [x] Lógica de cálculo de IVA añadida al modelo `Product`. (Métodos `calculateVat`, `pvp_with_vat`, `pvs_with_vat` añadidos a `Product.php`)
        *   [x] Crear/actualizar helpers o métodos en `CartService` y `Order` (modelo) para calcular precios base, monto de IVA, y precio final con IVA. (`CartService` actualizado para calcular y almacenar precios detallados con IVA; modelos `Order`/`OrderItem` y sus migraciones ajustados para la estructura de datos.)
    *   [x] **Visualización de Precios con IVA:**
        *   [x] Actualizar vistas de catálogo (`show-product-catalog.blade.php`), detalle de producto (`product-detail.blade.php`), carrito (`cart-page.blade.php`), y checkout (`checkout-page.blade.php`) para mostrar precios desglosados (Subtotal sin IVA, IVA, Total con IVA) tanto para PVP como para PVS según el tipo de usuario. (Vistas de carrito, checkout, detalle de producto y catálogo actualizadas)
    *   [x] **Almacenamiento en Pedido:** Asegurar que `Order` y `OrderItem` almacenen correctamente los valores de subtotal (sin IVA), impuestos (IVA), y total. (Lógica en `CheckoutPage.php` actualizada para transferir datos detallados del `CartService` a `Order` y `OrderItem` durante la creación del pedido.)

*   **[x] Tarea 1.4: Formulario de Registro (MVP del Frontend)**
    *   [x] **Decisión del Cliente:** Confirmar lista de campos esenciales para el primer formulario de registro. Sugerencia: (Confirmado e implementado con Provincia, Ciudad, Dirección)
        *   País (selector, usando tabla `countries`)
        *   Tipo de Usuario (Socio/Consumidor - selector)
        *   Invitador (campo para código/ID; lógica de validación básica)
        *   Patrocinador (campo para código/ID; lógica de asignación/validación básica, considerar si puede ser diferente al invitador y cómo se maneja la colocación)
        *   Nombres, Apellido Paterno, Apellido Materno
        *   DNI/RUC (con validación de formato básica según país seleccionado, y unicidad)
        *   Email (único, validado)
        *   Contraseña, Confirmación de Contraseña
        *   Teléfono Móvil
        *   Fecha de Nacimiento
        *   Género
        *   Checkbox de Aceptación de Términos y Condiciones.
    *   [x] Crear componente Livewire `UserRegistrationForm`.
    *   [x] Crear ruta para la página de registro.
    *   [x] Implementar la vista del formulario con los campos definidos.

*   **[x] Tarea 1.5: Lógica de Registro (MVP del Backend)**
    *   [x] En `UserRegistrationForm`:
        *   [x] Implementar reglas de validación para todos los campos. (Hecho)
        *   [x] Lógica para generar `username` automáticamente (ej. a partir de nombres/apellidos + número). (Hecho)
        *   [x] Lógica para buscar y validar `invitador_id` y `sponsor_id`. (Implementado con componente UserSearchSelect y validación de IDs)
        *   [x] Crear el nuevo registro `User` con `status = 'pending_initial_payment'`, `user_type`/rol asignado, y los IDs de invitador/patrocinador. (Hecho)
        *   [ ] **Redirigir al usuario a la "Página de Compra de Activación"** (a crear en Iteración 2). (Pendiente para Iteración 2)
        *   [ ] Enviar email de bienvenida/confirmación de pre-registro (opcional en esta iteración). (Pendiente, opcional)

---

## Iteración 2: Compra de Activación y Activación del Usuario

**Objetivo:** Permitir a los usuarios pre-registrados realizar su compra inicial obligatoria y activar su cuenta tras la confirmación del pago de este primer pedido.

*   **[ ] Tarea 2.1: Página de Compra de Activación (Frontend)**
    *   [ ] Crear componente Livewire `ActivationPurchasePage`.
    *   [ ] Crear ruta, accesible solo para usuarios con `status = 'pending_initial_payment'`.
    *   [ ] Mostrar catálogo de productos filtrado por el país del usuario (obtenido de su perfil).
    *   [ ] Integrar el `CartService` para un carrito de compras funcional en esta página.
    *   [ ] Implementar validación de compra mínima de 20 puntos para usuarios de tipo "Socio".
    *   [ ] Formulario simplificado para dirección de envío (puede pre-rellenarse si se capturó algo en el registro, o permitir nueva entrada).
    *   [ ] Sección de método de envío simplificada (ej. "Envío Estándar a Domicilio").
    *   [ ] Botón "Confirmar Compra para Activación".

*   **[ ] Tarea 2.2: Lógica de "Confirmar Compra para Activación" (Backend)**
    *   [ ] En `ActivationPurchasePage`:
        *   Validar carrito (ej. mínimo de puntos para socios).
        *   Validar dirección de envío.
        *   Crear un nuevo `Order` asociado al `user_id` del usuario, con estado "pending_payment".
        *   Guardar los ítems del carrito como `OrderItem`.
        *   Limpiar el carrito de la "Compra de Activación".
        *   Redirigir a una página de "Pedido Recibido / Pendiente de Pago" o mostrar un mensaje claro sobre los siguientes pasos para el pago.

*   **[ ] Tarea 2.3: Activación del Usuario Post-Pago del Pedido Inicial**
    *   [ ] **Decisión del Cliente:** ¿Cómo se confirmará el pago del pedido inicial en esta fase (manual por admin, simulación)?
    *   [ ] Crear un `OrderObserver` o un listener de eventos que se active cuando un `Order` (específicamente el pedido inicial) cambie su estado a "pagado" (o un estado equivalente como "completado" si el pago es offline y confirmado).
    *   [ ] En el observer/listener:
        *   Identificar el `User` asociado al pedido.
        *   Si el `User->status` es `'pending_initial_payment'`, cambiarlo a `'active'`.
        *   Registrar la fecha actual en `User->activated_at`.
        *   (Opcional) Actualizar otros campos de control como `Estado` (suscripción) si aplica en esta fase.
        *   (Opcional) Enviar email de bienvenida y activación completa.

---

## Iteraciones Futuras (Post-MVP de Onboarding)

*   [ ] Integrar el catálogo y carrito directamente en el formulario de registro de una sola página (si se prefiere sobre el flujo de dos pasos).
*   [ ] Añadir campos de perfil adicionales (Avatar, Biografía, Ocupación, Redes Sociales) y permitir su edición por el usuario.
*   [ ] Implementar módulo de localización avanzado para selectores de Estado/Provincia/Ciudad dependientes (API o carga desde BD).
*   [ ] Desarrollar opciones de envío detalladas, incluyendo "Retiro en Tienda" (requiere lógica de Puntos de Venta - POS).
*   [ ] Implementar la lógica completa de gestión de suscripciones/membresías y el ciclo de vida del estado del socio (renovaciones, desactivación por vencimiento).
*   [ ] Implementar la lógica de archivado de usuarios por inactividad.
*   [ ] Desarrollar el Módulo de Métodos de Pago completo.
*   [ ] Continuar con el Módulo de Rangos y Bonos (Fase 3 de la ruta de desarrollo principal).
*   [ ] Implementar la visualización de la red para el socio en su dashboard.
*   [ ] Mejorar la generación automática y gestión de `username`.

---

## Dependencias Clave a Definir por el Cliente (Para Iniciar Iteración 1)

*   **Socio 0:** Método de creación.
*   **Mecánica de Patrocinio/Invitador:** Flujo exacto, cómo se identifica/valida el patrocinador/invitador, lógica de colocación si son diferentes.
*   **Definición de PVS:** Cómo se calcula o de dónde se obtiene.
*   **Lista de Campos Esenciales para el Formulario de Registro (Iteración 1):** Confirmar o ajustar la lista sugerida.
*   **Tasa de IVA Actual en Ecuador.**
*   **Textos para Términos y Condiciones.**

Este documento servirá como guía. Se espera que los detalles de cada tarea se refinen antes de su implementación.
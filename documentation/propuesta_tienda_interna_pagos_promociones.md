# Propuesta de Desarrollo: Tienda Interna, Módulo de Pagos y Módulo de Promociones

Fecha: 2025-05-13

Este documento detalla el análisis y el enfoque de desarrollo propuesto para la implementación de una funcionalidad de "Tienda Interna" en el panel de administración, así como los módulos complejos de "Pagos" y "Promociones" identificados como necesarios.

## 1. Requerimiento General

Se requiere una funcionalidad dentro del panel de administración de Filament que permita a usuarios autorizados (administradores y, potencialmente, socios) navegar por el catálogo de productos (simples, bundles fijos y personalizables) y realizar compras. Esta funcionalidad debe integrarse con sistemas robustos de gestión de pagos y promociones.

## 2. Análisis de Componentes Clave

### 2.1. Tienda Interna (Panel de Administración y Panel de Socios)

*   **Alcance para Administradores:**
    *   Realizar compras para clientes existentes (seleccionándolos desde una lista/búsqueda).
    *   Realizar compras para nuevos clientes (creando un perfil de cliente simplificado durante el proceso).
    *   El flujo se asemeja a un sistema de Punto de Venta (POS).
*   **Alcance para Socios (desde su propio panel futuro):**
    *   Comprar para sí mismos (consumo personal o stock).
    *   Comprar para nuevos clientes que registren.
    *   Comprar para socios existentes dentro de su red (seleccionándolos).
*   **Implicaciones Técnicas:**
    *   Necesidad de una interfaz clara en Filament para la selección/creación de usuarios destinatarios.
    *   Asociación correcta de carritos y pedidos al cliente/socio final.
    *   Sistema de permisos para diferenciar acciones de administradores vs. socios.
    *   Reutilización/adaptación de los componentes de catálogo y detalle de producto del frontend.

### 2.2. Módulo de Pagos Dedicado

*   **Alcance Funcional:**
    *   Integración con múltiples **pasarelas de pago locales de Ecuador** (requiere investigación y desarrollo específico por pasarela).
    *   **Pagos Offline:** Soporte para transferencias, depósitos, con capacidad para cargar y validar comprobantes de pago.
    *   **Billetera de Socio (Wallet):** Sistema de crédito/saldo interno para socios, con funcionalidades de recarga, débito por compras, y visualización de transacciones.
    *   **Crédito:** Posibilidad de otorgar crédito directo o gestionar cuentas por cobrar.
    *   **Métodos de Pago Híbridos:** Permitir combinar múltiples fuentes de pago para cubrir el total de una compra (ej. parte con billetera, parte con transferencia).
*   **Implicaciones Técnicas:**
    *   Representa un esfuerzo de desarrollo muy significativo, casi un subproyecto.
    *   Requiere un diseño de base de datos robusto para transacciones, ledgers, estados de pago, y referencias a comprobantes.
    *   Alta necesidad de seguridad, auditoría y manejo de errores.

### 2.3. Módulo de Promociones Dedicado

*   **Alcance Funcional:**
    *   Sistema de promociones y descuentos con reglas granulares y combinables:
        *   Por producto individual.
        *   Por categoría de producto.
        *   Por país.
        *   Por cantidad de puntos acumulados por el cliente/socio.
        *   Por monto total de la compra.
        *   Cupones de descuento.
        *   Promociones por tiempo limitado.
        *   Otras opciones (ej. "compre X lleve Y", descuentos por volumen).
*   **Implicaciones Técnicas:**
    *   Módulo complejo que necesita su propio modelado de datos para definir reglas, condiciones y acciones de las promociones.
    *   Interfaz de administración en Filament para crear y gestionar estas promociones.
    *   Un "motor de promociones" que pueda evaluar el carrito y el contexto del cliente/pedido para aplicar los descuentos/beneficios correctos dinámicamente.
    *   Integración con la visualización de precios en el catálogo, detalle de producto y carrito.

## 3. Factibilidad y Enfoque de Desarrollo Estratégico

Todos los módulos descritos son técnicamente factibles con la pila tecnológica actual (Laravel, Livewire, Filament). Sin embargo, la implementación completa de todos estos componentes de forma simultánea y dentro de las fases iniciales del proyecto es inviable debido a su alta complejidad y el tiempo de desarrollo requerido.

Se propone un **enfoque iterativo e incremental**, alineado con el desarrollo por fases priorizadas:

### Fase 1: Núcleo E-commerce (Ya en curso)
*   **Tarea 4 (Frontend Catálogo/Detalle):** Finalizar la implementación del catálogo público y la página de detalle del producto (sin la lógica de selección avanzada para bundles personalizables en el frontend por ahora, solo visualización).
*   **Tarea 5 (Carrito de Compras - Público):** Implementar la lógica del carrito de compras para el frontend público.
*   **Tarea 6 (Checkout Básico - Público):** Implementar un proceso de checkout básico para el frontend público. Inicialmente, podría ser con:
    *   Un método de pago offline simple (ej. "Pedido pendiente, contactar para pago").
    *   Opcional: Integración muy básica de Laravel Cashier con Stripe en modo de prueba (si el tiempo lo permite y no desvía demasiado de los módulos más complejos).
    *   Esto establecerá los modelos base `Order` y `OrderItem`.

### Fase 1.5 o Fase Temprana de "Operaciones Internas" (MVP de Tienda para Administradores)

*   **Objetivo Principal:** Permitir a los administradores crear pedidos para **clientes existentes** desde el panel de Filament.
*   **Interfaz en Filament:**
    *   Crear una nueva página/sección en Filament (ej. "Realizar Pedido" o "POS Admin").
    *   **Selección de Cliente:** Un `Select` o campo de búsqueda para elegir un cliente existente. (La creación de nuevos clientes desde esta interfaz se considera para un MVP2 o fase posterior).
    *   **Catálogo y Detalle de Producto:** Reutilizar/adaptar los componentes Livewire `ShowProductCatalog` y `ProductDetail` para mostrar productos y permitir la configuración de bundles.
    *   **Carrito (Backend):** Implementar una instancia de carrito que se asocie al cliente seleccionado por el administrador. La lógica subyacente del carrito debe ser reutilizable.
    *   **Checkout (Backend Simplificado):**
        *   Generar los registros `Order` y `OrderItem`.
        *   Permitir al administrador marcar el pedido con un estado de pago simple y offline (ej. "Pago Registrado (Efectivo)", "Pendiente Transferencia"). No se implementará la carga de comprobantes ni la integración de pasarelas de pago en este MVP.
*   **Promociones/Precios Especiales:** No se implementarán en este MVP inicial. Se utilizarán los precios estándar de los productos.

### Fases Dedicadas Posteriores (Ejemplos, a priorizar según necesidad)

*   **Módulo de Pagos Avanzado (Fase X):**
    *   Investigación e integración de pasarelas de pago ecuatorianas.
    *   Desarrollo completo de la Billetera de Socio.
    *   Implementación de carga y validación de comprobantes para pagos offline.
    *   Desarrollo de la lógica para pagos híbridos.
*   **Módulo de Promociones Avanzado (Fase Y):**
    *   Diseño e implementación del motor de reglas de promoción.
    *   Creación de la interfaz en Filament para gestionar promociones.
    *   Integración con catálogo, carrito y checkout.
*   **Panel de Socios con Funcionalidad de Tienda (Fase Z):**
    *   Desarrollo del panel de socios.
    *   Integración de la funcionalidad de tienda para socios, permitiendo compras para sí mismos, su red y nuevos clientes.
    *   Aplicación de precios/descuentos específicos para socios si aplica (requiere el Módulo de Promociones).
*   **Mejoras a la Tienda de Administradores:**
    *   Creación de nuevos clientes desde la interfaz de la tienda.
    *   Integración con los módulos avanzados de Pagos y Promociones.

## 4. Consideraciones Adicionales

*   **Registro y Gestión de Socios/Consumidores:** Como se mencionó, el modelo de negocio para el registro, la estructura de la red de socios, y la diferenciación entre socios y consumidores son módulos fundamentales que deben desarrollarse y que impactarán cómo funciona la selección de clientes y la aplicación de precios/promociones. Estos deben ser considerados en la planificación general de fases.
*   **Inventario:** La gestión de stock (Fase 4 de la ruta original) debe integrarse con todos los flujos de pedido (frontend y backend).

Este enfoque iterativo permite construir una base sólida, entregar valor funcional de manera progresiva, y gestionar la complejidad inherente a los módulos de pagos y promociones de forma más controlada.
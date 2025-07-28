# Ruta de Desarrollo Detallada: Plataforma MLM E-commerce

Este documento presenta una ruta de desarrollo priorizada para la plataforma Multinivel (MLM) con E-commerce, basada en el análisis previo y la estructura de usuarios detallada.

---

## Grupos de Usuarios Clave

La plataforma servirá a tres grupos principales de usuarios, cada uno con roles específicos:

1.  **Usuarios de la Plataforma (Frontend/Aplicación Principal):**
    *   Socios Multinivel (Distribuidores)
    *   Consumidores Registrados (Clientes)
    *   Compradores Eventuales (Invitados)

2.  **Usuarios Administrativos (Backend/Panel de Filament):**
    *   Administradores Globales
    *   Gerentes
    *   Contadores
    *   Jefe de Bodega
    *   Bodegueros
    *   Operadores de Puntos de Venta (POS)
    *   Webmaster/Soporte Técnico

3.  **Usuarios de Desarrollo (Acceso especial):**
    *   SuperDev
    *   Dev

---

## Fases de Desarrollo Priorizadas

### Fase 0: Fundación y Configuración (Realizada Parcialmente)

*   **Objetivo:** Establecer el entorno de desarrollo base, sistema de autenticación y panel de administración.
*   **Tareas Clave:**
    1.  Configuración del proyecto Laravel 11. (HECHO)
    2.  Instalación de dependencias clave: Fortify, Cashier, Scout, Horizon, Telescope, Spatie Permission, Redis. (HECHO)
    3.  Configuración de base de datos (PostgreSQL) y `.env`. (HECHO)
    4.  Implementación de panel de administración con Filament PHP. (HECHO)
    5.  Configuración inicial de autenticación (login, registro, reseteo de contraseña) a través de Filament. (HECHO)
    6.  Definición y creación de Roles y Permisos base con Spatie y Filament para los grupos de usuarios administrativos y de desarrollo. (HECHO PARCIALMENTE - Resources creados, falta poblar y asignar)
    7.  Creación de Resource de Filament para Usuarios (`UserResource`) con asignación de roles. (HECHO)
*   **Prioridad:** Crítica (Base para todo lo demás)

---

### Fase 1: Núcleo E-commerce y Gestión de Productos

*   **Objetivo:** Implementar las funcionalidades esenciales de comercio electrónico y la gestión de productos, que son la base para las ventas y el sistema de puntos.
*   **Tareas Clave:**
    1.  **Modelado de Datos de Productos:**
        *   Modelo `Product` (nombre, descripción, SKU, imágenes, precio base, etc.).
        *   Modelo `Category` (jerarquía de categorías).
        *   Modelo `Attribute` y `AttributeValue` (para variantes de productos como talla, color).
        *   Relaciones entre ellos.
    2.  **Filament Resources para E-commerce:**
        *   `ProductResource`: CRUD completo para productos, incluyendo gestión de variantes, imágenes, asignación a categorías.
        *   `CategoryResource`: CRUD para categorías.
    3.  **Asignación de Puntos por Producto:**
        *   Añadir campo `points_value` (o similar) al modelo `Product`.
        *   Integrar este campo en el `ProductResource` de Filament.
    4.  **Frontend Básico para Catálogo y Vista de Producto:**
        *   Listado de productos por categoría.
        *   Página de detalle de producto.
        *   (Puede ser muy simple inicialmente si el foco es el backend y el POS).
    5.  **Carrito de Compras:**
        *   Lógica para añadir/actualizar/eliminar productos del carrito.
        *   Cálculo de subtotales y totales.
        *   (Puede ser una implementación backend inicialmente, con vistas simples o para el POS).
    6.  **Proceso de Checkout Básico:**
        *   Recopilación de información del cliente (para invitados o registrados).
        *   Selección de dirección de envío (si aplica).
        *   Integración inicial con **Laravel Cashier** para una pasarela de pago (ej. Stripe modo prueba).
        *   Creación de modelo `Order` y `OrderItem` para registrar ventas.
        *   Filament Resource para `OrderResource` (visualización y gestión básica de pedidos).
*   **Prioridad:** Muy Alta (Sin productos y ventas, no hay MLM ni e-commerce)

---

### Fase 2: Estructura Multinivel y Acumulación de Puntos

*   **Objetivo:** Implementar la estructura de la red de distribuidores y el sistema fundamental de acumulación de puntos por compras.
*   **Tareas Clave:**
    1.  **Modelado de la Red Multinivel:**
        *   Extender el modelo `User` para incluir `sponsor_id` (o `parent_id`) para la relación de patrocinio.
        *   Lógica para asegurar la integridad de la red (ej. un usuario no puede ser su propio patrocinador).
    2.  **Registro de Socios con Patrocinador:**
        *   Modificar el flujo de registro (manejado por Filament o una página de registro de "Socios") para permitir la especificación de un patrocinador (ej. mediante código de referencia o búsqueda).
    3.  **Visualización Básica de la Red en Filament (para Administradores):**
        *   En `UserResource`, mostrar quién es el patrocinador de un usuario.
        *   Posiblemente un "Relation Manager" para mostrar los referidos directos (downline de primer nivel) de un usuario.
        *   (Visualizaciones de árbol complejas pueden dejarse para una fase posterior o para el dashboard del socio).
    4.  **Acumulación de Puntos por Compra:**
        *   Al completarse un `Order`, registrar los puntos generados por cada `OrderItem` y asociarlos al comprador.
        *   Si el comprador es un Socio Multinivel, estos puntos también contarán para su volumen personal.
    5.  **Modelo `UserPointLedger` o similar:**
        *   Tabla para registrar todas las transacciones de puntos (créditos por compras, débitos por canjes si aplica, origen del punto).
    6.  **Visualización de Puntos Acumulados:**
        *   En el `UserResource` de Filament, mostrar los puntos totales de un usuario.
        *   En el dashboard del Socio (frontend, a desarrollar), mostrar sus puntos.
*   **Prioridad:** Muy Alta (Es el corazón del sistema MLM)

---

### Fase 3: Sistema de Compensaciones y Rangos (Versión Inicial)

*   **Objetivo:** Implementar un primer conjunto de reglas de compensación y el sistema de rangos.
*   **Tareas Clave:**
    1.  **Modelado de Planes de Compensación y Bonos:**
        *   Modelo `CompensationPlan`.
        *   Modelo `BonusType` (Unilevel, Binario, Inicio Rápido, etc.).
        *   Modelo `BonusRule` (condiciones, porcentajes, niveles afectados).
    2.  **Filament Resources para Compensaciones:**
        *   `CompensationPlanResource`.
        *   `BonusRuleResource` (permitiendo a los administradores definir y ajustar estas reglas).
    3.  **Cálculo de Comisiones (Unilevel Básico Inicialmente):**
        *   Lógica (probablemente trabajos encolados con Horizon/Redis) para calcular comisiones unilevel basadas en los puntos generados por el downline del socio.
        *   Modelo `CommissionLedger` o similar para registrar comisiones ganadas.
    4.  **Modelado de Rangos:**
        *   Modelo `Rank` (nombre del rango, requisitos de puntos personales, puntos de equipo, etc.).
    5.  **Filament Resource para Rangos (`RankResource`):**
        *   Permitir a los administradores definir los rangos y sus criterios.
    6.  **Lógica de Calificación y Asignación de Rangos:**
        *   Proceso (posiblemente un comando de Artisan programado o un job) para evaluar periódicamente a los socios y asignar/actualizar rangos.
    7.  **Visualización de Comisiones y Rangos:**
        *   En el `UserResource` de Filament, mostrar el rango actual y un resumen de comisiones.
        *   En el dashboard del Socio (frontend), mostrar esta información.
*   **Prioridad:** Alta (Motivación principal para los socios)

---

### Fase 4: Gestión Multi-Bodegas e Inventario

*   **Objetivo:** Implementar la capacidad de gestionar inventario a través de múltiples bodegas.
*   **Tareas Clave:**
    1.  **Modelado de Bodegas e Inventario:**
        *   Modelo `Warehouse`.
        *   Modelo `Inventory` o `Stock` (producto_id, warehouse_id, cantidad).
    2.  **Filament Resources para Bodegas e Inventario:**
        *   `WarehouseResource`.
        *   `InventoryResource` (o integrado en `ProductResource` y `WarehouseResource` mediante Relation Managers).
    3.  **Gestión de Stock por Bodega:**
        *   Lógica para añadir, restar, y transferir stock entre bodegas.
    4.  **Integración con E-commerce y Checkout:**
        *   Permitir la selección de bodega de despacho (o asignación automática basada en la ubicación del cliente/disponibilidad).
        *   Asegurar que los pedidos descuenten stock de la bodega correcta.
*   **Prioridad:** Media-Alta (Importante para operaciones si se maneja stock físico diverso)

---

### Fase 5: Sistema de Puntos de Venta (POS)

*   **Objetivo:** Desarrollar una interfaz para ventas físicas que se integre con el sistema.
*   **Tareas Clave:**
    1.  **Diseño de Interfaz POS:**
        *   Simple, rápida y adaptable (tablets/escritorio).
    2.  **Funcionalidades POS:**
        *   Búsqueda de productos.
        *   Añadir al carrito.
        *   Aplicar descuentos (si aplica).
        *   Procesar pagos (puede ser efectivo o integrado con terminales de pago).
        *   Asignar la venta a un Socio Multinivel (si es relevante para comisiones/puntos).
        *   Impresión de recibos (opcional).
    3.  **Sincronización en Tiempo Real:**
        *   Actualización de inventario de la bodega/POS asignada.
        *   Registro de la venta y puntos en el sistema central.
    4.  **Filament: Gestión de Terminales POS y Operadores:**
        *   Asignar usuarios (Operadores POS) a terminales/bodegas específicas.
*   **Prioridad:** Media (Depende de la importancia de las ventas físicas para el modelo de negocio)

---

### Fase 6: Frontend para Socios y Clientes

*   **Objetivo:** Desarrollar la interfaz de usuario para que los socios y clientes interactúen con la plataforma.
*   **Tareas Clave:**
    1.  **Dashboard del Socio:**
        *   Visualización de perfil, puntos, rango, comisiones.
        *   Visualización de su red (downline).
        *   Historial de pedidos y comisiones.
        *   Herramientas de reclutamiento (enlace de referido).
        *   Acceso al catálogo de productos y carrito de compras.
    2.  **Portal del Cliente:**
        *   Gestión de perfil, direcciones.
        *   Historial de pedidos.
        *   Navegación del catálogo y proceso de compra.
    3.  **Diseño UI/UX:**
        *   Asegurar una experiencia de usuario intuitiva y atractiva.
*   **Prioridad:** Media-Alta (Es la cara visible para la mayoría de los usuarios)

---

### Fase 7: Personalización Avanzada, Reportes y Notificaciones

*   **Objetivo:** Añadir flexibilidad y herramientas de análisis.
*   **Tareas Clave:**
    1.  **Panel Administrativo (Filament) Mejorado:**
        *   Más opciones de configuración para planes de compensación, bonos, promociones.
    2.  **Sistema de Reportes Avanzados (Filament o herramienta externa):**
        *   Ventas, crecimiento de red, actividad de socios, efectividad de bonos, etc.
        *   Filtros, exportación.
    3.  **Sistema de Notificaciones:**
        *   Email/SMS/En-plataforma para eventos clave (nuevo referido, comisión ganada, cambio de rango, nuevo pedido, etc.).
*   **Prioridad:** Media

---

### Fase 8: Optimización, Seguridad Avanzada y Escalabilidad

*   **Objetivo:** Refinar la plataforma para producción a gran escala.
*   **Tareas Clave:**
    1.  **Pruebas Exhaustivas:** Unitarias, de integración, funcionales, de carga.
    2.  **Optimización de Rendimiento:** Consultas de base de datos, código, tiempos de carga.
    3.  **Auditorías de Seguridad:** Revisión de vulnerabilidades, XSS, CSRF, SQL Injection, seguridad de API.
    4.  **Autenticación Multifactor (MFA):** Para usuarios administrativos y opcional para socios. (Fortify ya tiene la base).
    5.  **Estrategias de Backup y Recuperación de Desastres.**
    6.  **Monitoreo y Logging Avanzado.**
    7.  **Preparación para Escalado Horizontal/Vertical.**
*   **Prioridad:** Continua y Crítica antes del lanzamiento a gran escala.

---

Este plan es una guía. La duración y el esfuerzo de cada fase dependerán de la profundidad de las funcionalidades requeridas y del tamaño del equipo de desarrollo. Es crucial iterar y obtener feedback constante.
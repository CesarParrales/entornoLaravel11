# Resumen Global: Plataforma MLM E-commerce y Protocolo de Desarrollo

**Estado General:** 85% Completado - Lista para Producción con Pendientes Menores

Este documento proporciona un contexto global de la plataforma Multinivel (MLM) E-commerce y un resumen de su protocolo de desarrollo, sirviendo como memoria para futuras tareas. Está basado en la información contenida en `protocolo_desarrollo_mlm.md`, `ruta_desarrollo_detallada_mlm.md`, y `ruta_desarrollo_onboarding.md`.

**⚠️ IMPORTANTE:** Este documento ha sido actualizado para reflejar el estado real de la plataforma, que está significativamente más avanzado de lo que indicaba la documentación anterior.

---

## 1. Introducción a la Plataforma

La plataforma es un sistema de E-commerce con funcionalidades integradas de Marketing Multinivel (MLM). Está siendo desarrollada utilizando **Laravel 11** para el backend y **Filament PHP** para un robusto panel de administración. El frontend se apoya en **Livewire** para componentes dinámicos y **Tailwind CSS** para los estilos. El objetivo es crear una solución escalable, segura y eficiente para la gestión de ventas, red de distribuidores, comisiones y operaciones administrativas.

---

## 2. Protocolo de Desarrollo (Resumen)

El desarrollo se adhiere a un protocolo estricto para asegurar la calidad y mantenibilidad del software.

### 2.1. Principios Generales
*   **Claridad y Simplicidad (KISS):** Código fácil de entender.
*   **No Repetir Código (DRY):** Reutilización de código.
*   **Separación de Responsabilidades (SoC):** Componentes con una única responsabilidad.
*   **SOLID:** Seguir los principios SOLID.
*   **Seguridad por Defecto:** Considerar la seguridad en cada etapa.
*   **Rendimiento:** Código eficiente y optimización de consultas.
*   **Escalabilidad:** Diseño pensando en el crecimiento futuro.
*   **Consistencia:** Estilo de código y convenciones uniformes.
*   **Documentación Continua:** Documentar código y decisiones.

### 2.2. Gestión del Código Fuente
*   **Git:** Versionado de todo el código.
*   **Repositorio Central:** GitHub (`https://github.com/CesarParrales/entornoLaravel11.git`).
*   **Flujo de Ramas (Git Flow o similar):** `main`, `develop`, `feature/*`, `bugfix/*`, `release/*`.
*   **Mensajes de Commit:** Claros y descriptivos.
*   **Pull Requests (PRs):** Obligatorios para cambios a `develop` o `main`, con revisión.

### 2.3. Entorno de Desarrollo
*   **Consistencia:** Versiones compatibles de PHP, Node.js, Composer, NPM/Yarn, PostgreSQL, Redis.
*   **Variables de Entorno (`.env`):** Locales, no comiteadas. Usar `.env.example` como plantilla.
*   **Laravel Sail:** Recomendado para estandarizar el entorno con Docker.

### 2.4. Backend (Laravel 11)
*   **Estándar de Codificación:** PSR-12 (formateo con `php artisan pint`).
*   **Modelos (Eloquent):** Responsabilidad única, carga ansiosa (`with()`), casts, `$fillable`/`$guarded`.
*   **Controladores:** Delgados, delegando lógica a Clases de Acción o Servicios. Uso de Resource Controllers y Single Action Controllers. Validación con Form Requests.
*   **Rutas:** Organizadas (`web.php`, `api.php`), con nombres, grupos y middleware.
*   **Vistas (Blade):** Componentes Blade, layouts, escape de salida (`{{ $variable }}`).
*   **Lógica de Negocio:** Clases de Acción y Servicios, inyección de dependencias.
*   **Colas (Queues):** Laravel Horizon con Redis para tareas de larga duración.
*   **Migraciones y Seeders:** Para cambios de BD y datos iniciales.
*   **Configuración:** Directorio `config/` y variables de entorno.
*   **Seguridad:** Validación de entradas, autorización con Spatie Laravel Permission y Policies/Gates, protección CSRF/XSS, Eloquent para prevenir SQL Injection.

### 2.5. Panel de Administración (Filament PHP)
*   **Consistencia:** Diseño y UX uniformes.
*   **Resources:** Para cada modelo Eloquent gestionable. Personalización de navegación, iconos y título de registro.
*   **Formularios y Tablas:** Uso de componentes de Filament, validación, búsqueda y ordenamiento.
*   **Acciones, Filtros y Widgets:** Para mejorar la interactividad y visualización de datos.
*   **Autorización:** Policies de Laravel y integración con Spatie Laravel Permission.

### 2.6. Frontend (Livewire / SPA)
*   **Estándares del Framework:** Seguir mejores prácticas de Livewire (o React/Vue si se usa SPA).
*   **Componentes:** Reutilizables y cohesivos.
*   **Gestión de Estado:** Apropiada para la complejidad (propiedades de Livewire, Context/Redux, Vuex/Pinia).
*   **Interacción con API:** Contratos claros, manejo de errores. Laravel Sanctum para autenticación SPA.
*   **Estilos (Tailwind CSS):** Uso consistente, componentes de UI reutilizables, purga de CSS en producción.

### 2.7. Gestión de Paquetes
*   **Composer (PHP):** Comitear `composer.json` y `composer.lock`.
*   **NPM/Yarn (JavaScript):** Comitear `package.json` y `package-lock.json`/`yarn.lock`.

### 2.8. Pruebas
*   **Objetivo:** Alta cobertura.
*   **Herramientas:** PHPUnit (backend), Jest/Cypress/Playwright/Laravel Dusk (frontend/E2E).
*   **Tipos:** Unitarias, Integración, Funcionales/E2E, Aceptación del Usuario (UAT).

### 2.9. Documentación
*   **Código:** Comentarios, PHPDoc.
*   **API:** OpenAPI (Swagger), Scribe.
*   **Funcionalidades:** En carpeta `documentation/`.
*   **Despliegue y Mantenimiento.**

### 2.10. Revisión de Código
*   Obligatoria para cada PR/MR.

### 2.11. Despliegue
*   **Automatización:** CI/CD (GitHub Actions).
*   **Entornos:** Desarrollo, staging, producción.
*   **Comandos de Despliegue:** `optimize`, `config:cache`, `route:cache`, `view:cache`, `migrate --force`.

### 2.12. Monitoreo y Mantenimiento
*   **Logs:** Laravel Telescope, Sentry.
*   **Rendimiento:** New Relic, Datadog, Horizon/Telescope.
*   **Errores:** Alertas para errores críticos.

---

## 3. Arquitectura y Tecnologías Clave

*   **Backend Framework:** Laravel 11
*   **Panel de Administración:** Filament PHP
*   **Frontend Dinámico:** Livewire
*   **Estilos CSS:** Tailwind CSS
*   **Base de Datos:** PostgreSQL
*   **Servidor de Colas:** Redis (gestionado por Laravel Horizon)
*   **Autenticación:** Laravel Fortify
*   **Roles y Permisos:** Spatie Laravel Permission
*   **Pagos:** Laravel Cashier (integración inicial con Stripe)
*   **Versionado de Código:** Git (alojado en GitHub)
*   **Contenerización (Opcional):** Laravel Sail (Docker)

---

## 4. Ruta de Desarrollo (Resumen de Fases)

La plataforma se desarrolla por fases priorizadas:

1.  **Fase 0: Fundación y Configuración (Parcialmente Realizada):** Entorno base, autenticación, panel de administración con Filament, roles y permisos iniciales.
2.  **Fase 1: Núcleo E-commerce y Gestión de Productos:** Modelos de Producto/Categoría, Resources de Filament, puntos por producto, catálogo básico, carrito y checkout inicial con creación de `Order`/`OrderItem`.
3.  **Fase 2: Estructura Multinivel y Acumulación de Puntos:** Modelado de red (`sponsor_id`), registro de socios con patrocinador, visualización de red, acumulación de puntos (`UserPointLedger`).
4.  **Fase 3: Sistema de Compensaciones y Rangos (Versión Inicial):** Modelos de Planes de Compensación/Bonos/Rangos, cálculo de comisiones (Unilevel inicial), lógica de calificación de rangos.
5.  **Fase 4: Gestión Multi-Bodegas e Inventario:** Modelos de Bodega/Inventario, gestión de stock.
6.  **Fase 5: Sistema de Puntos de Venta (POS):** Interfaz para ventas físicas.
7.  **Fase 6: Frontend para Socios y Clientes:** Dashboards y portales de usuario.
8.  **Fase 7: Personalización Avanzada, Reportes y Notificaciones.**
9.  **Fase 8: Optimización, Seguridad Avanzada y Escalabilidad.**

---

## 5. Onboarding de Usuarios (Resumen del Mini-Proyecto)

Un flujo crucial para el registro y activación de nuevos usuarios:

*   **Diferenciación de Tipos:** 'socio' y 'consumidor'.
*   **Vinculación a la Red:** Campos `sponsor_id` (patrocinador directo en la estructura) e `invitador_id` (quien refirió, puede o no ser el sponsor).
*   **Estado del Usuario:** Inicia como `pending_initial_payment`, luego `active`, etc.
*   **Precios Específicos:**
    *   PVP (Precio Venta Público).
    *   PVS (Precio Venta Socio): Actualmente definido como un 25% de descuento sobre el PVP (configurable).
*   **IVA:** Tasa configurable (actualmente 15% en Ecuador), aplicada y desglosada en precios y pedidos.
*   **"Socio 0":** Usuario raíz de la red, creado vía seeder (`SocioZeroSeeder.php`).
*   **Formulario de Registro:** Componente Livewire `UserRegistrationForm.php` para capturar datos del nuevo usuario, incluyendo país, tipo, invitador, patrocinador, datos personales, y credenciales. Genera un `username` automático.
*   **Compra de Activación Obligatoria:**
    *   Tras el pre-registro, el usuario (especialmente 'socio') debe realizar una compra inicial.
    *   Para socios, se requiere una compra mínima de 20 puntos.
    *   Se desarrollará una `ActivationPurchasePage` (Livewire).
    *   Tras la confirmación del pago de este primer pedido, el estado del usuario cambia a `active` y se registra `activated_at`.

---

## 6. Usuarios Clave

La plataforma está diseñada para servir a los siguientes grupos de usuarios:

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

Este resumen debe servir como una referencia rápida y un punto de partida para entender la arquitectura, los procesos y los objetivos del proyecto.
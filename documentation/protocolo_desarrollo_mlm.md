# Protocolo de Desarrollo: Plataforma MLM E-commerce

**Versión:** 2.0  
**Fecha de Actualización:** {{env.CURRENT_DATE}}  
**Estado de la Plataforma:** 85% Completado - Lista para Producción

Este documento establece el protocolo de desarrollo para la plataforma Multinivel (MLM) con E-commerce. Su objetivo es asegurar la calidad, consistencia, mantenibilidad y escalabilidad del software, **manteniendo siempre la integridad y funcionalidad de la plataforma existente**.

**⚠️ PRINCIPIO FUNDAMENTAL:** Antes de implementar cualquier nueva funcionalidad, revisar exhaustivamente el código existente para evitar duplicaciones y mantener la coherencia del sistema.

---

## Tabla de Contenidos

- [1. Principios Generales](#1-principios-generales)
  - [1.1. Principios de Desarrollo](#11-principios-de-desarrollo)
  - [1.2. Principios de Seguridad](#12-principios-de-seguridad)
  - [1.3. Principios de Mantenimiento](#13-principios-de-mantenimiento)
- [2. Gestión del Código Fuente](#2-gestión-del-código-fuente)
  - [2.1. Control de Versiones](#21-control-de-versiones)
  - [2.2. Flujo de Trabajo](#22-flujo-de-trabajo)
- [3. Entorno de Desarrollo](#3-entorno-de-desarrollo)
  - [3.1. Tecnologías Base](#31-tecnologías-base)
  - [3.2. Configuración](#32-configuración)
- [4. Backend (Laravel 12.0)](#4-backend-laravel-120)
  - [4.1. Convenciones y Estándares](#41-convenciones-y-estándares)
  - [4.2. Modelos (Eloquent)](#42-modelos-eloquent)
  - [4.3. Controladores](#43-controladores)
  - [4.4. Rutas](#44-rutas)
  - [4.5. Vistas (Blade)](#45-vistas-blade)
  - [4.6. Lógica de Negocio y Servicios](#46-lógica-de-negocio-y-servicios)
  - [4.7. Migraciones y Seeders](#47-migraciones-y-seeders)
  - [4.8. Configuración](#48-configuración)
  - [4.9. Seguridad](#49-seguridad)
- [5. Panel de Administración (Filament 3.2)](#5-panel-de-administración-filament-32)
  - [5.1. Resources](#51-resources)
  - [5.2. Formularios y Tablas](#52-formularios-y-tablas)
  - [5.3. Acciones, Filtros y Widgets](#53-acciones-filtros-y-widgets)
  - [5.4. Autorización](#54-autorización)
- [6. Frontend (Livewire 3.x + Tailwind CSS 4.0)](#6-frontend-livewire-3x--tailwind-css-40)
  - [6.1. Componentes](#61-componentes)
  - [6.2. Gestión de Estado](#62-gestión-de-estado)
  - [6.3. Interacción con API](#63-interacción-con-api)
  - [6.4. Estilos (Tailwind CSS)](#64-estilos-tailwind-css)
- [7. Gestión de Paquetes y Dependencias](#7-gestión-de-paquetes-y-dependencias)
  - [7.1. Composer (PHP)](#71-composer-php)
  - [7.2. NPM/Yarn (JavaScript)](#72-npmyarn-javascript)
- [8. Funcionalidades MLM Específicas](#8-funcionalidades-mlm-específicas)
  - [8.1. Sistema de Bonos](#81-sistema-de-bonos)
  - [8.2. Sistema de Rangos](#82-sistema-de-rangos)
  - [8.3. Estructura de Red](#83-estructura-de-red)
  - [8.4. Billetera y Transacciones](#84-billetera-y-transacciones)
- [9. Pruebas](#9-pruebas)
  - [9.1. Pruebas Unitarias](#91-pruebas-unitarias)
  - [9.2. Pruebas de Integración](#92-pruebas-de-integración)
  - [9.3. Pruebas Funcionales/E2E](#93-pruebas-funcionalese2e)
  - [9.4. Pruebas de Aceptación del Usuario (UAT)](#94-pruebas-de-aceptación-del-usuario-uat)
  - [9.5. Cobertura de Pruebas](#95-cobertura-de-pruebas)
- [10. Documentación](#10-documentación)
  - [10.1. Documentación del Código](#101-documentación-del-código)
  - [10.2. Documentación de API](#102-documentación-de-api)
  - [10.3. Documentación de Funcionalidades](#103-documentación-de-funcionalidades)
  - [10.4. Documentación de Despliegue](#104-documentación-de-despliegue)
- [11. Revisión de Código (Code Review)](#11-revisión-de-código-code-review)
- [12. Despliegue](#12-despliegue)
- [13. Monitoreo y Mantenimiento](#13-monitoreo-y-mantenimiento)

---

## 1. Principios Generales

### **1.1 Principios de Desarrollo**
*   **Claridad y Simplicidad (KISS):** Código claro y fácil de entender.
*   **No Repetir Código (DRY):** Reutilizar código existente.
*   **Separación de Responsabilidades (SoC):** Una responsabilidad por componente.
*   **SOLID:** Aplicar principios SOLID.

### **1.2 Principios de Seguridad**
*   **Seguridad por Defecto:** Validar todas las entradas de usuario.
*   **Autorización:** Usar Spatie Permission para roles y permisos.
*   **Validación:** Validar datos en formularios y APIs.
*   **Sanitización:** Escapar salida HTML siempre.

### **1.3 Principios de Mantenimiento**
*   **Integridad del Sistema:** Mantener funcionalidad existente.
*   **Revisión de Código:** Buscar implementaciones existentes antes de crear nuevas.
*   **Migraciones Puntuales:** Crear migraciones específicas, no generales.
*   **Documentación:** Documentar cambios y decisiones.

## 2. Gestión del Código Fuente

### **2.1 Control de Versiones**
*   **Git:** Versionado obligatorio de todo el código.
*   **Repositorio:** `https://github.com/CesarParrales/entornoLaravel11.git`
*   **Ramas:**
    *   `main`: Producción estable
    *   `develop`: Integración de features
    *   `feature/nombre`: Nuevas funcionalidades
    *   `bugfix/nombre`: Correcciones
    *   `release/version`: Preparación de lanzamientos

### **2.2 Flujo de Trabajo**
*   **Commits:** Formato convencional ("feat:", "fix:", "docs:")
*   **Pull Requests:** Obligatorios para `develop` y `main`
*   **Revisión:** Mínimo un revisor por PR
*   **Testing:** Ejecutar pruebas antes de merge

## 3. Entorno de Desarrollo

### **3.1 Tecnologías Base**
*   **PHP:** 8.2+
*   **Laravel:** 12.0
*   **Node.js:** Compatible con Vite 6.2.4
*   **Base de Datos:** PostgreSQL/MySQL
*   **Redis:** Para colas y cache

### **3.2 Configuración**
*   **`.env`:** Nunca comitear, usar `.env.example`
*   **Laravel Sail:** Recomendado para Docker
*   **Composer:** Usar `composer install`, no `composer update`
*   **NPM:** Usar `npm install` para dependencias

## 4. Backend (Laravel 12.0)

### 4.1. Convenciones y Estándares

*   **PSR-12:** Formateo con `php artisan pint`
*   **Nomenclatura:** camelCase (métodos/variables), PascalCase (clases)
*   **Revisión de Código:** Buscar implementaciones existentes antes de crear nuevas

### 4.2. Modelos (Eloquent)

*   **Responsabilidad Única:** Solo lógica de entidad (relaciones, accessors, mutators)
*   **Lógica de Negocio:** Mover a Services o Action Classes
*   **Relaciones:** Definir claramente (`hasOne`, `hasMany`, `belongsTo`, etc.)
*   **Eager Loading:** Usar `with()` para evitar N+1 queries
*   **Casts:** Usar para tipos de datos (`boolean`, `date`, `array`, `encrypted`)
*   **Mass Assignment:** Definir `$fillable` o `$guarded`

### 4.3. Controladores

*   **Skinny Controllers:** Solo manejo de HTTP, delegar lógica a Services
*   **Resource Controllers:** Para CRUD estándar (`--resource`)
*   **Single Action Controllers:** Para acciones únicas (`--invokable`)
*   **Validación:** Usar Form Requests (`php artisan make:request NombreRequest`)

### 4.4. Rutas

*   **Organización:** `web.php`, `api.php`
*   **Nombres:** Asignar nombres (`->name('nombre.ruta')`)
*   **Grupos:** Usar para middleware, prefijos, namespaces
*   **Middleware:** Aplicar granularmente (`auth`, `role`, `permission`)

### 4.5. Vistas (Blade)

*   **Componentes:** Crear componentes reutilizables
*   **Layouts:** Definir layouts base para consistencia
*   **Directivas:** Crear directivas personalizadas si es necesario
*   **Seguridad:** Escapar HTML (`{{ $variable }}`), usar `{!! !!}` con precaución

### 4.6. Lógica de Negocio y Servicios

*   **Action Classes:** Para tareas específicas (`RegistrarNuevoSocioAction`)
*   **Service Classes:** Para lógica compleja (`BonusService`, `WalletService`)
*   **Inyección de Dependencias:** Usar DI de Laravel
*   **Colas:** Laravel Horizon + Redis para tareas largas

### 4.7. Migraciones y Seeders

*   **Migraciones Puntuales:**
    *   Crear migraciones específicas, no generales
    *   Nombres descriptivos
    *   Método `down()` debe revertir `up()`
    *   **⚠️ IMPORTANTE:** Revisar migraciones existentes antes de crear nuevas
*   **Seeders:**
    *   Para datos iniciales (roles, permisos, rangos)
    *   Organizar en `DatabaseSeeder`

### 4.8. Configuración

*   **Archivos:** Usar directorio `config/` para configuración
*   **Variables:** `config()` para configuración, `env()` para valores sensibles

### 4.9. Seguridad

*   **Validación:** Validar todas las entradas de usuario
*   **Autorización:** Spatie Permission + Policies/Gates
*   **CSRF/XSS:** Protecciones de Laravel activas
*   **SQL Injection:** Usar Eloquent y consultas parametrizadas
*   **Contraseñas:** Hashear siempre (Laravel por defecto)
*   **Dependencias:** Mantener actualizadas

## 5. Panel de Administración (Filament 3.2)

*   **Consistencia:** Diseño y UX uniformes en todo el panel
*   **Seguridad:** Políticas de autorización para Resources y acciones

### 5.1. Resources

*   **Generar:** Para cada modelo Eloquent gestionable
*   **Personalizar:** Iconos (`$navigationIcon`) y grupos (`$navigationGroup`)
*   **Títulos:** Definir `$recordTitleAttribute` para representación

### 5.2. Formularios y Tablas

*   **Formularios:**
    *   Usar componentes de Filament apropiadamente
    *   Aplicar reglas de validación
    *   Considerar UX (agrupación, layouts)
    *   Manejar contraseñas (hashing, opcionalidad)
*   **Tablas:**
    *   Seleccionar columnas relevantes
    *   Usar `TextColumn`, `IconColumn`, `BadgeColumn`
    *   Implementar búsqueda (`searchable()`) y ordenamiento (`sortable()`)
    *   Usar `toggleable()` para columnas menos importantes

### 5.3. Acciones, Filtros y Widgets

*   **Acciones:** Personalizar acciones de tabla y formulario
*   **Filtros:** Implementar filtros para búsqueda de datos
*   **Widgets:** Crear widgets para dashboard con estadísticas

### 5.4. Autorización

*   **Policies:** Usar Policies de Laravel para control de acceso
*   **Spatie Permission:** Integrar para roles y permisos

## 6. Frontend (Livewire 3.x + Tailwind CSS 4.0)

*   **Livewire:** Componentes reactivos para funcionalidad dinámica
*   **Tailwind CSS:** Framework de estilos principal
*   **Vite:** Bundler para assets

### 6.1. Componentes

*   **Reutilizables:** Diseñar componentes reutilizables
*   **Cohesivos:** Una responsabilidad por componente
*   **Nomenclatura:** Nombres claros y descriptivos

### 6.2. Gestión de Estado

*   **Livewire:** Usar propiedades de Livewire para estado
*   **Complejidad:** Escalar según necesidades de la aplicación

### 6.3. Interacción con API

*   **Contratos:** Definir contratos claros para API
*   **Errores:** Manejar errores y estados de carga
*   **Autenticación:** Laravel Sanctum si es necesario

### 6.4. Estilos (Tailwind CSS)

*   **Consistencia:** Usar Tailwind CSS de manera consistente
*   **Componentes:** Crear componentes UI reutilizables
*   **Configuración:** `tailwind.config.js` para purgar CSS
*   **Responsividad:** Asegurar diseño responsive

## 7. Gestión de Paquetes y Dependencias

### 7.1. Composer (PHP)

*   **Comitear:** `composer.json` y `composer.lock`
*   **Instalar:** `composer install` (no `composer update`)
*   **Revisar:** `composer outdated` periódicamente

### 7.2. NPM/Yarn (JavaScript)

*   **Comitear:** `package.json` y `package-lock.json`
*   **Instalar:** `npm install` o `yarn install`
*   **Revisar:** Dependencias periódicamente

## 8. Funcionalidades MLM Específicas

### 8.1. Sistema de Bonos

*   **BonusService:** Centralizar lógica de bonos
*   **Eventos:** Usar eventos para triggers de bonos
*   **Listeners:** Procesar eventos de forma asíncrona
*   **Validación:** Validar condiciones antes de aplicar bonos

### 8.2. Sistema de Rangos

*   **Rank Model:** Mantener jerarquía de rangos
*   **UserPeriodRank:** Registrar historial de rangos
*   **Calificación:** Lógica automática de calificación
*   **Validación:** Verificar requisitos antes de asignar

### 8.3. Estructura de Red

*   **Relaciones:** `sponsor_id`, `referrer_id`, `placement_id`
*   **Validación:** Verificar integridad de la red
*   **Niveles:** Calcular niveles automáticamente
*   **Restricciones:** Evitar ciclos en la red

### 8.4. Billetera y Transacciones

*   **WalletService:** Operaciones atómicas
*   **Transacciones:** Registrar todos los movimientos
*   **Validación:** Verificar saldos antes de operaciones
*   **Auditoría:** Mantener trazabilidad completa

## 9. Pruebas

*   **Objetivo:** Alta cobertura para estabilidad y fiabilidad
*   **Herramientas:** PHPUnit (backend), Laravel Dusk (navegador)

### 9.1. Pruebas Unitarias

*   **Unidades:** Probar clases y métodos aislados
*   **Mocking:** Mockear dependencias externas
*   **Críticas:** Lógica de negocio MLM (bonos, rangos)

### 9.2. Pruebas de Integración

*   **Componentes:** Probar interacción entre componentes
*   **Base de Datos:** Probar con BD real o de prueba

### 9.3. Pruebas Funcionales/E2E

*   **Flujos:** Probar flujos completos de usuario
*   **API:** Probar endpoints con autenticación y validación
*   **Frontend:** Probar interacción con UI
*   **Filament:** Probar CRUD en panel administrativo
*   **Socios:** Probar funcionalidades del portal de socios

### 9.4. Pruebas de Aceptación del Usuario (UAT)

*   **Stakeholders:** Validar requerimientos con usuarios finales

### 9.5. Cobertura de Pruebas

*   **Medición:** Usar herramientas para medir cobertura
*   **Áreas Críticas:** Enfocarse en lógica de negocio MLM

## 10. Documentación

### 10.1. Documentación del Código

*   **Comentarios:** Explicar lógica compleja
*   **PHPDoc:** Para clases, métodos y propiedades

### 10.2. Documentación de API

*   **OpenAPI:** Si se expone API, usar Swagger
*   **Scribe:** Herramienta recomendada para Laravel

### 10.3. Documentación de Funcionalidades

*   **Carpeta:** Mantener en `/documentation/`
*   **Reglas de Negocio:** Documentar especialmente MLM

### 10.4. Documentación de Despliegue

*   **Entornos:** Instrucciones para diferentes entornos
*   **Mantenimiento:** Procedimientos comunes

## 11. Revisión de Código (Code Review)

*   **Obligatorio:** Revisión por al menos otro miembro
*   **Enfoque:** Correctitud, claridad, rendimiento, seguridad
*   **Feedback:** Proporcionar feedback constructivo

## 12. Despliegue

*   **CI/CD:** Automatizar con GitHub Actions
*   **Entornos:** Desarrollo, staging, producción
*   **Zero-Downtime:** Minimizar tiempo de inactividad
*   **Comandos:** `optimize`, `config:cache`, `route:cache`, `view:cache`, `migrate --force`

## 13. Monitoreo y Mantenimiento

*   **Logs:** Laravel Telescope, Sentry, Papertrail
*   **Rendimiento:** New Relic, Datadog, Horizon/Telescope
*   **Errores:** Alertas para errores críticos
*   **Actualizaciones:** Planificar actualizaciones regulares

---

## 📋 **Checklist de Cumplimiento**

### **Antes de Implementar Nueva Funcionalidad:**
- [ ] Revisar código existente para evitar duplicaciones
- [ ] Verificar migraciones existentes
- [ ] Validar integridad del sistema MLM
- [ ] Documentar cambios y decisiones

### **Durante el Desarrollo:**
- [ ] Seguir principios SOLID
- [ ] Validar todas las entradas de usuario
- [ ] Usar Spatie Permission para autorización
- [ ] Crear migraciones puntuales
- [ ] Documentar código con PHPDoc

### **Antes del Merge:**
- [ ] Ejecutar pruebas unitarias
- [ ] Verificar cobertura de código
- [ ] Revisar seguridad
- [ ] Actualizar documentación

---

**⚠️ IMPORTANTE:** Este protocolo es un documento vivo que debe actualizarse conforme evoluciona la plataforma. La adherencia a estas directrices asegura la integridad y funcionalidad del sistema MLM.
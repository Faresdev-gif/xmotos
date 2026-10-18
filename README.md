README
# 🏍️ XMOTOS · E-commerce + Replicación por Triggers + Monitor de Observabilidad

**Sistema integral de gestión para venta de motocicletas con replicación de base de datos en tiempo real y panel de observabilidad tipo Grafana.**

[![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php&logoColor=white)](https://php.net)
[![MariaDB](https://img.shields.io/badge/MariaDB-10.4-003545?logo=mariadb&logoColor=white)](https://mariadb.org)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?logo=bootstrap&logoColor=white)](https://getbootstrap.com)
[![Chart.js](https://img.shields.io/badge/Chart.js-4.4-FF6384?logo=chart.js&logoColor=white)](https://chartjs.org)
[![License](https://img.shields.io/badge/License-MIT-green)](LICENSE)

---

## 📖 Descripción

**XMOTOS** es una plataforma de comercio electrónico especializada en la venta de motocicletas, con un sistema de **replicación síncrona** de base de datos implementado mediante **triggers de MariaDB**. El proyecto incluye además un **monitor de observabilidad** que permite visualizar en tiempo real el estado de la replicación, el historial de operaciones y el estado general del sistema.

Este proyecto fue desarrollado como parte del curso de **Sistemas Distribuidos**, demostrando conceptos de:
- **Replicación de bases de datos** (maestro-réplica).
- **Auditoría forense** mediante logs estructurados.
- **Separación de dominios de seguridad**.
- **Arquitectura MVC** sin frameworks.
- **Monitoreo y observabilidad** de sistemas en producción.
---

## ✨ Características principales

### 🌐 Aplicación de negocio (E-commerce)
- **Catálogo de motos** con filtros por categoría (Sport, Naked, Adventure, Urbana, Scooter, On-Off).
- **Carrito de compras** persistente por usuario.
- **Sistema de favoritos** con AJAX.
- **Cotización y financiamiento** de motos.
- **Paneles diferenciados** para clientes, vendedores y administradores.
- **Gestión de inventario** con movimientos de stock.
- **Compras a proveedores** con detalle.
- **Devoluciones** y su flujo de aprobación.

### 🔄 Sistema de replicación
- **21 triggers** activos sobre **7 tablas** replicadas:
  - `usuarios` → `usuarios_replica`
  - `productos` → `productos_replica`
  - `ventas` → `ventas_replica`
  - `venta_detalle` → `venta_detalle_replica`
  - `solicitudes` → `solicitudes_replica`
  - `compras` → `compras_replica`
  - `compras_detalle` → `compras_detalle_replica`
- **Tabla de auditoría global** (`replica_log`) con datos en formato JSON (antes/después).
- **Seed idempotente** para sincronización inicial.
- **Registro de operación** (`INSERT`, `UPDATE`, `DELETE`, `SEED`) por cada cambio.

### 📊 Monitor de observabilidad
- **Dashboard** con KPIs (tablas replicadas, total operaciones, actividad reciente).
- **Gráfico Chart.js** de operaciones por hora (últimas 24h).
- **Explorador de auditoría** con filtros por tabla, operación, fecha y búsqueda de texto.
- **Paginación** de resultados.
- **Página de paridad** maestro vs réplica con detección de divergencias.
- **Gestión de usuarios del monitor** con roles (`admin` / `observador`).
- **Auditoría de accesos** (login, logout, bloqueos).
- **Auto-refresh** configurable (10s) con preservación de scroll.
- **Sistema de bloqueo** tras 5 intentos fallidos de login.
---

## 🏗️ Arquitectura

### Diagrama general

┌─────────────────────────────────────────────────────────────┐
│ NAVEGADOR (Cliente) │
│ ┌──────────────┐ ┌──────────────┐ ┌──────────────────┐ │
│ │ Web pública │ │ Dashboard │ │ Monitor de │ │
│ │ (clientes) │ │ admin │ │ observabilidad │ │
│ └──────┬───────┘ └──────┬───────┘ └────────┬─────────┘ │
└─────────┼─────────────────┼───────────────────┼─────────────┘
│ │ │
▼ ▼ ▼
┌─────────────────────────────────────────────────────────────┐
│ SERVIDOR (Apache + PHP) │
│ │
│ ┌──────────────────┐ ┌────────────────────────────────┐ │
│ │ public/ │ │ monitor/ │ │
│ │ • index.php │ │ • public/index.php (front) │ │
│ │ • productos.php │ │ • auth/ (login, logout) │ │
│ │ • carrito.php │ │ • pages/ (dashboard, auditoría)│ │
│ │ • dashboard/ │ │ • api/ (endpoints JSON) │ │
│ └────────┬─────────┘ └────────────┬───────────────────┘ │
│ │ │ │
│ └──────────┬──────────────┘ │
│ ▼ │
│ ┌────────────────────────┐ │
│ │ app/config/ │ │
│ │ • database.php (PDO) │ │
│ │ • helpers.php │ │
│ └────────────┬───────────┘ │
└──────────────────────┼──────────────────────────────────────┘
│
┌────────────┴────────────┐
▼ ▼
┌──────────────────────┐ ┌──────────────────────────┐
│ BD MAESTRA │ │ BD RÉPLICA │
│ xmotos │ │ xmotos_replica │
│ │ │ │
│ • usuarios │ │ • usuarios_replica │
│ • productos │──▶│ • productos_replica │
│ • ventas │ 21│ • ventas_replica │
│ • venta_detalle │ tr│ • venta_detalle_replica │
│ • solicitudes │ ig│ • solicitudes_replica │
│ • compras │ er│ • compras_replica │
│ • compras_detalle │ s │ • compras_detalle_replica│
│ • categorias │ │ • replica_log │
│ • proveedores │ │ • monitor_usuarios │
│ • movimientos_stock │ │ • monitor_auditoria_* │
└──────────────────────┘ └──────────────────────────┘

### Flujo de replicación
[Operación en maestro]
│
▼
[INSERT/UPDATE/DELETE en xmotos.X]
│
▼
[Trigger AFTER en xmotos.X se dispara]
│
├──▶ [1. Copiar a xmotos_replica.X_replica]
│
└──▶ [2. Registrar en xmotos_replica.replica_log]
(con datos JSON de la operación)

---

## 🛠️ Tecnologías usadas

| Capa | Tecnología | Versión |
|------|------------|---------|
| **Lenguaje** | PHP | 8.2.12 |
| **Base de datos** | MariaDB | 10.4.32 |
| **Servidor web** | Apache (XAMPP) | 2.4.58 |
| **Frontend** | Bootstrap | 5.3.2 |
| **Iconos** | Bootstrap Icons | 1.11.3 |
| **Fuentes** | Google Fonts (Inter, Montserrat) | — |
| **Gráficos** | Chart.js | 4.4.0 |
| **Control de versiones** | Git + GitHub | — |
| **IDE** | Visual Studio Code | — |

### Decisiones técnicas destacadas

- **PDO con prepared statements** en lugar de `mysqli` → mejor seguridad contra SQL injection.
- **Variables de entorno** (`.env`) con parser propio → sin dependencias externas (no Composer).
- **MVC manual** sin framework → demuestra comprensión de los fundamentos.
- **Front controller** en el monitor → URLs limpias y protección centralizada.
- **Separación de dominios de autenticación** → el monitor tiene su propia tabla de usuarios.
- **Data attributes (`data-*`)** para pasar datos de PHP a JS → compatible con CSP.
- **Patrón Repository** en el monitor → separación de lógica de datos y presentación.
---

## 📋 Requisitos previos

- **PHP 8.2+** con extensiones: `pdo_mysql`, `mysqli`, `mbstring`, `openssl`.
- **MariaDB 10.4+** o **MySQL 8.0+**.
- **Apache 2.4+** (o Nginx) con `mod_rewrite` habilitado.
- **XAMPP** (recomendado para desarrollo local).

---

## 🚀 Instalación

### 1. Clonar el repositorio

```bash

# ============ Aplicación ============
APP_ENV=local
APP_DEBUG=true

# ============ Base de datos maestra ============
DB_HOST=tu_host
DB_PORT=3306
DB_NAME=tu_base_de_datos
DB_USER=tu_usuario
DB_PASS=tu_contraseña
DB_CHARSET=utf8mb4

# Base maestra (reemplaza con tus credenciales y ruta local)
mysql -h TU_HOST -P TU_PUERTO -u TU_USUARIO -p TU_BASE_DE_DATOS < ruta/a/tu/estructura_xmotos.sql

# Base réplica (reemplaza con tus credenciales)
mysql -h TU_HOST -P TU_PUERTO -u TU_USUARIO -p TU_BASE_DE_DATOS_REPLICA < ruta/a/tu/estructura_replica.sql

# Triggers (ejecutar en la base maestra)
mysql -h TU_HOST -P TU_PUERTO -u TU_USUARIO -p TU_BASE_DE_DATOS < ruta/a/tu/01_usuarios.sql
mysql -h TU_HOST -P TU_PUERTO -u TU_USUARIO -p TU_BASE_DE_DATOS < ruta/a/tu/02_productos.sql
mysql -h TU_HOST -P TU_PUERTO -u TU_USUARIO -p TU_BASE_DE_DATOS < ruta/a/tu/03_ventas.sql
mysql -h TU_HOST -P TU_PUERTO -u TU_USUARIO -p TU_BASE_DE_DATOS < ruta/a/tu/04_venta_detalle.sql
mysql -h TU_HOST -P TU_PUERTO -u TU_USUARIO -p TU_BASE_DE_DATOS < ruta/a/tu/05_solicitudes.sql
mysql -h TU_HOST -P TU_PUERTO -u TU_USUARIO -p TU_BASE_DE_DATOS < ruta/a/tu/06_compras.sql
mysql -h TU_HOST -P TU_PUERTO -u TU_USUARIO -p TU_BASE_DE_DATOS < ruta/a/tu/07_compras_detalle.sql

# Seed inicial (ejecutar en la base réplica)
mysql -h TU_HOST -P TU_PUERTO -u TU_USUARIO -p TU_BASE_DE_DATOS_REPLICA < ruta/a/tu/seed_inicial.sql

# Usuario del monitor
mysql -h TU_HOST -P TU_PUERTO -u TU_USUARIO -p TU_BASE_DE_DATOS_REPLICA < ruta/a/tu/seed_monitor_usuario.sql
---

## Parte 6 — Uso

```markdown
---

## 💻 Uso

### Acceso al monitor

1. Abre `http://localhost/xmotos/monitor/public/`
2. Ingresa con las credenciales del administrador:
   - **Email:** `admin@xmotos.com`
   - **Password:** (la que definiste en el setup)

### Funcionalidades del monitor

#### 📊 Dashboard
- **4 tarjetas KPI:** tablas replicadas, total de operaciones, actividad reciente, distribución.
- **Gráfico de barras** con operaciones por hora (últimas 24h).
- **Últimas 10 operaciones** con badges por tipo.
- **Auto-refresh** cada 10 segundos.

#### 📜 Auditoría
- **Filtros combinables:** tabla, operación, rango de fechas, búsqueda de texto.
- **Paginación** de resultados (25 por página).
- **URLs compartibles** (los filtros quedan en la URL).

#### 🔄 Paridad
- Comparación **detallada** maestro vs réplica.
- Columna **Diferencia** que muestra divergencias.
- Estado general con semáforo.

#### 👥 Usuarios
- Crear usuarios (rol `admin` o `observador`).
- Editar rol y estado.
- Desbloquear usuarios bloqueados por intentos fallidos.
- **Solo accesible para admins.**

### Probar la replicación en vivo

1. Abre dos pestañas: una con `xmotos` (maestro) y otra con el monitor.
2. En la maestra, ejecuta:
   ```sql
   INSERT INTO usuarios (nombre_completo, email, telefono, password, rol)
   VALUES ('Test Replicación', 'test@xmotos.com', '3001234567', 'hash', 'cliente');

---

## Parte 7 — Estructura del proyecto

```markdown
---

## 📁 Estructura del proyecto
xmotos/
├── app/ # Núcleo de la aplicación
│ ├── config/
│ │ ├── database.php # Clase Database (PDO)
│ │ ├── helpers.php # Funciones auxiliares
│ │ └── auth.php # Autenticación del negocio
│ ├── controllers/
│ │ └── AuthController.php
│ └── views/
│ ├── auth/
│ └── layouts/
│ ├── header.php
│ ├── footer.php
│ ├── dashboard_head.php
│ └── cliente_header.php
│
├── monitor/ # Panel de observabilidad
│ ├── public/
│ │ ├── index.php # Front controller
│ │ ├── .htaccess
│ │ └── assets/
│ │ ├── css/monitor.css
│ │ └── js/monitor.js
│ ├── auth/
│ │ ├── login.php
│ │ ├── procesar_login.php
│ │ ├── logout.php
│ │ └── requiere_login.php
│ ├── pages/
│ │ ├── dashboard.php
│ │ ├── auditoria.php
│ │ ├── paridad.php
│ │ └── usuarios.php
│ ├── api/
│ │ ├── metricas.php
│ │ └── paridad.php
│ ├── includes/
│ │ ├── bootstrap.php
│ │ ├── funciones.php
│ │ ├── repositorio.php
│ │ ├── header.php
│ │ └── footer.php
│ └── config/
│ └── config.php
│
├── public/ # Web pública
│ ├── index.php
│ ├── productos.php
│ ├── carrito_agregar.php
│ ├── favorito_agregar.php
│ ├── login.php
│ ├── logout.php
│ ├── registro.php
│ ├── cliente/ # Panel del cliente
│ ├── dashboard/ # Panel admin/vendedor
│ ├── css/
│ │ ├── bootstrap.min.css
│ │ └── xmotos.css
│ ├── js/
│ │ ├── bootstrap.bundle.min.js
│ │ └── xmotos.js
│ ├── img/
│ └── uploads/
│
├── sql/ # Scripts SQL
│ ├── 01_estructura_xmotos.sql
│ ├── 02_estructura_replica.sql
│ ├── 03_triggers.sql
│ └── 04_seed_inicial.sql
│
├── docs/ # Documentación
│ ├── Documentacion_Formal_XMOTOS.docx
│ ├── Presentacion_Sustentacion_XMOTOS.pptx
│ └── XMOTOS.pdf
│
├── .env # Variables de entorno (no commitear)
├── .htaccess # Rewrite rules
├── .gitignore
└── README.md

---

## 🗄️ Esquema de replicación

### Tablas replicadas

| Tabla maestra | Tabla réplica | Registros actuales |
|---------------|---------------|--------------------|
| `usuarios` | `usuarios_replica` | 10 |
| `productos` | `productos_replica` | 30 |
| `ventas` | `ventas_replica` | 8 |
| `venta_detalle` | `venta_detalle_replica` | 8 |
| `solicitudes` | `solicitudes_replica` | 3 |
| `compras` | `compras_replica` | 5 |
| `compras_detalle` | `compras_detalle_replica` | 6 |

### Tablas administrativas de la réplica

- **`replica_log`** — Bitácora de todas las operaciones replicadas.
- **`monitor_usuarios`** — Usuarios del panel de observabilidad (dominio separado).
- **`monitor_auditoria_accesos`** — Registro de intentos de login/logout.

---

## 🔒 Seguridad

El proyecto implementa **defensa en profundidad** con múltiples capas:

| Capa | Medida |
|------|--------|
| **Contraseñas** | `password_hash()` con `PASSWORD_DEFAULT` (bcrypt) |
| **Sesiones** | Regeneración de ID tras login (`session_regenerate_id(true)`) |
| **Bloqueo** | Tras 5 intentos fallidos → 15 min de bloqueo |
| **SQL Injection** | Prepared statements en el 100% de las consultas |
| **XSS** | Función `e()` con `htmlspecialchars(ENT_QUOTES)` |
| **CSRF** | Tokens en formularios sensibles (futuro) |
| **Separación de dominios** | `xmotos.usuarios` ≠ `xmotos_replica.monitor_usuarios` |
| **Auditoría** | Registro de accesos con IP y User-Agent |
| **.htaccess** | Denegación de acceso a carpetas sensibles |

### Justificación de decisiones

- **¿Por qué prepared statements?** Impiden que un atacante inyecte SQL incluso si controla un parámetro.
- **¿Por qué password_hash?** Las contraseñas nunca se almacenan en texto plano. bcrypt es lento por diseño → resistente a fuerza bruta.
- **¿Por qué regenerar ID de sesión?** Previene ataques de **session fixation**.
- **¿Por qué dominios separados?** Un compromiso en la cuenta de un cliente NO da acceso al monitor.

---

## 👤 Autor

**[Ingrid Johanna Hernández Niño]**
- Estudiante de Ingeniería de Sistemas
- Curso: Sistemas Distribuidos
- [ing.ingridj.hernandez@gmail.com]
- [GitHub](https://github.com/Faresdev-gif)

---

## 📄 Licencia

Este proyecto se distribuye bajo la licencia **MIT**. Ver el archivo `LICENSE` para más información.

---

## 🙏 Agradecimientos

- **Dios Padre, Hijo y Espíritu Santo
- **Comunidad de PHP y MariaDB** por la documentación exhaustiva.
- **Bootstrap** y **Chart.js** por las herramientas de UI.
- **Asistencia técnica con IA** durante el desarrollo y debugging.

---

## 🔮 Trabajo futuro

- [ ] **Autenticación de dos factores (2FA)** para el monitor.
- [ ] **Tokens CSRF** en formularios sensibles.
- [ ] **WebSockets** para reemplazar el auto-refresh por push en tiempo real.
- [ ] **Docker** para despliegue reproducible.
- [ ] **CI/CD** con GitHub Actions.
- [ ] **Monitoreo con Prometheus + Grafana** para métricas de rendimiento.
- [ ] **Tests automatizados** con PHPUnit.
- [ ] **Paginación AJAX** en el explorador de auditoría.
- [ ] **Exportación a CSV** del log de operaciones.

---

## 📚 Referencias

- [MySQL Reference Manual — Triggers](https://dev.mysql.com/doc/refman/8.0/en/triggers.html)
- [MariaDB Documentation — Triggers](https://mariadb.com/kb/en/triggers/)
- [PHP Manual — PDO](https://www.php.net/manual/en/book.pdo.php)
- [Bootstrap 5 Documentation](https://getbootstrap.com/docs/5.3/)
- [Chart.js Documentation](https://www.chartjs.org/docs/latest/)
- [Teorema CAP — Wikipedia](https://es.wikipedia.org/wiki/Teorema_CAP)

---

**Hecho con 💚 en Colombia · 2026**

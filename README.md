# 📬 Laravel Inbox Messaging System

> A modern, RESTful messaging system built with Laravel 12 and JWT authentication. This project implements a complete inbox-style messaging platform with thread management, real-time notifications, and comprehensive API documentation.

<p align="center">
<a href="#"><img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel" alt="Laravel 12"></a>
<a href="#"><img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php" alt="PHP 8.2+"></a>
<a href="#"><img src="https://img.shields.io/badge/JWT-Auth-000000?style=for-the-badge&logo=json-web-tokens" alt="JWT"></a>
<a href="#"><img src="https://img.shields.io/badge/License-MIT-green?style=for-the-badge" alt="License"></a>
</p>

---

## 📑 Tabla de Contenidos

- [Características](#-características)
- [Requisitos](#-requisitos)
- [Instalación](#-instalación)
- [Configuración](#-configuración)
- [Uso de la API](#-uso-de-la-api)
- [Estructura del Proyecto](#-estructura-del-proyecto)
- [Desarrollo](#-desarrollo)
- [Testing](#-testing)
- [Despliegue](#-despliegue)
- [Construido con](#-construido-con)
- [Contribución](#-contribución)
- [Licencia](#-licencia)

---

## ✨ Características

- **🔐 Autenticación JWT**: Sistema de autenticación seguro con tokens JWT (tymon/jwt-auth)
- **💬 Gestión de Conversaciones**: Creación y administración de threads de mensajes
- **📨 Mensajería en Tiempo Real**: Envío y recepción de mensajes entre usuarios
- **🔔 Sistema de Notificaciones**: Notificaciones automáticas para nuevos mensajes
- **🔍 Búsqueda y Filtrado**: Filtros avanzados para threads y mensajes
- **📄 Paginación Optimizada**: Respuestas paginadas para mejor rendimiento
- **📚 Documentación Swagger**: API completamente documentada con OpenAPI/Swagger
- **✅ Testing Completo**: Suite de pruebas automatizadas con PHPUnit
- **🎯 API RESTful**: Arquitectura REST siguiendo mejores prácticas
- **🛡️ Políticas de Autorización**: Control granular de permisos por recurso

---

## 🔧 Requisitos

### Servidor

- **PHP**: 8.2 o superior
- **Composer**: 2.0 o superior
- **Base de Datos**: MySQL 8.0+ / PostgreSQL 13+ / SQLite 3.8+
- **Servidor Web**: Apache 2.4+ / Nginx 1.18+

### Extensiones PHP Requeridas

```bash
BCMath
Ctype
Fileinfo
JSON
Mbstring
OpenSSL
PDO
Tokenizer
XML
```

### Herramientas de Desarrollo (Opcional)

- **Node.js**: 18+ (para assets frontend si aplica)
- **Git**: Para control de versiones
- **Postman/Insomnia**: Para pruebas de API

---

## 📦 Instalación

### 1. Clonar el Repositorio

```bash
git clone https://github.com/tu-usuario/laravel-inbox-messaging.git
cd laravel-inbox-messaging
```

### 2. Instalar Dependencias

```bash
composer install
```

### 3. Configurar Variables de Entorno

```bash
# Copiar archivo de ejemplo
cp .env.example .env

# Generar clave de aplicación
php artisan key:generate

# Generar clave JWT
php artisan jwt:secret
```

### 4. Configurar Base de Datos

Edita el archivo `.env` con tus credenciales de base de datos:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inbox_messaging
DB_USERNAME=root
DB_PASSWORD=tu_contraseña
```

### 5. Ejecutar Migraciones y Seeders

```bash
# Ejecutar migraciones
php artisan migrate

# Ejecutar seeders (opcional - crea usuarios de prueba)
php artisan db:seed
```

### 6. Generar Documentación Swagger

```bash
php artisan l5-swagger:generate
```

### 7. Iniciar Servidor de Desarrollo

```bash
php artisan serve
```

La aplicación estará disponible en: `http://localhost:8000`

---

## ⚙️ Configuración

### Variables de Entorno Importantes

#### Aplicación

```env
APP_NAME="Laravel Inbox Messaging"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
```

#### Base de Datos

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inbox_messaging
DB_USERNAME=root
DB_PASSWORD=
```

#### JWT Authentication

```env
JWT_SECRET=tu_clave_secreta_jwt
JWT_TTL=60                    # Tiempo de vida del token (minutos)
JWT_REFRESH_TTL=20160         # Tiempo de vida del refresh token (14 días)
JWT_ALGO=HS256                # Algoritmo de encriptación
JWT_BLACKLIST_ENABLED=true    # Habilitar blacklist de tokens
JWT_BLACKLIST_GRACE_PERIOD=30 # Período de gracia (segundos)
```

#### Correo Electrónico (Notificaciones)

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=tu_username
MAIL_PASSWORD=tu_password
MAIL_FROM_ADDRESS="noreply@inbox.com"
MAIL_FROM_NAME="${APP_NAME}"
```

#### Colas (Opcional)

```env
QUEUE_CONNECTION=database
```

---

## 🚀 Uso de la API

### Base URL

```
http://localhost:8000/api
```

### Documentación Interactiva

Accede a la documentación Swagger en:

```
http://localhost:8000/api/documentation
```

### Autenticación

#### 1. Login - Obtener Token JWT

**Endpoint:** `POST /api/login`

**Request:**
```json
{
  "email": "user@example.com",
  "password": "password"
}
```

**Response:**
```json
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "token_type": "bearer",
  "expires_in": 3600
}
```

#### 2. Obtener Usuario Actual

**Endpoint:** `GET /api/user`

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "created_at": "2024-01-15T10:30:00.000000Z"
  }
}
```

#### 3. Logout

**Endpoint:** `POST /api/logout`

**Headers:**
```
Authorization: Bearer {token}
```

#### 4. Refresh Token

**Endpoint:** `POST /api/refresh`

**Headers:**
```
Authorization: Bearer {token}
```

---

### Threads (Conversaciones)

#### 1. Listar Threads

**Endpoint:** `GET /api/threads`

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `page` (opcional): Número de página (default: 1)
- `per_page` (opcional): Resultados por página (default: 15)

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "subject": "Consulta sobre producto",
      "participants": [
        {
          "id": 1,
          "name": "John Doe",
          "email": "john@example.com"
        },
        {
          "id": 2,
          "name": "Jane Smith",
          "email": "jane@example.com"
        }
      ],
      "latest_message": {
        "id": 5,
        "body": "Gracias por tu respuesta",
        "sender": {
          "id": 2,
          "name": "Jane Smith"
        },
        "created_at": "2024-01-15T14:30:00.000000Z"
      },
      "unread_count": 2,
      "created_at": "2024-01-15T10:00:00.000000Z",
      "updated_at": "2024-01-15T14:30:00.000000Z"
    }
  ],
  "links": {
    "first": "http://localhost:8000/api/threads?page=1",
    "last": "http://localhost:8000/api/threads?page=3",
    "prev": null,
    "next": "http://localhost:8000/api/threads?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 3,
    "per_page": 15,
    "to": 15,
    "total": 42
  }
}
```

#### 2. Ver Thread Específico

**Endpoint:** `GET /api/threads/{id}`

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "data": {
    "id": 1,
    "subject": "Consulta sobre producto",
    "participants": [...],
    "messages": {
      "data": [
        {
          "id": 1,
          "body": "Hola, tengo una consulta",
          "sender": {
            "id": 1,
            "name": "John Doe"
          },
          "is_read": true,
          "created_at": "2024-01-15T10:00:00.000000Z"
        }
      ],
      "links": {...},
      "meta": {...}
    },
    "created_at": "2024-01-15T10:00:00.000000Z"
  }
}
```

#### 3. Crear Nuevo Thread

**Endpoint:** `POST /api/threads`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request:**
```json
{
  "subject": "Nueva consulta",
  "participant_ids": [2, 3],
  "message": "Este es el primer mensaje del thread"
}
```

**Response:**
```json
{
  "data": {
    "id": 10,
    "subject": "Nueva consulta",
    "participants": [...],
    "latest_message": {...},
    "created_at": "2024-01-15T15:00:00.000000Z"
  }
}
```

---

### Messages (Mensajes)

#### 1. Enviar Mensaje en Thread

**Endpoint:** `POST /api/threads/{thread_id}/messages`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request:**
```json
{
  "body": "Este es mi mensaje"
}
```

**Response:**
```json
{
  "data": {
    "id": 15,
    "body": "Este es mi mensaje",
    "sender": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "is_read": false,
    "created_at": "2024-01-15T15:30:00.000000Z"
  }
}
```

---

### Códigos de Estado HTTP

| Código | Descripción |
|--------|-------------|
| `200` | OK - Solicitud exitosa |
| `201` | Created - Recurso creado exitosamente |
| `400` | Bad Request - Datos de entrada inválidos |
| `401` | Unauthorized - Token inválido o expirado |
| `403` | Forbidden - Sin permisos para acceder al recurso |
| `404` | Not Found - Recurso no encontrado |
| `422` | Unprocessable Entity - Errores de validación |
| `500` | Internal Server Error - Error del servidor |

---

## 📁 Estructura del Proyecto

```
laravel-inbox-messaging/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php       # Autenticación JWT
│   │   │   ├── ThreadController.php     # Gestión de threads
│   │   │   └── MessageController.php    # Gestión de mensajes
│   │   ├── Requests/
│   │   │   ├── StoreThreadRequest.php   # Validación crear thread
│   │   │   └── StoreMessageRequest.php  # Validación crear mensaje
│   │   └── Resources/
│   │       ├── UserResource.php         # Transformador de usuarios
│   │       ├── ThreadResource.php       # Transformador de threads
│   │       └── MessageResource.php      # Transformador de mensajes
│   ├── Models/
│   │   ├── User.php                     # Modelo de usuario
│   │   ├── Thread.php                   # Modelo de thread
│   │   ├── Message.php                  # Modelo de mensaje
│   │   └── Notification.php             # Modelo de notificación
│   └── Policies/
│       ├── ThreadPolicy.php             # Políticas de autorización threads
│       └── MessagePolicy.php            # Políticas de autorización mensajes
├── config/
│   ├── auth.php                         # Configuración de autenticación
│   ├── jwt.php                          # Configuración JWT
│   └── l5-swagger.php                   # Configuración Swagger
├── database/
│   ├── factories/
│   │   ├── UserFactory.php              # Factory de usuarios
│   │   ├── ThreadFactory.php            # Factory de threads
│   │   └── MessageFactory.php           # Factory de mensajes
│   ├── migrations/
│   │   ├── 2024_01_01_000000_create_users_table.php
│   │   ├── 2024_01_02_000000_create_threads_table.php
│   │   ├── 2024_01_03_000000_create_messages_table.php
│   │   ├── 2024_01_04_000000_create_thread_user_table.php
│   │   └── 2024_01_05_000000_create_notifications_table.php
│   └── seeders/
│       ├── DatabaseSeeder.php           # Seeder principal
│       └── UserSeeder.php               # Seeder de usuarios
├── routes/
│   ├── api.php                          # Rutas de la API
│   └── web.php                          # Rutas web
├── tests/
│   ├── Feature/
│   │   ├── AuthTest.php                 # Tests de autenticación
│   │   ├── ThreadTest.php               # Tests de threads
│   │   └── MessageTest.php              # Tests de mensajes
│   └── TestCase.php                     # Clase base de tests
├── storage/
│   └── api-docs/
│       └── api-docs.json                # Documentación OpenAPI generada
├── .env.example                         # Ejemplo de variables de entorno
├── composer.json                        # Dependencias PHP
├── phpunit.xml                          # Configuración PHPUnit
└── README.md                            # Este archivo
```

---

## 💻 Desarrollo

### Configuración del Entorno de Desarrollo

1. **Clonar el repositorio**
   ```bash
   git clone https://github.com/tu-usuario/laravel-inbox-messaging.git
   cd laravel-inbox-messaging
   ```

2. **Instalar dependencias**
   ```bash
   composer install
   ```

3. **Configurar entorno**
   ```bash
   cp .env.example .env
   php artisan key:generate
   php artisan jwt:secret
   ```

4. **Configurar base de datos**
   - Editar `.env` con credenciales de base de datos
   - Ejecutar migraciones: `php artisan migrate --seed`

5. **Iniciar servidor de desarrollo**
   ```bash
   php artisan serve
   ```

### Convenciones de Código

Este proyecto sigue las siguientes convenciones:

- **PSR-12**: Estándar de estilo de código PHP
- **Nombres en Inglés**: Variables, métodos, clases y comentarios
- **Conventional Commits**: Formato de mensajes de commit
  ```
  feat: add user authentication
  fix: resolve thread pagination issue
  docs: update API documentation
  test: add message controller tests
  ```

### Comandos Útiles

```bash
# Limpiar caché de configuración
php artisan config:clear

# Limpiar caché de rutas
php artisan route:clear

# Listar todas las rutas
php artisan route:list

# Generar documentación Swagger
php artisan l5-swagger:generate

# Ejecutar migraciones
php artisan migrate

# Rollback última migración
php artisan migrate:rollback

# Refrescar base de datos con seeders
php artisan migrate:fresh --seed

# Crear nuevo controlador
php artisan make:controller NombreController

# Crear nuevo modelo con migración
php artisan make:model NombreModelo -m

# Crear nueva policy
php artisan make:policy NombrePolicy

# Crear nuevo request
php artisan make:request NombreRequest
```

### Trabajar con Colas

Si necesitas procesar tareas en segundo plano:

```bash
# Iniciar worker de colas
php artisan queue:work

# Procesar un solo job
php artisan queue:work --once

# Ver jobs fallidos
php artisan queue:failed

# Reintentar job fallido
php artisan queue:retry {id}
```

---

## 🧪 Testing

### Ejecutar Tests

```bash
# Ejecutar todos los tests
php artisan test

# Ejecutar tests con output detallado
php artisan test --verbose

# Ejecutar un test específico
php artisan test --filter=AuthTest

# Ejecutar tests de una suite específica
php artisan test tests/Feature/ThreadTest.php
```

### Cobertura de Código

```bash
# Generar reporte de cobertura en HTML
php artisan test --coverage-html=coverage

# Ver cobertura en terminal
php artisan test --coverage
```

El reporte HTML se generará en `coverage/index.html`

### Estructura de Tests

```
tests/
├── Feature/
│   ├── AuthTest.php          # Tests de autenticación
│   ├── ThreadTest.php        # Tests de threads
│   └── MessageTest.php       # Tests de mensajes
├── Unit/                     # Tests unitarios (si aplica)
└── TestCase.php              # Clase base con helpers
```

### Escribir Tests

Ejemplo de test para crear un thread:

```php
public function test_user_can_create_thread(): void
{
    $user = User::factory()->create();
    $participants = User::factory()->count(2)->create();
    
    $response = $this->actingAs($user, 'api')
        ->postJson('/api/threads', [
            'subject' => 'Test Thread',
            'participant_ids' => $participants->pluck('id')->toArray(),
            'message' => 'First message'
        ]);
    
    $response->assertStatus(201)
        ->assertJsonStructure([
            'data' => [
                'id',
                'subject',
                'participants',
                'latest_message'
            ]
        ]);
}
```

---

## 🚢 Despliegue

### Requisitos de Producción

- PHP 8.2+
- Composer
- Base de datos (MySQL/PostgreSQL)
- Servidor web (Nginx/Apache)
- Supervisor (para colas, opcional)
- SSL/TLS (recomendado)

### Pasos para Despliegue

#### 1. Preparar el Servidor

```bash
# Actualizar sistema
sudo apt update && sudo apt upgrade -y

# Instalar PHP y extensiones
sudo apt install php8.2 php8.2-fpm php8.2-mysql php8.2-mbstring \
  php8.2-xml php8.2-bcmath php8.2-curl -y

# Instalar Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Instalar MySQL
sudo apt install mysql-server -y
```

#### 2. Clonar y Configurar Aplicación

```bash
# Clonar repositorio
cd /var/www
git clone https://github.com/tu-usuario/laravel-inbox-messaging.git
cd laravel-inbox-messaging

# Instalar dependencias (sin dev)
composer install --optimize-autoloader --no-dev

# Configurar permisos
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

#### 3. Configurar Variables de Entorno

```bash
# Copiar y editar .env
cp .env.example .env
nano .env
```

Configuración de producción:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inbox_production
DB_USERNAME=inbox_user
DB_PASSWORD=contraseña_segura

QUEUE_CONNECTION=database
CACHE_STORE=redis
SESSION_DRIVER=redis
```

#### 4. Optimizar Aplicación

```bash
# Generar key
php artisan key:generate

# Generar JWT secret
php artisan jwt:secret

# Ejecutar migraciones
php artisan migrate --force

# Optimizar autoloader
composer dump-autoload --optimize

# Cachear configuración
php artisan config:cache

# Cachear rutas
php artisan route:cache

# Cachear vistas
php artisan view:cache

# Generar documentación
php artisan l5-swagger:generate
```

#### 5. Configurar Nginx

```nginx
server {
    listen 80;
    server_name tu-dominio.com;
    root /var/www/laravel-inbox-messaging/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

#### 6. Configurar SSL con Let's Encrypt

```bash
# Instalar Certbot
sudo apt install certbot python3-certbot-nginx -y

# Obtener certificado
sudo certbot --nginx -d tu-dominio.com
```

#### 7. Configurar Supervisor (Colas)

```bash
# Instalar Supervisor
sudo apt install supervisor -y

# Crear configuración
sudo nano /etc/supervisor/conf.d/laravel-worker.conf
```

Contenido:

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/laravel-inbox-messaging/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/laravel-inbox-messaging/storage/logs/worker.log
```

```bash
# Recargar Supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

#### 8. Configurar Tarea Programada (Cron)

```bash
# Editar crontab
crontab -e
```

Agregar:

```
* * * * * cd /var/www/laravel-inbox-messaging && php artisan schedule:run >> /dev/null 2>&1
```

### Monitoreo y Mantenimiento

```bash
# Ver logs de aplicación
tail -f storage/logs/laravel.log

# Ver logs de Nginx
sudo tail -f /var/log/nginx/error.log

# Ver estado de workers
sudo supervisorctl status

# Reiniciar workers después de deploy
sudo supervisorctl restart laravel-worker:*
```

---

## 🛠️ Construido con

### Backend

- **[Laravel 12](https://laravel.com)** - Framework PHP
- **[tymon/jwt-auth](https://github.com/tymondesigns/jwt-auth)** - Autenticación JWT
- **[darkaonline/l5-swagger](https://github.com/DarkaOnLine/L5-Swagger)** - Documentación OpenAPI/Swagger

### Base de Datos

- **MySQL 8.0+** / **PostgreSQL 13+** / **SQLite 3.8+**

### Testing

- **[PHPUnit](https://phpunit.de/)** - Framework de testing

### Herramientas de Desarrollo

- **[Laravel Pint](https://laravel.com/docs/pint)** - Formateador de código PHP
- **[Laravel Sail](https://laravel.com/docs/sail)** - Entorno de desarrollo Docker (opcional)

---

## 🤖 Uso de IA en el Proyecto

Este proyecto fue desarrollado con la asistencia de herramientas de Inteligencia Artificial, específicamente utilizando **Windsurf IDE** con el agente de IA **Gravity**. A continuación se detalla cómo se utilizó la IA en el desarrollo:

### Herramientas Utilizadas

- **[Windsurf IDE](https://codeium.com/windsurf)**: Editor de código con capacidades de IA integradas
- **Gravity AI Agent**: Asistente de programación avanzado de Windsurf

### Áreas de Asistencia

La IA fue utilizada en las siguientes fases del proyecto:

#### 1. **Configuración Inicial del Proyecto**
- Configuración de Laravel 12 con PHP 8.2
- Instalación y configuración de JWT Authentication (tymon/jwt-auth)
- Setup de L5-Swagger para documentación OpenAPI

#### 2. **Diseño y Modelado de Base de Datos**
- Creación de migraciones para las tablas: `users`, `threads`, `messages`, `thread_user`, `notifications`
- Definición de relaciones Eloquent entre modelos
- Implementación de índices y claves foráneas

#### 3. **Desarrollo de la API**
- Implementación de controladores: `AuthController`, `ThreadController`, `MessageController`
- Creación de Form Requests para validación: `StoreThreadRequest`, `StoreMessageRequest`
- Desarrollo de API Resources para transformación de datos: `UserResource`, `ThreadResource`, `MessageResource`

#### 4. **Autenticación y Autorización**
- Configuración de JWT guards y middleware
- Implementación de políticas de autorización: `ThreadPolicy`, `MessagePolicy`
- Sistema de permisos granular para acceso a recursos

#### 5. **Testing Automatizado**
- Creación de suite completa de tests con PHPUnit
- Tests de Feature para: Autenticación, Threads, Mensajes
- Factories para generación de datos de prueba
- Configuración de base de datos en memoria para tests

#### 6. **Documentación**
- Generación de anotaciones Swagger/OpenAPI
- Creación de este README.md completo
- Documentación de endpoints de API con ejemplos

### Prompt Original del Proyecto

El desarrollo de este proyecto comenzó con el siguiente contexto:

```
# Contexto del Proyecto
Estoy desarrollando una prueba técnica de Laravel: un sistema de mensajería tipo Inbox. 
Ya completé las Fases 1-5 (configuración, modelos, autenticación, controladores, pruebas).

# Fase Actual: FASE 6 - Documentación en README.md

## Objetivo
Crear un README.md completo que sirva como documentación principal del proyecto, incluyendo:
- Descripción general del sistema
- Guías de instalación y configuración
- Documentación de la API
- Guías de desarrollo
- Información sobre el uso de IA en el proyecto
```

### Metodología de Trabajo con IA

1. **Planificación**: Se definieron los requisitos y estructura del proyecto
2. **Iteración**: Desarrollo incremental con revisión continua
3. **Validación**: Testing automatizado para garantizar calidad
4. **Documentación**: Generación de documentación completa y actualizada

### Transparencia

Este proyecto demuestra cómo la IA puede ser una herramienta poderosa para:
- Acelerar el desarrollo de aplicaciones
- Mantener mejores prácticas y estándares de código
- Generar documentación completa y actualizada
- Implementar testing exhaustivo desde el inicio

**Nota**: Aunque se utilizó IA como asistente, todas las decisiones de arquitectura, diseño y lógica de negocio fueron supervisadas y validadas por el desarrollador.

---

## 📄 Licencia

Este proyecto está licenciado bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

```
MIT License

Copyright (c) 2024 Laravel Inbox Messaging

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

---

<p align="center">Hecho con ❤️ usando Laravel y 🤖 Windsurf AI</p>

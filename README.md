# 🍽️ Mi Ciudad Gourmet - Directorio de Restaurantes

[![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)](https://getbootstrap.com)
[![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Google Gemini](https://img.shields.io/badge/Google_Gemini-4285F4?style=for-the-badge&logo=google&logoColor=white)](https://gemini.google.com)

Plataforma web completa para descubrir, valorar y gestionar restaurantes locales con sistema de reseñas, autenticación de usuarios, gestión de imágenes y asistente virtual impulsado por IA.

---

## 🌟 Características principales

- ✅ **Autenticación completa** - Registro, login y logout seguro
- 🏪 **Gestión de restaurantes** - CRUD completo con categorías, descripciones detalladas y fotos
- ⭐ **Sistema de reseñas** - Calificaciones de 1-5 estrellas con comentarios
- 📸 **Subida de imágenes** - Fotos para cada restaurante
- 🏷️ **Categorización** - Organización por tipos de cocina
- 🔒 **Autorización** - Solo el dueño puede editar sus restaurantes
- 📱 **Diseño responsive** - Interfaz moderna con Bootstrap 5
- 🚀 **API RESTful** - Endpoints completos para integración externa
- 🤖 **Chatbot IA** - Asistente virtual con Google Gemini para consultas sobre restaurantes
- 📝 **Descripciones detalladas** - Campo de descripción para información completa de restaurantes

---

## 🆕 Cambios Importantes en Laravel 11

Laravel 11 introduce cambios significativos que afectan a MiCiudadGourmet:

### Configuración de Rutas API

En Laravel 11, las rutas API no vienen configuradas por defecto. **Debes instalarlas manualmente**:

```bash
php artisan install:api
```

Este comando:
- Crea el archivo `routes/api.php`
- Instala Laravel Sanctum para autenticación de API
- Configura `bootstrap/app.php` con el prefijo API

### Nueva Estructura `bootstrap/app.php`

La configuración principal ahora se realiza en `bootstrap/app.php`:

```php
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: '/api',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Configuración de middleware
        $middleware->validateCsrfTokens(except: [
            '/api/chatbot/*',
        ]);
    })
    ->create();
```

---

## 🔧 Solución de Problemas Comunes

### Error 404 en Rutas API del Chatbot

**Síntoma**: `GET /api/chatbot/history 404 (Not Found)`

**Solución**:
1. Ejecutar `php artisan install:api`
2. Verificar que `bootstrap/app.php` incluye la línea API
3. Limpiar caché: `php artisan route:clear && php artisan config:clear`

### Error 500 con Modelo no Encontrado

**Síntoma**: `Class "App\Http\Controllers\Api\ChatConversation" not found`

**Solución**:
1. Verificar imports en el controlador:
   ```php
   use App\Models\ChatConversation;
   use App\Models\ChatMessage;
   ```
2. Verificar que la declaración `namespace` sea la primera línea del archivo

### Error SQL: Unknown column 'description'

**Síntoma**: `SQLSTATE[42S22]: Column not found: 1054 Unknown column 'description'`

**Solución**:
1. Ejecutar la migración para añadir el campo:
   ```bash
   php artisan migrate
   ```
2. Si no existe la migración, crearla:
   ```bash
   php artisan make:migration add_description_to_restaurants_table --table=restaurants
   ```

### Problemas de CSRF Token

**Síntoma**: Error de token CSRF en formularios o API

**Solución**:
1. Incluir meta tag en layouts: `<meta name="csrf-token" content="{{ csrf_token() }}">`
2. Configurar excepción en `bootstrap/app.php` para rutas API
3. Para SPA, llamar a `sanctum/csrf-cookie` antes de peticiones autenticadas

---

## 🚀 Instalación rápida

### 📋 Requisitos previos

Asegúrate de tener instalado:

- **PHP 8.2+** con extensiones: mbstring, openssl, pdo, tokenizer, xml, curl, zip, bcmath, gd
- **Composer 2.5+**
- **MySQL 8.0+** o MariaDB 10.3+
- **Node.js 18+** y npm
- **Git**

### 🔧 Pasos de instalación

#### 1. Clonar el repositorio
```bash
git clone https://github.com/CarlotadeMiguel/MiCiudadGourmet.git
cd MiCiudadGourmet
```

#### 2. Instalar dependencias
```bash
# Dependencias de PHP
composer install

# Dependencias de JavaScript
npm install && npm run build
```

#### 3. Configurar entorno
```bash
# Copiar archivo de configuración
cp .env.example .env

# Generar clave de aplicación
php artisan key:generate
```

#### 4. Configurar base de datos

**Crear base de datos en MySQL:**
```sql
CREATE DATABASE mi_ciudad_gourmet CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

**Editar archivo `.env`:**
```env
APP_NAME="Mi Ciudad Gourmet"
APP_ENV=local
APP_KEY=base64:XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mi_ciudad_gourmet
DB_USERNAME=root
DB_PASSWORD=tu_contraseña_mysql

SESSION_DRIVER=database
SESSION_LIFETIME=120

# Google Gemini API Key para el chatbot
GEMINI_API_KEY=tu_clave_api_de_gemini
```

#### 5. Configurar el Chatbot con Google Gemini

```bash
# Instalar el paquete Gemini para Laravel
composer require google-gemini-php/laravel

# Configurar Google Gemini
php artisan gemini:install

# Instalar API para rutas en Laravel 11
php artisan install:api
```

#### 6. Ejecutar migraciones
```bash
# Ejecutar migraciones para crear las tablas
php artisan migrate

# O con datos de prueba (recomendado para desarrollo)
php artisan migrate --seed
```

#### 7. Configurar almacenamiento
```bash
# Crear enlace simbólico para las imágenes
php artisan storage:link
```

#### 8. Iniciar servidor de desarrollo
```bash
php artisan serve
```

¡Listo! Visita **http://localhost:8000** en tu navegador.

---

## 🤖 Implementación Detallada del Chatbot

### Obtener Clave API de Google Gemini

1. Visita [Google AI Studio](https://makersuite.google.com/app/apikey)
2. Genera una nueva clave API
3. Añade la clave en tu archivo `.env`:
   ```env
   GEMINI_API_KEY=tu_clave_api_aquí
   ```

### Estructura de la Base de Datos del Chatbot

**Tabla `chat_conversations`:**
```
id - Identificador único
session_id - ID de sesión del navegador
user_id - ID del usuario (si está autenticado)
title - Título de la conversación
created_at, updated_at
```

**Tabla `chat_messages`:**
```
id - Identificador único
conversation_id - ID de la conversación
role - Rol del mensaje ('user' o 'assistant')
content - Contenido del mensaje
context_data - Datos de contexto en JSON
created_at, updated_at
```

### Servicios del Chatbot

- **ContextService**: Proporciona información relevante sobre restaurantes según el mensaje
- **ChatbotService**: Procesa mensajes, genera respuestas con Gemini y guarda historial
- **ChatbotController**: Gestiona las rutas API del chatbot

### Rutas API del Chatbot

```php
// routes/api.php
Route::prefix('chatbot')->group(function () {
    Route::post('/send', [ChatbotController::class, 'sendMessage']);
    Route::get('/history', [ChatbotController::class, 'getConversationHistory']);
});
```

### Configuración del Frontend

El chatbot está disponible en `/chatbot` y utiliza JavaScript para comunicarse con la API:

```javascript
// Enviar mensaje
fetch('/api/chatbot/send', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    body: JSON.stringify({
        message: userMessage,
        session_id: sessionId
    })
});
```

---

## 📝 Nueva Funcionalidad: Campo Description para Restaurantes

### Migración para el Campo Description

```bash
php artisan make:migration add_description_to_restaurants_table --table=restaurants
```

**Contenido de la migración:**
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->text('description')->nullable()->after('address');
        });
    }

    public function down()
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
```

### Actualización del Modelo Restaurant

```php
// app/Models/Restaurant.php
protected $fillable = [
    'name',
    'address',
    'phone',
    'description',  // Campo añadido
];
```

### Implementación en Formularios

```blade
<div class="form-floating mb-4">
    <textarea class="form-control" id="description" name="description" 
              placeholder="Descripción" style="height: 120px;">
        {{ old('description', $restaurant->description ?? '') }}
    </textarea>
    <label for="description">Descripción del Restaurante</label>
</div>
```

### Visualización en Vistas

```blade
<!-- En la vista de listado -->
@if($restaurant->description)
    <p class="card-text">{{ Str::limit($restaurant->description, 80) }}</p>
@endif

<!-- En la vista de detalle -->
@if($restaurant->description)
    <p class="mt-3">{{ $restaurant->description }}</p>
@endif
```

### Datos de Ejemplo con Descripciones

```php
// Usando Tinker: php artisan tinker
$restaurante = App\Models\Restaurant::create([
    'name' => 'La Trattoria Romana',
    'address' => 'Calle Gran Vía 25, Madrid',
    'phone' => '+34 91 123 4567',
    'description' => 'Auténtica cocina italiana en el corazón de Madrid. Nuestro chef, originario de Roma, prepara pastas caseras y pizzas en horno de leña siguiendo recetas tradicionales transmitidas por generaciones. Ambiente acogedor ideal para cenas románticas o reuniones familiares.',
    'user_id' => $user->id
]);
```

---

## 🌐 Despliegue en Producción

### AWS EC2 (Ubuntu) - Solución Completa

#### 1. Solución al Error de PHP 8.2 en Ubuntu

**Problema**: `Unable to locate package php8.2`

**Solución**:
```bash
# Actualizar sistema
sudo apt update && sudo apt upgrade -y

# Instalar software-properties-common
sudo apt install software-properties-common -y

# Añadir repositorio de Ondrej para PHP
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Instalar PHP 8.2 y extensiones (compatible con Laravel 11)
sudo apt install apache2 php8.2 php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl mysql-server composer git unzip -y

# Instalar extensiones adicionales
sudo apt install php8.2-bcmath php8.2-gd php8.2-zip php8.2-intl php8.2-opcache -y
```

#### 2. Configurar Virtual Host para Apache

```bash
sudo nano /etc/apache2/sites-available/miciudadgourmet.conf
```

```apache
<VirtualHost *:80>
    ServerName tu-dominio.com
    DocumentRoot /var/www/MiCiudadGourmet/public

    <Directory /var/www/MiCiudadGourmet/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/miciudadgourmet_error.log
    CustomLog ${APACHE_LOG_DIR}/miciudadgourmet_access.log combined
</VirtualHost>
```

#### 3. Activar sitio y configurar permisos

```bash
sudo a2ensite miciudadgourmet.conf
sudo a2dissite 000-default.conf
sudo a2enmod rewrite
sudo systemctl reload apache2

sudo chown -R www-data:www-data /var/www/MiCiudadGourmet
sudo chmod -R 755 /var/www/MiCiudadGourmet
sudo chmod -R 775 /var/www/MiCiudadGourmet/storage
sudo chmod -R 775 /var/www/MiCiudadGourmet/bootstrap/cache
```

#### 4. Configurar entorno de producción

```bash
cd /var/www/MiCiudadGourmet

# Instalar dependencias sin dev
composer install --no-dev --optimize-autoloader

# Compilar assets
npm install && npm run build

# Configurar la aplicación
php artisan storage:link
php artisan migrate --force

# Configurar el chatbot
php artisan install:api

# Optimizar para producción
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### 5. Configurar .env para producción

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio.com

# Configurar base de datos de producción
DB_HOST=tu-host-mysql
DB_DATABASE=mi_ciudad_gourmet_prod
DB_USERNAME=tu_usuario_prod
DB_PASSWORD=tu_contraseña_segura

# Configurar Google Gemini
GEMINI_API_KEY=tu_clave_api_de_gemini

# Configurar sesiones para producción
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_DOMAIN=tu-dominio.com
```

### Lista de Verificación para Despliegue

#### ✅ Pre-despliegue
- [ ] Código actualizado en repositorio
- [ ] Tests pasando
- [ ] Variables de entorno configuradas
- [ ] Credenciales de base de datos verificadas

#### ✅ Durante el despliegue
- [ ] `php artisan down` - Poner en mantenimiento
- [ ] `git pull origin main` - Actualizar código
- [ ] `composer install --no-dev --optimize-autoloader`
- [ ] `npm install && npm run build`
- [ ] `php artisan migrate --force`
- [ ] `php artisan config:cache`
- [ ] `php artisan route:cache`
- [ ] `php artisan view:cache`
- [ ] `php artisan up` - Quitar mantenimiento

#### ✅ Post-despliegue
- [ ] Verificar que el sitio carga correctamente
- [ ] Probar funcionalidades principales
- [ ] Verificar que el chatbot funciona
- [ ] Comprobar logs de errores

---

## 🧪 Crear datos de prueba con descripciones

### Usando el Seeder

```bash
php artisan db:seed --class=DatabaseSeeder
```

### Usando Tinker para datos personalizados

```bash
php artisan tinker
```

```php
// Crear restaurantes con descripciones detalladas
$user = App\Models\User::create([
    'name' => 'Carlos Restaurantero',
    'email' => 'carlos@example.com',
    'password' => bcrypt('password123')
]);

$restaurant = App\Models\Restaurant::create([
    'name' => 'El Rincón Mexicano',
    'address' => 'Calle Fuencarral 42, Madrid',
    'phone' => '+34 91 234 5678',
    'description' => 'Viaje gastronómico a México sin salir de Madrid. Ofrecemos auténticos tacos al pastor, enchiladas, guacamole preparado al momento y la mejor selección de tequilas y mezcales. Decoración colorida y festiva que transporta directamente a las calles de Ciudad de México.',
    'user_id' => $user->id
]);

// Añadir categoría
$categoria = App\Models\Category::create(['name' => 'Mexicana']);
$restaurant->categories()->attach($categoria->id);

echo "¡Restaurante con descripción creado exitosamente!";
exit
```

---

## 🛠️ Comandos útiles

| Comando | Descripción |
|---------|-------------|
| `php artisan route:list \| grep chatbot` | Verificar rutas del chatbot |
| `php artisan migrate:fresh --seed` | Recrear BD con datos de prueba |
| `php artisan tinker` | Consola interactiva de Laravel |
| `php artisan storage:link` | Crear enlace para archivos públicos |
| `php artisan optimize:clear` | Limpiar todos los cachés |
| `php artisan install:api` | Instalar rutas API (Laravel 11) |
| `tail -f storage/logs/laravel.log` | Ver logs en tiempo real |

---

## 🔐 Funcionalidades de seguridad

- **Autenticación Laravel Sanctum** para tokens de API
- **Validación CSRF** en todos los formularios (configurable por ruta)
- **Autorización basada en propietario** para operaciones CRUD
- **Validación de entrada** con Form Requests personalizados
- **Protección contra SQL injection** con Eloquent ORM
- **Hashing seguro de contraseñas** con bcrypt
- **Protección de rutas API** con middleware personalizable

---

## 📋 Funcionalidades por implementar

- [ ] Búsqueda avanzada usando el campo `description`
- [ ] Filtros por palabras clave en descripciones
- [ ] Integración con mapas (Google Maps)
- [ ] Sistema de reservas
- [ ] Múltiples idiomas
- [ ] Panel de administración avanzado
- [ ] Mejoras en el chatbot con entrenamiento personalizado
- [ ] Notificaciones push
- [ ] Exportación de datos

---

## 📞 Soporte y Troubleshooting

### Problemas comunes y soluciones:

1. **Error 404 en rutas API**: Ejecutar `php artisan install:api`
2. **Error 500 en chatbot**: Revisar logs y verificar migraciones
3. **Problemas de permisos**: Verificar ownership de `www-data`
4. **Error de CSRF**: Configurar excepciones en `bootstrap/app.php`
5. **Problemas con Gemini**: Verificar clave API y región de acceso

### Logs importantes:
- Laravel: `storage/logs/laravel.log`
- Apache: `/var/log/apache2/miciudadgourmet_error.log`
- MySQL: `/var/log/mysql/error.log`

### Contacto:
- **GitHub Issues**: [Reportar problemas](https://github.com/CarlotadeMiguel/MiCiudadGourmet/issues)
- **Documentación**: Este README
- **Logs**: Revisar siempre los logs antes de reportar

---

## 👥 Créditos

Desarrollado por **Carlota de Miguel** como proyecto final de desarrollo web con Laravel 11.

**Tecnologías utilizadas:**
- Laravel 11 con nuevo sistema de rutas API
- Google Gemini AI para chatbot inteligente
- Bootstrap 5 para diseño responsive
- MySQL para base de datos
- Laravel Sanctum para autenticación API

---

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo [LICENSE](LICENSE) para más detalles.

---

**¿Te ha gustado el proyecto?** ⭐ ¡Dale una estrella en GitHub!

**Sígueme en:** [GitHub](https://github.com/CarlotadeMiguel) | [LinkedIn](https://linkedin.com/in/carlota-de-miguel)
# 🍽️ Mi Ciudad Gourmet - Directorio de Restaurantes

[![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)](https://getbootstrap.com)
[![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)

Plataforma web completa para descubrir, valorar y gestionar restaurantes locales con sistema de reseñas, autenticación de usuarios y gestión de imágenes.

---

## 🌟 Características principales

- ✅ **Autenticación completa** - Registro, login y logout seguro
- 🏪 **Gestión de restaurantes** - CRUD completo con categorías y fotos
- ⭐ **Sistema de reseñas** - Calificaciones de 1-5 estrellas con comentarios
- 📸 **Subida de imágenes** - Fotos para cada restaurante
- 🏷️ **Categorización** - Organización por tipos de cocina
- 🔒 **Autorización** - Solo el dueño puede editar sus restaurantes
- 📱 **Diseño responsive** - Interfaz moderna con Bootstrap 5
- 🚀 **API RESTful** - Endpoints completos para integración externa

---

## 🚀 Instalación rápida

### 📋 Requisitos previos

Asegúrate de tener instalado:

- **PHP 8.1+** con extensiones: mbstring, openssl, pdo, tokenizer, xml
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
```

#### 5. Ejecutar migraciones
```bash
# Ejecutar migraciones para crear las tablas
php artisan migrate

# O con datos de prueba (recomendado para desarrollo)
php artisan migrate --seed
```

#### 6. Configurar almacenamiento
```bash
# Crear enlace simbólico para las imágenes
php artisan storage:link
```

#### 7. Iniciar servidor de desarrollo
```bash
php artisan serve
```

¡Listo! Visita **http://localhost:8000** en tu navegador.

---

## 🗄️ Estructura de la base de datos

### Tablas principales:
- **users** - Usuarios del sistema
- **restaurants** - Información de restaurantes
- **categories** - Categorías de cocina (italiana, mexicana, etc.)
- **reviews** - Reseñas y calificaciones
- **photos** - Imágenes (relación polimórfica)
- **favorites** - Restaurantes favoritos de usuarios
- **restaurant_category** - Tabla pivote muchos a muchos

### Relaciones:
- Un usuario puede tener **muchos restaurantes**
- Un restaurante puede tener **muchas reseñas** y **muchas fotos**
- Los restaurantes y categorías tienen relación **muchos a muchos**
- Un usuario **no puede reseñar su propio restaurante**

---

## 🧪 Crear datos de prueba

### Opción 1: Usar seeders (recomendado)
```bash
php artisan db:seed --class=DatabaseSeeder
```

### Opción 2: Usar Tinker paso a paso
```bash
php artisan tinker
```

```php
# Crear usuarios con roles diferentes
$admin = \App\Models\User::create([
    'name' => 'Admin Usuario',
    'email' => 'admin@miciudadgourmet.com',
    'password' => bcrypt('password123'),
    'is_admin' => true
]);

$restaurantOwner1 = \App\Models\User::create([
    'name' => 'Carlos Rodríguez',
    'email' => 'carlos@example.com',
    'password' => bcrypt('password123')
]);

$restaurantOwner2 = \App\Models\User::create([
    'name' => 'María López',
    'email' => 'maria@example.com',
    'password' => bcrypt('password123')
]);

$regularUser = \App\Models\User::create([
    'name' => 'Ana García',
    'email' => 'ana@example.com',
    'password' => bcrypt('password123')
]);

# Crear categorías de restaurantes
$categorias = [
    'Italiana' => 'Pizzas, pastas y más',
    'Mexicana' => 'Tacos, enchiladas y comida picante',
    'Japonesa' => 'Sushi, ramen y platos tradicionales',
    'Española' => 'Tapas, paella y cocina mediterránea',
    'India' => 'Curry, tandoori y sabores exóticos',
    'Vegetariana' => 'Platos sin carne y opciones veganas',
    'Mariscos' => 'Pescados y mariscos frescos',
    'Cafetería' => 'Café, pasteles y ambiente relajado'
];

$categoriasCreadas = [];
foreach ($categorias as $nombre => $descripcion) {
    $categoriasCreadas[$nombre] = \App\Models\Category::create(['name' => $nombre]);
}

# Crear restaurantes para Carlos
$restaurante1 = \App\Models\Restaurant::create([
    'name' => 'La Trattoria Romana',
    'address' => 'Calle Gran Vía 25, Madrid',
    'phone' => '+34 91 123 4567',
    'user_id' => $restaurantOwner1->id
]);

$restaurante1->categories()->attach([$categoriasCreadas['Italiana']->id]);

$restaurante2 = \App\Models\Restaurant::create([
    'name' => 'El Rincón Mexicano',
    'address' => 'Calle Fuencarral 42, Madrid',
    'phone' => '+34 91 234 5678',
    'user_id' => $restaurantOwner1->id
]);

$restaurante2->categories()->attach([$categoriasCreadas['Mexicana']->id]);

# Crear restaurantes para María
$restaurante3 = \App\Models\Restaurant::create([
    'name' => 'Sakura Sushi Bar',
    'address' => 'Calle Serrano 78, Madrid',
    'phone' => '+34 91 345 6789',
    'user_id' => $restaurantOwner2->id
]);

$restaurante3->categories()->attach([$categoriasCreadas['Japonesa']->id]);

$restaurante4 = \App\Models\Restaurant::create([
    'name' => 'La Tapería',
    'address' => 'Plaza Mayor 5, Madrid',
    'phone' => '+34 91 456 7890',
    'user_id' => $restaurantOwner2->id
]);

$restaurante4->categories()->attach([$categoriasCreadas['Española']->id]);

# Crear reseñas (Ana reseña restaurantes de Carlos y María)
$reseña1 = \App\Models\Review::create([
    'rating' => 5,
    'comment' => '¡Excelente pasta carbonara! El ambiente es muy acogedor y el servicio impecable.',
    'user_id' => $regularUser->id,
    'restaurant_id' => $restaurante1->id
]);

$reseña2 = \App\Models\Review::create([
    'rating' => 4,
    'comment' => 'Los tacos están deliciosos, aunque el servicio fue un poco lento. Volveré por la comida.',
    'user_id' => $regularUser->id,
    'restaurant_id' => $restaurante2->id
]);

$reseña3 = \App\Models\Review::create([
    'rating' => 5,
    'comment' => 'El mejor sushi que he probado en Madrid. Fresco y auténtico.',
    'user_id' => $regularUser->id,
    'restaurant_id' => $restaurante3->id
]);

# Carlos reseña el restaurante de María (pero no puede reseñar el suyo propio)
$reseña4 = \App\Models\Review::create([
    'rating' => 4,
    'comment' => 'Buenas tapas y buen ambiente. Recomendable para ir con amigos.',
    'user_id' => $restaurantOwner1->id,
    'restaurant_id' => $restaurante4->id
]);

# María reseña el restaurante de Carlos
$reseña5 = \App\Models\Review::create([
    'rating' => 3,
    'comment' => 'La comida estaba bien, pero esperaba más sabor en los platos principales.',
    'user_id' => $restaurantOwner2->id,
    'restaurant_id' => $restaurante1->id
]);

echo "¡Datos de prueba creados con éxito!";

# Salir de Tinker
exit
```

---

## ⚙️ Configuración avanzada

### Variables de entorno importantes
```env
# Configuración de correo (ejemplo con Mailtrap)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=tu_username
MAIL_PASSWORD=tu_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@miciudadgourmet.com

# Configuración de Sanctum para API
SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1,tu-dominio.com

# Configuración de archivos
FILESYSTEM_DISK=public
```

### Comandos de optimización
```bash
# Limpiar y optimizar caché
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Para desarrollo (sin caché)
php artisan optimize:clear
```

---

## 🛠️ Comandos útiles

| Comando | Descripción |
|---------|-------------|
| `php artisan migrate:fresh --seed` | Recrear base de datos con datos de prueba |
| `php artisan tinker` | Consola interactiva de Laravel |
| `php artisan make:model Producto -mfc` | Crear modelo con migración, factory y controlador |
| `php artisan storage:link` | Crear enlace para archivos públicos |
| `npm run dev` | Compilar assets en modo desarrollo |
| `npm run build` | Compilar assets para producción |

---

## 🌐 Despliegue en producción

### AWS EC2 (recomendado)

#### 1. Preparar servidor
```bash
# Actualizar sistema
sudo apt update && sudo apt upgrade -y

# Instalar Apache, PHP, MySQL
sudo apt install apache2 php8.2 php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl mysql-server composer -y

# Habilitar mod_rewrite
sudo a2enmod rewrite
```

#### 2. Configurar Virtual Host
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

#### 3. Activar sitio
```bash
sudo a2ensite miciudadgourmet.conf
sudo a2dissite 000-default.conf
sudo systemctl reload apache2
```

#### 4. Configurar permisos
```bash
sudo chown -R www-data:www-data /var/www/MiCiudadGourmet
sudo chmod -R 755 /var/www/MiCiudadGourmet
sudo chmod -R 775 /var/www/MiCiudadGourmet/storage
sudo chmod -R 775 /var/www/MiCiudadGourmet/bootstrap/cache
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
```

---

## 🔐 Funcionalidades de seguridad

- **Autenticación Laravel Sanctum** para tokens de API
- **Validación CSRF** en todos los formularios
- **Autorización basada en propietario** para operaciones CRUD
- **Validación de entrada** con Form Requests personalizados
- **Protección contra SQL injection** con Eloquent ORM
- **Hashing seguro de contraseñas** con bcrypt

---

## 🧪 Testing

```bash
# Ejecutar tests
php artisan test

# Con cobertura
php artisan test --coverage

# Tests específicos
php artisan test --filter=RestaurantTest
```

---

## 📋 Funcionalidades por implementar

- [ ] Sistema de notificaciones
- [ ] Búsqueda avanzada con filtros
- [ ] Integración con mapas (Google Maps)
- [ ] Sistema de reservas
- [ ] Múltiples idiomas
- [ ] Panel de administración avanzado
- [ ] API rate limiting
- [ ] Importación masiva de datos

---

## 🤝 Cómo contribuir

1. **Fork** el repositorio
2. Crea tu rama de feature: `git checkout -b feature/nueva-funcionalidad`
3. **Commit** tus cambios: `git commit -m 'Add: nueva funcionalidad'`
4. **Push** a la rama: `git push origin feature/nueva-funcionalidad`
5. Abre un **Pull Request**

### Estándares de código
- Seguir **PSR-12** para PHP
- Usar **nombres descriptivos** para variables y métodos
- **Comentar código complejo**
- **Tests unitarios** para nuevas funcionalidades

---

## 📞 Soporte

¿Problemas durante la instalación?

1. **Revisa los logs:** `tail -f storage/logs/laravel.log`
2. **Verifica permisos:** `ls -la storage/`
3. **Comprueba la configuración:** `php artisan config:show database`
4. **Abre un issue:** [GitHub Issues](https://github.com/CarlotadeMiguel/MiCiudadGourmet/issues)

---

## 👥 Créditos

Desarrollado por **Carlota de Miguel** como parte del proyecto final de desarrollo web.

---

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo [LICENSE](LICENSE) para más detalles.

---

**¿Te ha gustado el proyecto?** ⭐ ¡Dale una estrella en GitHub!

**Sígueme en:** [GitHub](https://github.com/CarlotadeMiguel) | [LinkedIn](https://linkedin.com/in/carlota-de-miguel)
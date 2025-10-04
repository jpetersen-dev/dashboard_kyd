# Usar la imagen oficial de PHP 8.2 con el servidor web Apache
FROM php:8.2-apache

# 1. Instalar dependencias del sistema
# Se añaden las dependencias de Composer (git, zip, unzip)
RUN apt-get update && apt-get install -y \
    libpq-dev \
    git \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# 2. Instalar la extensión de PostgreSQL
RUN docker-php-ext-install pdo pdo_pgsql

# 3. Instalar Composer (el gestor de dependencias de PHP)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 4. Establecer el directorio de trabajo
WORKDIR /var/www/html

# 5. Copiar los archivos de Composer y ejecutar la instalación
# Esto crea la carpeta /vendor dentro del contenedor
COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-dev --optimize-autoloader

# 6. Copiar el resto del código de la aplicación
COPY . .

# 7. Configurar Apache
RUN a2enmod rewrite
COPY .docker/000-default.conf /etc/apache2/sites-available/000-default.conf

# 8. Establecer permisos
RUN chown -R www-data:www-data /var/www/html

# 9. Exponer el puerto
EXPOSE 80


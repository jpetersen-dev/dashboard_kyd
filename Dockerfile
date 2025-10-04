# Usar la imagen oficial de PHP 8.2 con el servidor web Apache
FROM php:8.2-apache

# Instalar las extensiones de PHP necesarias para conectar con PostgreSQL
RUN docker-php-ext-install pdo pdo_pgsql

# Habilitar el módulo de reescritura de Apache para las URLs amigables (.htaccess)
RUN a2enmod rewrite

# Copiar los archivos de tu proyecto al directorio web del contenedor
COPY . /var/www/html/

# Establecer los permisos correctos para que Apache pueda leer los archivos
RUN chown -R www-data:www-data /var/www/html

# Exponer el puerto 80 para el tráfico web
EXPOSE 80

# Usar la imagen oficial de PHP 8.2 con el servidor web Apache
FROM php:8.2-apache

# --- INICIO DE LA CORRECCIÓN ---
# Instalar las dependencias del sistema necesarias ANTES de instalar las extensiones de PHP.
# - libpq-dev: Contiene las librerías de desarrollo para el cliente de PostgreSQL (el famoso libpq-fe.h).
# - git, zip, unzip: Herramientas útiles que a menudo se necesitan.
RUN apt-get update && apt-get install -y \
    libpq-dev \
    git \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*
# --- FIN DE LA CORRECCIÓN ---

# Ahora que las dependencias están instaladas, este comando funcionará.
RUN docker-php-ext-install pdo pdo_pgsql

# Habilitar el módulo de reescritura de Apache para las URLs amigables (.htaccess)
RUN a2enmod rewrite

# Copiar la configuración personalizada de Apache para apuntar a la carpeta /public
COPY .docker/000-default.conf /etc/apache2/sites-available/000-default.conf

# Copiar los archivos de tu proyecto al directorio web del contenedor
COPY . /var/www/html/

# Establecer los permisos correctos para que Apache pueda leer y escribir (si es necesario)
RUN chown -R www-data:www-data /var/www/html

# Exponer el puerto 80 para el tráfico web
EXPOSE 80


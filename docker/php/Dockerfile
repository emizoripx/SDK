FROM php:8.4-cli

# Instalar extensiones necesarias
RUN apt-get update && apt-get install -y \
    default-mysql-client \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_mysql

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

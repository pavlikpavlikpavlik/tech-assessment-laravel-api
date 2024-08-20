FROM php:8.2-fpm

RUN apt-get update && \
    apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    unzip \
    git \
    libxml2-dev \
    curl \
    default-mysql-client && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install gd zip xml pdo_mysql && \
    pecl install xdebug && \
    docker-php-ext-enable xdebug && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

COPY ./src /var/www/html
COPY ./docker/xdebug.ini /usr/local/etc/php/conf.d/
COPY ./docker/php.ini /usr/local/etc/php/conf.d/

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
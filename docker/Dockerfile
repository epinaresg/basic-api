FROM php:8.0.12-fpm

RUN apt-get update && apt-get install -y \
    curl \
    zip \
    vim \
    unzip

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www
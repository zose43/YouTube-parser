FROM php:8.1-fpm

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN usermod -u 1000 www-data \
  && groupmod -g 1000 www-data \
  && chown www-data:www-data /var/www

RUN apt-get update && apt-get install -y \
    wget \
    git \
    unzip \
    vim \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && docker-php-source delete \
    && apt-get autoremove -y \
    && apt-get autoclean -y \
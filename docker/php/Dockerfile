FROM php:8.0-fpm

RUN apt-get update && apt-get install -y  --no-install-recommends libmcrypt-dev \
    zip unzip libmagickwand-dev libmagickcore-dev

RUN docker-php-ext-install mysqli pdo pdo_mysql

# install xdebug
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# install imagick
RUN pecl install imagick \
    && docker-php-ext-enable imagick

# install redis
RUN pecl install redis \
    && docker-php-ext-enable redis


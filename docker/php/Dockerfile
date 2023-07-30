FROM php:8.2-fpm

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

RUN apt-get install -y libmemcached11 libmemcachedutil2 build-essential libmemcached-dev libz-dev \
  && pecl install memcached \
  && echo extension=memcached.so >> /usr/local/etc/php/conf.d/memcached.ini

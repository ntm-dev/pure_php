FROM php:8.0-fpm

RUN apt-get update && apt-get install -y  --no-install-recommends libmcrypt-dev \
    zip unzip

RUN docker-php-ext-install mysqli pdo pdo_mysql

RUN BEFORE_PWD=$(pwd) \
    && mkdir -p /opt/xdebug \
    && cd /opt/xdebug \
    && curl -k -L https://xdebug.org/files/xdebug-3.1.2.tgz | tar zx \
    && cd xdebug-3.1.2 \
    && phpize \
    && ./configure --enable-xdebug \
    && make \
    && ls -la modules \
    && cp modules/xdebug.so /usr/local/lib/php/extensions/no-debug-non-zts-20200930 \
    # && sed -i 's/-O2/-O0/g' Makefile \
    # && make \
    # # && make test \
    # && make install \
    && cd "${BEFORE_PWD}" \
    && rm -r /opt/xdebug

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug


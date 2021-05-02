FROM php:8.0-fpm

ARG PHP_USER_ID=1000
ARG PHP_GROUP_ID=1000
ARG PHP_HOST_IP=172.17.0.1

RUN groupadd --gid $PHP_GROUP_ID php \
    && useradd --uid $PHP_USER_ID --gid php --create-home php

RUN apt-get update && apt-get install -y git zip zlib1g-dev libfreetype6-dev libjpeg62-turbo-dev libpng-dev libwebp-dev

RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install opcache pdo_mysql gd \
    && pecl install apcu xdebug \
    && docker-php-ext-enable apcu xdebug \
    && echo "\nxdebug.mode=debug\nxdebug.client_host=$PHP_HOST_IP" \
        >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

COPY scripts/install_composer.sh /tmp/install_composer.sh
RUN sh /tmp/install_composer.sh && rm /tmp/install_composer.sh

# Add shortcuts for enabling/disabling Xdebug when running CLI scripts.
RUN echo "\nfunction exd {\n    export XDEBUG_CONFIG=\n}\n\nfunction dxd {\n    unset XDEBUG_CONFIG\n}\n" \
        >> /home/php/.bashrc

USER php

WORKDIR /var/www/sandbox

FROM php:7.4-fpm

ARG PHP_USER_ID=1000
ARG PHP_GROUP_ID=1000
ARG PHP_HOST_IP=172.17.0.1

RUN groupadd --gid $PHP_GROUP_ID php \
    && useradd --uid $PHP_USER_ID --gid php --create-home php

RUN apt-get update && apt-get install -y git zip

RUN docker-php-ext-install opcache pdo_mysql \
    && pecl install apcu xdebug \
    && docker-php-ext-enable apcu xdebug \
    && echo "\nxdebug.remote_enable=1\nxdebug.remote_host=$PHP_HOST_IP" \
        >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

COPY scripts/install_composer.sh /tmp/install_composer.sh
RUN sh /tmp/install_composer.sh && rm /tmp/install_composer.sh

COPY scripts/exd scripts/dxd /usr/local/bin/

USER php

WORKDIR /var/www/sandbox

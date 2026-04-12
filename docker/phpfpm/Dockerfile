FROM php:8.1-fpm-alpine3.18

RUN apk add --no-cache git unzip icu-dev libzip-dev \
    && docker-php-ext-install -j$(nproc) \
        intl zip opcache \
    && apk del --no-cache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

COPY docker/phpfpm/php.ini /usr/local/etc/php/php.ini
COPY docker/phpfpm/phpfpm.conf /usr/local/etc/php-fpm.d/zzz_config.conf

WORKDIR /www

EXPOSE 9000
CMD ["php-fpm"]

FROM php:fpm-alpine3.8

# Build for production
ARG SSH_KEY=""
ARG BUILD_DATE="dev"
ARG VCS_REF="dev"
ARG APP_ENV=prod

# docker build configuration variables
ARG XDEBUG_VERSION=2.7.1
ARG APCU_VERSION=5.1.11
ENV ALPINE_VERSION=3.8
ENV ALPINE_MIRROR=http://nl.alpinelinux.org/alpine

# The maintainer list
LABEL authors="Christopher Thomas <chris.thomas@antimatter-studios.com>"
LABEL org.label-schema.build-date=$BUILD_DATE
LABEL org.label-schema.vcs-ref=$VCS_REF

# persistent / runtime deps
RUN apk add --no-cache acl file gettext git;

RUN set -xeu \
    && echo ${ALPINE_MIRROR}/v${ALPINE_VERSION}/main > /etc/apk/repositories \
    && echo ${ALPINE_MIRROR}/v${ALPINE_VERSION}/community >> /etc/apk/repositories \
    && apk add --no-cache git \
	&& apk add --no-cache --virtual .build-deps ${PHPIZE_DEPS} icu-dev libzip-dev zlib-dev \
	&& docker-php-ext-configure zip --with-libzip \
	&& docker-php-ext-install -j$(nproc) intl zip sockets pdo_mysql tokenizer mbstring \
	&& pecl install apcu-${APCU_VERSION} \
	&& pecl clear-cache \
	&& pecl install xdebug-${XDEBUG_VERSION} \
	&& docker-php-ext-enable apcu \
	&& docker-php-ext-enable opcache \
	&& runDeps="$( \
		scanelf --needed --nobanner --format '%n#p' --recursive /usr/local/lib/php/extensions \
			| tr ',' '\n' \
			| sort -u \
			| awk 'system("[ -e /usr/local/lib/" $1 " ]") == 0 { next } { print "so:" $1 }' \
	)" \
	&& apk add --no-cache --virtual .api-phpexts-rundeps $runDeps \
	&& apk del .build-deps

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY docker/phpfpm/php.ini /usr/local/etc/php/php.ini
# Configure XDEBUG extension and configuration
COPY docker/phpfpm/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
# the zzz in the filename, is to make sure this file is loaded last into the configuration
COPY docker/phpfpm/phpfpm.conf /usr/local/etc/php-fpm.d/zzz_config.conf

# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer global require "hirak/prestissimo:^0.3" \
        --prefer-dist \
        --no-progress \
        --no-suggest \
        --classmap-authoritative \
	&& composer clear-cache
ENV PATH="${PATH}:/root/.composer/vendor/bin"

WORKDIR /www

# If there was a shell key, then we should prepare to install composer dev-branches so install git/openssh-client
# Also we need to write the shell keys into the right places so it'll be found during php composer installation
RUN if [ ! -z "${SSH_KEY}" ]; then \
        apk --no-cache add git openssh-client \
        && mkdir -m 700 ~/.ssh \
        && echo "${SSH_KEY}" > ~/.ssh/id_rsa \
        && chmod 400 ~/.ssh/id_rsa \
        && echo -e "Host *\n   StrictHostKeyChecking=no\n   UserKnownHostsFile=/dev/null" >> /etc/ssh/ssh_config; \
    fi

COPY . /www

# prevent the reinstallation of vendors at every changes in the source code
RUN set -eux; \
	composer install --prefer-dist --no-dev --no-scripts --no-progress --no-suggest; \
	composer clear-cache

COPY docker/phpfpm/xdebug.sh /xdebug.sh
RUN chmod +x /xdebug.sh

COPY docker/phpfpm/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 9000
ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-fpm"]

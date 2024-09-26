FROM hyperf/hyperf:8.2-alpine-v3.19-swoole-v5 as base

# Envs
ENV POSTGRES_HASH="4612021aa9b441578fcbcbe6ae5b5d414f3d7b46"
ENV PHPIZE_DEPS="autoconf dpkg-dev dpkg file g++ gcc libc-dev make php82-dev php82-pear postgresql-dev pkgconf re2c pcre-dev pcre2-dev zlib-dev libtool automake"

## Install packages
RUN set -ex \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && php -m \
    && ln -s /usr/bin/phpize8 /usr/local/bin/phpize \
    && ln -s /usr/bin/php-config8 /usr/local/bin/php-config

## Configure PHP
RUN { \
        echo "max_execution_time=500"; \
        echo "upload_max_filesize=512M"; \
        echo "post_max_size=512M"; \
        echo "memory_limit=1G"; \
    } | tee /etc/php82/conf.d/99_overrides.ini \
    # Configure timezone
    && ln -sf /usr/share/zoneinfo/${TIMEZONE} /etc/localtime \
    && echo "${TIMEZONE}" > /etc/timezone \
    # Clear works
    && rm -rf /var/cache/apk/* /tmp/* /usr/share/man \
    && echo -e "\033[42;37m Build Completed :).\033[0m\n" \
    && ls /etc/php82/conf.d/

# Install dependencies with composer
WORKDIR /opt/www
COPY ./composer.* /opt/www/
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-dev -o --ignore-platform-reqs
ADD . /opt/www

## Dev image
FROM base as dev
ENV APP_ENV=dev SCAN_CACHEABLE=(false)
ENTRYPOINT ["php", "/opt/www/bin/hyperf.php"]
CMD [ "server:watch" ]

## Prod image
FROM base as prod
EXPOSE 8080
EXPOSE 9501
ENTRYPOINT ["php", "/opt/www/bin/hyperf.php"]
CMD [ "start" ]
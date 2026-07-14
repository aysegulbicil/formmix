FROM php:8.2-apache

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public \
    COMPOSER_ALLOW_SUPERUSER=1

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libicu-dev \
    && docker-php-ext-install -j"$(nproc)" \
        intl \
        mysqli \
        pdo_mysql \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader

COPY . .
COPY .env.docker .env
COPY docker/apache-vhost.conf /etc/apache2/sites-available/000-default.conf
COPY docker/entrypoint.sh /usr/local/bin/formmix-entrypoint

RUN sed -i 's/\r$//' /usr/local/bin/formmix-entrypoint \
    && chmod +x /usr/local/bin/formmix-entrypoint \
    && mkdir -p \
        writable/cache \
        writable/logs \
        writable/session \
        writable/uploads \
        writable/debugbar \
    && chown -R www-data:www-data writable

ENTRYPOINT ["formmix-entrypoint"]
CMD ["apache2-foreground"]

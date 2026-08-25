FROM php:8.3-fpm-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends libzip-dev \
    && docker-php-ext-install pdo_mysql zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker/php/entrypoint.sh /usr/local/bin/self-ordering-entrypoint

RUN chmod +x /usr/local/bin/self-ordering-entrypoint

WORKDIR /app

ENTRYPOINT ["self-ordering-entrypoint"]
CMD ["php-fpm"]

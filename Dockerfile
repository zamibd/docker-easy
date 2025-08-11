# --- Builder stage ---
FROM php:8.3-fpm-alpine AS builder

ENV PHPIZE_DEPS="autoconf gcc g++ make pkgconfig"

WORKDIR /app/sms

RUN apk add --no-cache \
        git bash tzdata \
        libzip-dev libxml2-dev curl-dev libcurl mariadb-connector-c-dev \
        libpng-dev libjpeg-turbo-dev libwebp-dev libxpm-dev freetype-dev \
        $PHPIZE_DEPS \
    && cp /usr/share/zoneinfo/Asia/Dubai /etc/localtime \
    && echo "Asia/Dubai" > /etc/timezone \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
        --with-xpm \
    && docker-php-ext-install gd pdo pdo_mysql mysqli zip pcntl bcmath curl \
    && apk del $PHPIZE_DEPS \
    && rm -rf /var/cache/apk/* \
    && addgroup -g 1000 imzami \
    && adduser -u 1000 -G imzami -s /bin/sh -D imzami

# Copy composer files first (for better cache)
COPY ./app/sms/composer.json ./app/sms/composer.lock* /app/sms/

# Copy app source code (পুরো /app ফোল্ডার)
COPY ./app /app

# Fix ownership so non-root user can write
RUN chown -R imzami:imzami /app

# Copy composer binary
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Run composer install in /app/sms (যেখানে composer.json)
USER imzami
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-scripts --no-progress
USER root

# --- Final stage ---
FROM php:8.3-fpm-alpine

WORKDIR /var/www/html

RUN apk add --no-cache tzdata \
    && cp /usr/share/zoneinfo/Asia/Dubai /etc/localtime \
    && echo "Asia/Dubai" > /etc/timezone \
    && apk del tzdata \
    && addgroup -g 1000 imzami \
    && adduser -u 1000 -G imzami -s /bin/sh -D imzami

# Copy PHP extensions and config from builder
COPY --from=builder /usr/local/lib/php/extensions /usr/local/lib/php/extensions
COPY --from=builder /usr/local/etc/php/conf.d /usr/local/etc/php/conf.d

# Copy composer binary (optional)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application code and vendor from builder
COPY --from=builder /app /var/www/html

# Fix ownership so runtime user can write
RUN chown -R imzami:imzami /var/www/html

USER imzami

# Copy your php.ini and php-fpm config
COPY ./docker/php/php.ini /usr/local/etc/php/php.ini
COPY ./docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

# Entrypoint script
COPY ./docker/php/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-fpm"]

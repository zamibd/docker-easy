# 🐘 Use official PHP 8.3 FPM Alpine image for lightweight performance
FROM php:8.3-fpm-alpine

# 📛 Metadata labels for clarity
LABEL Name="SMS GATEWAY" \
      Version="1.0.0" \
      Description="Android SMS Gateway" \
      Maintainer="hey@imzami.com"

# 🛠 Define build tools required for compiling PHP extensions
ENV PHPIZE_DEPS="autoconf gcc g++ make pkgconfig"

# 📁 Set working directory inside container
WORKDIR /var/www/html

# 🧰 Install system dependencies and PHP extensions
RUN set -ex \
    && apk add --no-cache \
        git bash tzdata \
        libzip-dev libxml2-dev \
        curl-dev mariadb-connector-c-dev \
        mariadb-client \
        $PHPIZE_DEPS \
    # 🕒 Configure timezone
    && cp /usr/share/zoneinfo/Asia/Dubai /etc/localtime \
    && echo "Asia/Dubai" > /etc/timezone \
    # 🔌 Install Redis PHP extension
    && pecl install redis \
    && docker-php-ext-enable redis \
    # 🧩 Install core PHP extensions
    && docker-php-ext-install \
        pdo pdo_mysql mysqli zip pcntl bcmath curl \
        opcache intl mbstring exif \
    # 🧹 Remove build tools and clean cache
    && apk del $PHPIZE_DEPS libzip-dev libxml2-dev curl-dev mariadb-connector-c-dev \
    && rm -rf /var/cache/apk/*

# 🎼 Copy Composer binary from official Composer image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 📦 Copy Laravel project files
COPY . .

# 📜 Copy only composer files first to optimize caching
# (Optional: uncomment if using multi-stage caching)
# COPY composer.json composer.lock ./

# 📥 Install Laravel dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# ⚙️ Copy PHP configuration files
COPY ./docker/php/php.ini /usr/local/etc/php/php.ini
COPY ./docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

# 🚪 Expose PHP-FPM port
EXPOSE 9000

# 🚀 Copy entrypoint script and make it executable
COPY ./docker/php/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# 🧨 Entrypoint script should call php-fpm internally
ENTRYPOINT ["/entrypoint.sh"]

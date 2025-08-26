# Stage 1: Build dependencies
FROM composer:lts AS vendor

WORKDIR /app

COPY database/ database/
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --ignore-platform-reqs

COPY . .
RUN composer dump-autoload --optimize --classmap-authoritative --no-dev --no-scripts

# Stage 2: Build frontend assets (Optional - if you have JS/CSS build steps)
FROM node:lts AS frontend_builder
WORKDIR /app
COPY --from=vendor /app /app
COPY package.json pnpm-lock.yaml ./
RUN npm install -g pnpm
ENV CI=true
RUN pnpm install --frozen-lockfile
COPY vite.config.js ./
COPY resources/ resources/
RUN pnpm run build

# Stage 3: Final production image
FROM dunglas/frankenphp AS frankenphp_runtime

ARG APP_VERSION
ENV APP_VERSION=$APP_VERSION

# Set environment variables defaults (can be overridden at runtime)
ENV SERVER_NAME=:8080 \
    APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    QUEUE_CONNECTION=redis \
    REDIS_HOST=queue \
    DB_QUEUE_CONNECTION=queues-sqlite

WORKDIR /app
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && apt-get update && apt-get install -y supervisor \
    && mkdir -p /var/log/supervisor \
    && apt-get clean && rm -rf /var/lib/apt/lists/* \
    # Install PHP extensions required by Laravel and your app
    # Ensure opcache is installed and enabled
    && install-php-extensions \
        pdo_sqlite \
        gd \
        intl \
        zip \
        opcache \
        redis

COPY docker/entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Copy built vendor dependencies
COPY --from=vendor /app/vendor /app/vendor
COPY . /app
COPY --from=frontend_builder /app/public/build /app/public/build/

# Clear package discovery cache first, then generate clean production cache
RUN rm -f bootstrap/cache/packages.php bootstrap/cache/services.php \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Set permissions for storage and bootstrap/cache (adjust user/group if needed)
RUN mkdir -p storage/framework/{sessions,views,cache} storage/logs bootstrap/cache \
    && chown -R www-data:www-data ./* \
    && chmod -R 775 storage bootstrap/cache \
    && chmod -R 770 database/mount

# Expose port and set entrypoint/cmd
EXPOSE 8080
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["supervisord", "-c", "/app/docker/supervisord.conf"]

# Healthcheck (optional)
# HEALTHCHECK --interval=30s --timeout=3s \
#  CMD curl --fail http://localhost:8080/ping || exit 1


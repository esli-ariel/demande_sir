FROM composer:2@sha256:ca11f643d7791ebdd90a34fc20caf11380d434007ae539a9ea75cd9212e13dca AS composer-bin

FROM php:8.4-fpm-alpine@sha256:511af6fa3ef55c4ea0c68bb47096ba4b81771ab4949bde7813807bf177efda11 AS php-base
ENV APP_ENV=production APP_DEBUG=false
WORKDIR /app
RUN apk add --no-cache nginx supervisor icu-libs libzip libpng freetype libjpeg-turbo \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS icu-dev libzip-dev libpng-dev freetype-dev libjpeg-turbo-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" bcmath pdo_mysql intl zip gd \
    && apk del .build-deps

FROM php-base AS backend
RUN apk add --no-cache git unzip
COPY --from=composer-bin /usr/bin/composer /usr/local/bin/composer
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction --prefer-dist --no-progress
COPY . .
RUN composer dump-autoload --no-dev --classmap-authoritative --no-scripts --no-interaction

FROM node:22-alpine@sha256:0e1754758c2df4cfa3e68d59e2e1b7374bb7fd9ea6328eaaacf15cc934af8202 AS frontend
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --ignore-scripts --no-audit --no-fund
COPY vite.config.js tailwind.config.js postcss.config.js ./
COPY resources ./resources
COPY public ./public
COPY --from=backend /app/vendor/laravel/framework/src/Illuminate/Pagination/resources/views ./vendor/laravel/framework/src/Illuminate/Pagination/resources/views
RUN npm run build

FROM php-base AS app
COPY --from=backend /app /app
COPY --from=frontend /app/public/build /app/public/build
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/zzzz-app.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/entrypoint.sh /usr/local/bin/dma-entrypoint
RUN addgroup -g 10001 app \
    && adduser -D -u 10001 -G app app \
    && sed -i -E '/^[[:space:]]*(user|group)[[:space:]]*=/d' /usr/local/etc/php-fpm.d/www.conf \
    && mkdir -p storage/app/public storage/app/private storage/logs storage/framework/cache/data storage/framework/sessions storage/framework/views bootstrap/cache \
    && chown -R 10001:10001 storage bootstrap/cache \
    && chmod -R u=rwX,g=rX,o= storage bootstrap/cache \
    && chmod 0555 /usr/local/bin/dma-entrypoint \
    && ln -s /app/storage/app/public /app/public/storage

USER 10001:10001
EXPOSE 8080
ENTRYPOINT ["/usr/local/bin/dma-entrypoint"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]

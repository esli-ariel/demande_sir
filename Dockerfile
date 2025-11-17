FROM webdevops/php-nginx:8.3-alpine

# Réglages Nginx/PHP
ENV WEB_DOCUMENT_ROOT=/app/public
ENV APP_ENV=production

# Paquets nécessaires
RUN apk add --no-cache \
    oniguruma-dev \
    libxml2-dev \
    mysql-client \
    nodejs \
    npm \
    git \
    unzip \
    icu-dev \
    libpng-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libzip-dev

# Extensions PHP utiles à Laravel
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install \
        bcmath \
        ctype \
        fileinfo \
        mbstring \
        pdo_mysql \
        xml \
        zip \
        intl \
        gd

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Code
WORKDIR /app
COPY . .

# .env (ne pas écraser si déjà présent)
RUN cp -n .env.example .env || true

# Dépendances back
RUN composer install --no-interaction --prefer-dist --no-progress --no-dev && \
    composer dump-autoload --optimize

# Clé d’application (ne régénère pas si APP_KEY déjà défini)
RUN php -r "file_exists('.env') && !preg_match('/^APP_KEY=.+/m', file_get_contents('.env')) && exit(0);" || true && \
    php artisan key:generate --force || true

# Optimisations Laravel
RUN php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache

# Dépendances/front build (Breeze/Vite)
RUN npm ci --no-audit --no-fund || npm install --no-audit --no-fund
RUN npm run build

# Hook d'entrypoint: attendre MySQL puis lancer migrations + seeds
COPY docker/entrypoint/99-artisan.sh /opt/docker/provision/entrypoint.d/99-artisan.sh
RUN chmod +x /opt/docker/provision/entrypoint.d/99-artisan.sh

# Permissions
RUN chown -R application:application /app && \
    find storage -type d -exec chmod 775 {} \; && \
    find storage -type f -exec chmod 664 {} \; && \
    chmod -R 775 bootstrap/cache

# Nginx écoute 80 par défaut dans cette image; PHP-FPM déjà supervisé
EXPOSE 80



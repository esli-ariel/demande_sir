#!/bin/sh
set -eu
umask 077

if [ "$(id -u)" -eq 0 ]; then
    echo 'Le conteneur DMA doit fonctionner sans root.' >&2
    exit 1
fi

load_secret() {
    secret_name=$1
    secret_file=$2
    secret_existing=$3
    if [ -n "$secret_file" ]; then
        if [ -n "$secret_existing" ]; then
            echo "Configurer uniquement $secret_name ou son fichier secret, pas les deux." >&2
            exit 1
        fi
        if [ ! -r "$secret_file" ]; then
            echo "Fichier secret illisible pour $secret_name." >&2
            exit 1
        fi
        secret_value=$(cat -- "$secret_file")
    else
        secret_value=$secret_existing
    fi
    if [ -z "$secret_value" ]; then
        echo "Secret obligatoire absent : $secret_name." >&2
        exit 1
    fi
    export "$secret_name=$secret_value"
    unset secret_value secret_existing secret_file secret_name
}

load_secret APP_KEY "${APP_KEY_FILE:-}" "${APP_KEY:-}"
load_secret DB_PASSWORD "${DB_PASSWORD_FILE:-}" "${DB_PASSWORD:-}"
unset APP_KEY_FILE DB_PASSWORD_FILE

php artisan config:cache --no-ansi
php artisan route:cache --no-ansi
php artisan view:cache --no-ansi
exec "$@"

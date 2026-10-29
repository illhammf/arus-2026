#!/bin/bash
set -e

echo "🚀 Starting Arus container..."

# ---------------------------------------------------------------------------
# Environment
# ---------------------------------------------------------------------------
# Never overwrite an existing .env. On a fresh checkout, create it from the
# example and generate a unique application key once.
if [ ! -f /var/www/html/.env ]; then
    echo "📄 Creating .env from .env.example..."
    cp /var/www/html/.env.example /var/www/html/.env
fi

if ! grep -q '^APP_KEY=base64:' /var/www/html/.env 2>/dev/null; then
    echo "🔐 Generating application key..."
    php artisan key:generate --force
fi

# ---------------------------------------------------------------------------
# Wait for database
# ---------------------------------------------------------------------------
DB_HOST=$(grep '^DB_HOST=' /var/www/html/.env | cut -d '=' -f2- | tr -d '"' | tr -d "'")
DB_PORT=$(grep '^DB_PORT=' /var/www/html/.env | cut -d '=' -f2- | tr -d '"' | tr -d "'")
DB_HOST=${DB_HOST:-db}
DB_PORT=${DB_PORT:-3306}

echo "⏳ Waiting for database at ${DB_HOST}:${DB_PORT}..."
RETRIES=30
until nc -z "$DB_HOST" "$DB_PORT"; do
    if [ "$RETRIES" -le 0 ]; then
        echo "❌ Timeout waiting for database. Exiting."
        exit 1
    fi
    echo "Waiting for DB..."
    sleep 2
    RETRIES=$((RETRIES - 1))
done

# ---------------------------------------------------------------------------
# Dependencies and runtime directories
# ---------------------------------------------------------------------------
if [ ! -d /var/www/html/vendor ]; then
    echo "📦 Installing composer dependencies..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Migrations are additive by design. Set RUN_MIGRATIONS=false when migrations
# are executed by a dedicated release step (recommended for production).
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    echo "🗃️  Running pending migrations..."
    php artisan migrate --force
fi

php artisan storage:link >/dev/null 2>&1 || true

# ---------------------------------------------------------------------------
# Cron / scheduler
# ---------------------------------------------------------------------------
service cron start >/dev/null 2>&1 || true

# ---------------------------------------------------------------------------
# Xdebug toggle (development only)
# ---------------------------------------------------------------------------
XDEBUG_CONFIG="/usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini"

if [ "$XDEBUG" = "true" ] && [ ! -f "$XDEBUG_CONFIG" ]; then
    echo "🐞 Enabling Xdebug..."
    docker-php-ext-enable xdebug
    {
        echo "xdebug.mode=debug"
        echo "xdebug.start_with_request=yes"
        echo "xdebug.client_host=${REMOTE_HOST:-host.docker.internal}"
        echo "xdebug.client_port=9003"
    } >> "$XDEBUG_CONFIG"
elif [ "$XDEBUG" != "true" ] && [ -f "$XDEBUG_CONFIG" ]; then
    echo "🔧 Disabling Xdebug..."
    rm -f "$XDEBUG_CONFIG"
fi

echo "✅ Arus container is ready."

exec "$@"

#!/bin/sh
set -e

# Install dependencies
composer install --no-interaction --quiet 2>/dev/null || true

# Wait for database
echo "Waiting for database..."
until php bin/console doctrine:database:create --if-not-exists --no-interaction 2>/dev/null; do
    sleep 1
done

# Sync database schema and migrations
echo "Syncing database schema..."
php bin/console doctrine:migrations:migrate --no-interaction 2>/dev/null || {
    echo "Migration failed, marking existing tables and syncing schema..."
    php bin/console doctrine:migrations:version 'DoctrineMigrations\Version20260428133320' --add --mark-migrated --no-interaction 2>/dev/null || true
    php bin/console doctrine:migrations:migrate --no-interaction 2>/dev/null || php bin/console doctrine:schema:update --force --complete --no-interaction
}

# Load fixtures (only if empty)
echo "Checking if data exists..."
PRODUCT_COUNT=$(php bin/console doctrine:query:sql "SELECT COUNT(*) as cnt FROM produto" 2>/dev/null | tail -1 || echo "0")
if [ "$PRODUCT_COUNT" -eq 0 ] 2>/dev/null; then
    echo "Loading fixtures..."
    php bin/console doctrine:fixtures:load --no-interaction
else
    echo "Data already exists, skipping fixtures."
fi

# Start Symfony server
echo "Starting application on port 8000..."
exec php -S 0.0.0.0:8000 -t public

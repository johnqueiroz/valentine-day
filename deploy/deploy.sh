#!/usr/bin/env bash
# Deploy/atualização do app na VM. Rode da raiz do projeto:
#   bash deploy/deploy.sh
set -euo pipefail

cd "$(dirname "$0")/.."

echo "==> git pull"
git pull --ff-only

echo "==> composer (sem dev)"
composer install --no-dev --optimize-autoloader --no-interaction

echo "==> assets (vite build)"
npm ci
npm run build

echo "==> migrations"
php artisan migrate --force

echo "==> storage symlink"
php artisan storage:link || true

echo "==> cache de config/rotas/views"
php artisan optimize

echo "==> permissões (storage, cache, database)"
sudo chown -R www-data:www-data storage bootstrap/cache database
sudo chmod -R ug+rw storage bootstrap/cache database

echo "==> reload php-fpm"
sudo systemctl reload php8.3-fpm

echo "OK — deploy concluído."

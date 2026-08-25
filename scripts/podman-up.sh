#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

if [[ -f .env ]]; then
  set -a
  # shellcheck disable=SC1091
  source .env
  set +a
fi

POD_NAME="${POD_NAME:-myapp-pod}"
APP_IMAGE="${APP_IMAGE:-localhost/self-ordering-api:dev}"
API_FORWARD_PORT="${API_FORWARD_PORT:-8080}"
HTTPS_FORWARD_PORT="${HTTPS_FORWARD_PORT:-8443}"
MYSQL_FORWARD_PORT="${MYSQL_FORWARD_PORT:-3307}"
REDIS_FORWARD_PORT="${REDIS_FORWARD_PORT:-6379}"

DB_DATABASE="${DB_DATABASE:-self_ordering}"
DB_USERNAME="${DB_USERNAME:-self_ordering}"
DB_PASSWORD="${DB_PASSWORD:-self_ordering}"
MYSQL_ROOT_PASSWORD="${MYSQL_ROOT_PASSWORD:-self_ordering_root}"

containers=(
  self-ordering-nginx
  self-ordering-php-fpm
  self-ordering-queue
  self-ordering-scheduler
  self-ordering-mysql
  self-ordering-redis
)

for name in "${containers[@]}"; do
  podman rm -f "$name" >/dev/null 2>&1 || true
done

podman pod rm -f "$POD_NAME" >/dev/null 2>&1 || true

podman pod create \
  --name "$POD_NAME" \
  -p "${API_FORWARD_PORT}:80" \
  -p "${HTTPS_FORWARD_PORT}:443" \
  -p "${MYSQL_FORWARD_PORT}:3306" \
  -p "${REDIS_FORWARD_PORT}:6379" >/dev/null

podman volume exists selforderingbackend_mysql-data >/dev/null 2>&1 || podman volume create selforderingbackend_mysql-data >/dev/null
podman volume exists selforderingbackend_redis-data >/dev/null 2>&1 || podman volume create selforderingbackend_redis-data >/dev/null

podman run -d \
  --name self-ordering-mysql \
  --pod "$POD_NAME" \
  -e MYSQL_DATABASE="$DB_DATABASE" \
  -e MYSQL_USER="$DB_USERNAME" \
  -e MYSQL_PASSWORD="$DB_PASSWORD" \
  -e MYSQL_ROOT_PASSWORD="$MYSQL_ROOT_PASSWORD" \
  -v selforderingbackend_mysql-data:/var/lib/mysql:Z \
  mysql:8.4 >/dev/null

podman run -d \
  --name self-ordering-redis \
  --pod "$POD_NAME" \
  -v selforderingbackend_redis-data:/data:Z \
  redis:7.2-bookworm >/dev/null

podman run -d \
  --name self-ordering-php-fpm \
  --pod "$POD_NAME" \
  --env-file .env \
  -e DB_HOST=127.0.0.1 \
  -e DB_PORT=3306 \
  -e REDIS_HOST=127.0.0.1 \
  -e REDIS_PORT=6379 \
  -v "$ROOT_DIR:/app:Z" \
  -w /app \
  "$APP_IMAGE" >/dev/null

podman run -d \
  --name self-ordering-queue \
  --pod "$POD_NAME" \
  --env-file .env \
  -e DB_HOST=127.0.0.1 \
  -e DB_PORT=3306 \
  -e REDIS_HOST=127.0.0.1 \
  -e REDIS_PORT=6379 \
  -v "$ROOT_DIR:/app:Z" \
  -w /app \
  "$APP_IMAGE" \
  php artisan queue:work --sleep=1 --tries=3 >/dev/null

podman run -d \
  --name self-ordering-scheduler \
  --pod "$POD_NAME" \
  --env-file .env \
  -e DB_HOST=127.0.0.1 \
  -e DB_PORT=3306 \
  -e REDIS_HOST=127.0.0.1 \
  -e REDIS_PORT=6379 \
  -v "$ROOT_DIR:/app:Z" \
  -w /app \
  "$APP_IMAGE" \
  php artisan schedule:work >/dev/null

podman run -d \
  --name self-ordering-nginx \
  --pod "$POD_NAME" \
  -v "$ROOT_DIR:/app:Z" \
  -v "$ROOT_DIR/docker/nginx/default.conf:/etc/nginx/conf.d/default.conf:Z" \
  nginx:1.28-bookworm >/dev/null

podman pod ps --filter "name=${POD_NAME}"
podman ps --pod --filter "pod=${POD_NAME}"

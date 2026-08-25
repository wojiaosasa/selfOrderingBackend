#!/usr/bin/env sh
set -eu

if [ ! -d vendor ]; then
  echo "Dependencies are not installed. Run: composer install"
  sleep infinity
fi

exec "$@"

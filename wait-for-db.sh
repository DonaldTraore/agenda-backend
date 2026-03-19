#!/bin/bash
set -e

echo "Waiting for database..."
for i in {1..30}; do
  if php artisan migrate:status > /dev/null 2>&1; then
    echo "Database connected!"
    break
  fi
  echo "Attempt $i/30: Database not ready, waiting..."
  sleep 3
done

echo "Running migrations..."
php artisan migrate --force

echo "Starting server..."
php artisan serve --host=0.0.0.0 --port=8000
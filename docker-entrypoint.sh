#!/bin/bash
set -e

echo "=== Configuration Laravel pour Docker ==="

# Créer le fichier .env s'il n'existe pas
if [ ! -f .env ]; then
    echo "Création du fichier .env à partir de .env.example..."
    cp .env.example .env
fi

# Générer APP_KEY si elle est vide
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
    echo "Génération de APP_KEY..."
    php artisan key:generate --force
fi

# Générer JWT_SECRET s'il est vide
if ! grep -q "JWT_SECRET=" .env || [ -z "$(grep JWT_SECRET .env | cut -d'=' -f2)" ]; then
    echo "Génération de JWT_SECRET..."
    php artisan jwt:secret --force 2>/dev/null || true
fi

echo "=== Attente de la base de données ==="
for i in {1..30}; do
  if php artisan db:monitor --durations=0 > /dev/null 2>&1 || php artisan migrate:status > /dev/null 2>&1; then
    echo "✓ Base de données connectée!"
    break
  fi
  echo "Tentative $i/30: Base de données non prête, attente..."
  sleep 3
done

echo "=== Exécution des migrations ==="
php artisan migrate --force || echo "⚠ Avertissement: Problème avec les migrations"

echo "=== Démarrage du serveur ==="
php artisan serve --host=0.0.0.0 --port=8000

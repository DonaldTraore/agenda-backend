#!/bin/bash
set -e
echo "=== Configuration Laravel pour Docker ==="

# Créer le fichier .env s'il n'existe pas
if [ ! -f .env ]; then
    echo "Création du fichier .env à partir de .env.example..."
    cp .env.example .env
fi

# Injecter les variables d'environnement Railway dans .env
echo "Injection des variables d'environnement..."
cat > .env << EOF
APP_NAME=Laravel
APP_ENV=${APP_ENV:-production}
APP_KEY=${APP_KEY}
APP_DEBUG=${APP_DEBUG:-false}
APP_URL=${APP_URL:-http://localhost}

DB_CONNECTION=mysql
DB_HOST=${DB_HOST}
DB_PORT=${DB_PORT:-3306}
DB_DATABASE=${DB_DATABASE}
DB_USERNAME=${DB_USERNAME}
DB_PASSWORD=${DB_PASSWORD}

JWT_SECRET=${JWT_SECRET}

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
EOF

echo "✓ .env configuré"

# Générer APP_KEY si elle est vide
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
    echo "Génération de APP_KEY..."
    php artisan key:generate --force
fi

# Générer JWT_SECRET s'il est vide
if [ -z "$JWT_SECRET" ]; then
    echo "Génération de JWT_SECRET..."
    php artisan jwt:secret --force 2>/dev/null || true
fi

echo "=== Diagnostic connexion BD ==="
echo "DB_HOST: ${DB_HOST}"
echo "DB_PORT: ${DB_PORT:-3306}"
echo "DB_DATABASE: ${DB_DATABASE}"
echo "DB_USERNAME: ${DB_USERNAME}"

echo "=== Attente de la base de données ==="
for i in {1..30}; do
  if php artisan migrate:status > /dev/null 2>&1; then
    echo "✓ Base de données connectée!"
    break
  fi
  echo "Tentative $i/30: Base de données non prête, attente..."
  if [ $i -eq 30 ]; then
    echo "❌ Impossible de se connecter. Erreur:"
    php artisan migrate:status 2>&1 || true
    exit 1
  fi
  sleep 3
done

echo "=== Exécution des migrations ==="
php artisan migrate --force

echo "=== Optimisation ==="
php artisan config:cache
php artisan route:cache

echo "=== Démarrage du serveur ==="
php artisan serve --host=0.0.0.0 --port=8000

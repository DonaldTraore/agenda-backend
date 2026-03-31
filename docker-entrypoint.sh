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
sed -i "s|APP_KEY=.*|APP_KEY=${APP_KEY}|" .env
sed -i "s|APP_ENV=.*|APP_ENV=${APP_ENV:-production}|" .env
sed -i "s|DB_HOST=.*|DB_HOST=${DB_HOST}|" .env
sed -i "s|DB_PORT=.*|DB_PORT=${DB_PORT:-3306}|" .env
sed -i "s|DB_DATABASE=.*|DB_DATABASE=${DB_DATABASE}|" .env
sed -i "s|DB_USERNAME=.*|DB_USERNAME=${DB_USERNAME}|" .env
sed -i "s|DB_PASSWORD=.*|DB_PASSWORD=${DB_PASSWORD}|" .env

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

echo "=== Diagnostic connexion BD ==="
echo "DB_HOST: ${DB_HOST}"
echo "DB_PORT: ${DB_PORT}"
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
    echo "❌ Impossible de se connecter à la BD. Vérifiez les variables Railway."
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

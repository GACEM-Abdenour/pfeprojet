#!/bin/bash
set -e
echo "[docker-entrypoint] Attente de SQL Server..."
php /var/www/html/docker/wait-for-sql.php

echo "[docker-entrypoint] Exécution de setup_database.php..."
php /var/www/html/setup_database.php || true

echo "[docker-entrypoint] Données de démonstration (comptes de test)..."
php /var/www/html/test/insert_test_data.php || true

exec apache2-foreground

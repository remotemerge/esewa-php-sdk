#!/bin/bash

# Enable strict error handling
set -euo pipefail

echo "Changing directory to /var/www/html..."
cd /var/www/html || exit 1

echo "Installing PHP dependencies via Composer..."
composer install --no-interaction --no-progress --optimize-autoloader --prefer-dist

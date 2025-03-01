#!/bin/bash

# Exit on error
set -e

su - application <<'EOF'
cd /var/www/html

# Install dependencies
composer install --prefer-dist --optimize-autoloader --no-progress --no-interaction

EOF

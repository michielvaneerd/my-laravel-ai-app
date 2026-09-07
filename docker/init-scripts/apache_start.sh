#!/bin/sh

# This is the original Peercode file.

set -e

# copy env.example to .env when .env not exists
# test -f .env || cp -n .env.example .env

echo 'running prestart apache script'

# # Place your commands here to execute on every container startup action
composer install

php artisan migrate
php artisan view:clear

# if [ ! -e public/storage ]; then
#    echo 'Create public storage link'
#    php artisan storage:link
# fi

echo 'initialization done, starting apache'
# Make sure this is the last command of this file
exec apache2-foreground

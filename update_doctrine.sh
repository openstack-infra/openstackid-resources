#!/usr/bin/env bash
php composer.phar dump-autoload --optimize;
php artisan doctrine:clear:metadata:cache
php artisan doctrine:clear:query:cache
php artisan doctrine:clear:result:cache
php artisan doctrine:generate:proxies -v
php artisan route:clear
php artisan route:cache
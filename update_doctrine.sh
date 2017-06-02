#!/usr/bin/env bash
php artisan doctrine:generate:proxies
php artisan doctrine:clear:metadata:cache
php artisan doctrine:clear:query:cache
php artisan doctrine:clear:result:cache
php artisan route:clear
php artisan route:cache
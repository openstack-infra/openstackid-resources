# OpenStackId Resource Server

## Prerequisites

    * LAMP/LEMP environment
    * PHP >= 5.4.0
    * Redis
    * composer (https://getcomposer.org/)

## Install

run following commands on root folder
   * curl -s https://getcomposer.org/installer | php
   * php composer.phar install --prefer-dist
   * php composer.phar dump-autoload --optimize
   * php artisan migrate --env=YOUR_ENVIRONMENT
   * php artisan db:seed --env=YOUR_ENVIRONMENT
   * phpunit --bootstrap vendor/autoload.php
   * php artisan doctrine:generate:proxies
   * php artisan doctrine:clear:metadata:cache
   * php artisan doctrine:clear:query:cache
   * php artisan doctrine:clear:result:cache
   * php artisan doctrine:ensure:production
   * php artisan route:clear
   * php artisan route:cache
   * give proper rights to storage folder (775 and proper users)
   * chmod 777 vendor/ezyang/htmlpurifier/library/HTMLPurifier/DefinitionCache/Serializer
   
## Permissions

Laravel may require some permissions to be configured: folders within storage and vendor require write access by the web server.   

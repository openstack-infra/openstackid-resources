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
   * cp Libs/Doctrine/BasicEntityPersister.php.tpl vendor/doctrine/orm/lib/Doctrine/ORM/Persisters/Entity/BasicEntityPersister.php
   * give proper rights to storage folder (775 and proper users)
   
## Permissions

Laravel may require some permissions to be configured: folders within storage and vendor require write access by the web server.   

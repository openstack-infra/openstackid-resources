# OpenStackId Resource Server

## Prerequisites

    * LAMP environment
    * PHP >= 5.4.0
    * composer (https://getcomposer.org/)

## Install

run following commands on root folder
   * curl -s https://getcomposer.org/installer | php
   * php composer.phar install --prefer-dist
   * php composer.phar dump-autoload --optimize
   * php artisan migrate --env=YOUR_ENVIRONMENT
   * php artisan db:seed --env=YOUR_ENVIRONMENT
   * phpunit --bootstrap vendor/autoload.php
   
   
## Permissions

Laravel may require some permissions to be configured: folders within storage and vendor require write access by the web server.   

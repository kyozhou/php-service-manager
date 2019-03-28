# php-service-manager
tool help you to manage your process, based on swoole

## How to use ?
1. add `kyozhou/php-service-manager` to require filed in your composer.json file
2. composer install
3. `php yourscript.php start|stop|restart|debug`(it will create a "yourscript.pid" file in current dic)

## use case
```
<?php
require 'vendor/autoload.php'; //this will autoload php-service-manager
while(true) {
    file_put_contents("/tmp/logger.log", rand(1, 9) . ',', FILE_APPEND);
    sleep(1);
}
```

author email: kyozhou@sina.com

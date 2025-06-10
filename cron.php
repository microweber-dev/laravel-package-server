<?php

$dir = dirname(__FILE__);
//exec('cd '.$dir.' && php artisan queue:work --stop-when-empty >> /dev/null 2>&1');
exec('cd '.$dir.' && php artisan build-last-queued-package >> /dev/null 2>&1');

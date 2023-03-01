#!/bin/sh
while :
do
    php artisan build-last-queued-package
    sleep 5
done

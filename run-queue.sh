#!/bin/sh
while :
do
   php artisan build-last-queued-package
done

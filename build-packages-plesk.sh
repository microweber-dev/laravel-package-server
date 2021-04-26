php -d memory_limit=-1 artisan package-manager:change-satis-schema
php -d memory_limit=-1 vendor/composer/satis/bin/satis build ./satis.json /public --stats -n
mv /public/packages.json /public/original-packages.json
php -d memory_limit=-1 artisan package-manager:build

/opt/plesk/php/7.4/bin/php -d memory_limit=-1 artisan package-manager:change-satis-schema
/opt/plesk/php/7.4/bin/php -d memory_limit=-1 vendor/composer/satis/bin/satis build ./satis.json public --stats -n
mv public/packages.json public/original-packages.json
/opt/plesk/php/7.4/bin/php -d memory_limit=-1 artisan package-manager:build

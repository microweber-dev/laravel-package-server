php -d memory_limit=-1 artisan package-manager:change-satis-schema
php -d memory_limit=-1 vendor/composer/satis/bin/satis build ./satis.json public/compiled_packages --stats -n
php -d memory_limit=-1 artisan package-manager:build

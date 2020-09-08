dir=$(cd -P -- "$(dirname -- "$0")" && pwd -P)

php -d memory_limit=-1 $dir/artisan package-manager:change-satis-schema
php -d memory_limit=-1 $dir/vendor/composer/satis/bin/satis build $dir/satis.json public --stats -n
mv $dir/public/packages.json $dir/public/original-packages.json
php -d memory_limit=-1 $dir/artisan package-manager:build
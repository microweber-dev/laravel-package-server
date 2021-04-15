nohup php -d memory_limit=-1 artisan package-manager:change-satis-schema </dev/null >public/build-packages-output.log 2>public/build-packages-output.error &
nohup php -d memory_limit=-1 vendor/composer/satis/bin/satis build ./satis.json public --stats -n </dev/null >public/build-packages-output.log 2>public/build-packages-output.error &
nohup mv public/packages.json public/original-packages.json </dev/null >public/build-packages-output.log 2>public/build-packages-output.error &
nohup php -d memory_limit=-1 artisan package-manager:build </dev/null >public/build-packages-output.log 2>public/build-packages-output.error &

echo "Done! Packages is builded successfully!" >> public/build-packages-output.log

while getopts e: flag
do
    case "${flag}" in
        e) env=${OPTARG};;
    esac
done

php -d memory_limit=-1 artisan package-manager:change-satis-schema --env $env </dev/null >public/$env/build-packages-output.log 2>public/$env/build-packages-output.error &
php -d memory_limit=-1 vendor/composer/satis/bin/satis build ./satis.json public/$env --stats -n </dev/null >public/$env/build-packages-output.log 2>public/$env/build-packages-output.error &
mv public/$env/packages.json public/$env/original-packages.json </dev/null >public/$env/build-packages-output.log 2>public/$env/build-packages-output.error &
php -d memory_limit=-1 artisan package-manager:build --env $env </dev/null >public/$env/build-packages-output.log 2>public/$env/build-packages-output.error &

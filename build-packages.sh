while getopts e: flag
do
    case "${flag}" in
        e) env=${OPTARG};;
    esac
done

if ! [ -d "public/domains/$env" ]; then
    mkdir public/domains/$env
fi

if ! [ -d "public/domains/$env/meta" ]; then
    mkdir public/domains/$env/meta
fi

if ! [ -d "public/domains/$env/dist" ]; then
    mkdir public/domains/$env/dist
fi

if ! [ -d "public/domains/$env/include" ]; then
    mkdir public/domains/$env/include
fi

#example
#php -d memory_limit=-1 vendor/composer/satis/bin/satis build ./config/packages-bg.microweberapi.com/satis.json public/domains/packages-bg.microweberapi.com --stats

php -d memory_limit=-1 artisan package-manager:change-satis-schema --env $env </dev/null >public/domains/$env/build-packages-output.log 2>public/domains/$env/build-packages-output.error &
php -d memory_limit=-1 vendor/composer/satis/bin/satis build ./config/$env/satis.json public/domains/$env --stats -n </dev/null >public/domains/$env/build-packages-output.log 2>public/domains/$env/build-packages-output.error &
mv public/domains/$env/packages.json public/domains/$env/original-packages.json </dev/null >public/domains/$env/build-packages-output.log 2>public/domains/$env/build-packages-output.error &
php -d memory_limit=-1 artisan package-manager:build --env $env </dev/null >public/domains/$env/build-packages-output.log 2>public/domains/$env/build-packages-output.error &



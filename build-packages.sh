while getopts e: flag
do
    case "${flag}" in
        e) env=${OPTARG};;
    esac
done

if ! [ -d "public/$env" ]; then
    mkdir public/$env
fi

if ! [ -d "public/$env/meta" ]; then
    mkdir public/$env/meta
fi

if ! [ -d "public/$env/dist" ]; then
    mkdir public/$env/dist
fi

if ! [ -d "public/$env/include" ]; then
    mkdir public/$env/include
fi

php -d memory_limit=-1 artisan package-manager:change-satis-schema --env $env </dev/null >public/$env/build-packages-output.log 2>public/$env/build-packages-output.error &
php -d memory_limit=-1 vendor/composer/satis/bin/satis build ./config/$env/satis.json public/$env --stats -n </dev/null >public/$env/build-packages-output.log 2>public/$env/build-packages-output.error &
mv public/$env/packages.json public/$env/original-packages.json </dev/null >public/$env/build-packages-output.log 2>public/$env/build-packages-output.error &
php -d memory_limit=-1 artisan package-manager:build --env $env </dev/null >public/$env/build-packages-output.log 2>public/$env/build-packages-output.error &

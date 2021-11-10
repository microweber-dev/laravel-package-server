while getopts e: flag
do
    case "${flag}" in
        e) env=${OPTARG};;
    esac
done

if ! [ -d "public/domains" ]; then
    mkdir public/domains
fi

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

php -d memory_limit=-1 artisan package-manager:change-satis-schema --env $env
php -d memory_limit=-1 vendor/composer/satis/bin/satis build ./config/$env/satis.json public/domains-temp/$env --stats -n
mv public/domains-temp/$env/packages.json public/domains-temp/$env/original-packages.json
php -d memory_limit=-1 artisan package-manager:build --env $env --domains-dir domains-temp


echo 'done!'

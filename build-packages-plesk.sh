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

/opt/plesk/php/7.4/bin/php -d memory_limit=-1 artisan package-manager:change-satis-schema --env $env
/opt/plesk/php/7.4/bin/php -d memory_limit=-1 vendor/composer/satis/bin/satis build ./config/$env/satis.json public/domains/$env --stats -n
mv public/domains/$env/packages.json public/domains/$env/original-packages.json
/opt/plesk/php/7.4/bin/php -d memory_limit=-1 artisan package-manager:build --env $env

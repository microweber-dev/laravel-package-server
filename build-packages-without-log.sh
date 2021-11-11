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

if ! [ -d "../public_html/domains" ]; then
    mkdir "../public_html/domains"
fi

php -d memory_limit=-1 artisan package-manager:change-satis-schema --env $env
php -d memory_limit=-1 vendor/composer/satis/bin/satis build ./config/$env/satis.json public/domains/$env --stats -n

if [ -f "public/domains/$env/packages.json" ]; then
    mv public/domains/$env/packages.json public/domains/$env/original-packages.json
    php -d memory_limit=-1 artisan package-manager:build --env $env
fi

validatePackageManager=$(php -d memory_limit=-1 artisan package-manager:validate --env $env)
if [ $validatePackageManager != '1']
then
echo "Validation not passed"
exit
fi

if [ -f "public/domains/$env/original-packages.json" ]; then
    echo 'remove old domain env...'
    rm -rf "../public_html/domains/$env/"
    echo 'move domain env...'
    mv "public/domains/$env" "../public_html/domains/$env"
fi

echo 'done!'

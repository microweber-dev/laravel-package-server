FROM php:8.0.5
RUN apt-get update -y && apt-get install -y openssl zip unzip git curl libonig-dev libfreetype6-dev libjpeg-dev libcurl4-openssl-dev libpng-dev libmcrypt-dev libpng-dev libwebp-dev libxml2-dev libmagickwand-dev libkrb5-dev libbz2-dev libzip-dev libtidy-dev libc-client-dev
RUN apt-get install -y libc-client-dev
RUN apt-get install -y htop




RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN docker-php-ext-configure gd --with-freetype --with-webp --with-jpeg && \
    docker-php-ext-install gd

RUN docker-php-ext-install pdo_mysql zip dom curl mbstring intl
WORKDIR /app
COPY . /app
RUN cd /app/satis-builder && composer install --no-interaction --no-dev --prefer-dist --ignore-platform-reqs
RUN cd /app/ && composer install --no-interaction --no-dev --prefer-dist



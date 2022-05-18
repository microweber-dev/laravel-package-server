FROM php:8.1-apache


RUN apt update && apt install -y --no-install-recommends \
        git \
        zip \
        curl \
        libzip-dev \
        zlib1g-dev \
        unzip \
        libonig-dev \
        graphviz \
        libxml2-dev \
        libcurl4-openssl-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libwebp-dev \
        libpng-dev && \
    rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype --with-webp --with-jpeg && \
    docker-php-ext-install gd

RUN docker-php-ext-install pdo_mysql zip dom curl mbstring intl


#   apache configs + document root
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf


#  mod_rewrite for URL rewrite and mod_headers for .htaccess extra headers like Access-Control-Allow-Origin-
RUN a2enmod rewrite headers


RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY . /var/www/html

WORKDIR /var/www/html

RUN composer install --no-interaction --no-dev --prefer-dist

RUN chmod -R 777 /var/www/html
RUN a2enmod rewrite
RUN service apache2 restart



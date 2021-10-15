FROM php:7.3-fpm-alpine
# lumen packages

WORKDIR /var/www

RUN docker-php-ext-install mbstring tokenizer mysqli pdo_mysql

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

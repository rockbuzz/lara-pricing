FROM php:7.3-fpm-alpine

RUN apk add --no-cache $PHPIZE_DEPS bash \
    && pecl install pcov \
    && docker-php-ext-enable pcov

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

ENTRYPOINT ["php-fpm"]
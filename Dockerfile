FROM php:8.2-cli
WORKDIR /app
RUN docker-php-ext-install pdo pdo_mysql pdo_sqlite
COPY . /app
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --no-interaction --prefer-dist
CMD ["php", "example.php"]

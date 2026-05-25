FROM dunglas/frankenphp:php8.2-bookworm

# Install PHP extensions required by CodeIgniter + dompdf
RUN install-php-extensions mysqli pdo_mysql mbstring gd zip

# Get Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Sessions and logs must be writable
RUN chmod -R 777 /app/application/cache/ \
    && chmod -R 777 /app/application/logs/

EXPOSE 8080

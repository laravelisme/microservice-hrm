FROM dunglas/frankenphp:1-php8.3

WORKDIR /app

RUN apt-get update && apt-get install -y \
    git unzip libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql sockets pcntl

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . .

RUN composer install --no-dev --optimize-autoloader

EXPOSE 9000

CMD ["frankenphp", "run", "--config", "/app/Caddyfile"]

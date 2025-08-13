FROM php:8.2-cli-alpine

RUN apk add --no-cache postgresql-dev \
  && docker-php-ext-install pdo_pgsql

WORKDIR /app
CMD ["php", "/app/bin/run.php"]

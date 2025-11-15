FROM php:8.2-cli
COPY . /app
WORKDIR /app
CMD exec php -S 0.0.0.0:$PORTs
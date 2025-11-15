FROM php:8.2-cli

# Instala a extensão PostgreSQL
RUN docker-php-ext-install pdo_pgsql

# Copia os arquivos
COPY . /app
WORKDIR /app

# Inicia o servidor
CMD exec php -S 0.0.0.0:$PORT
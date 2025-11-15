FROM php:8.2-cli

# Instala dependências do sistema + extensão PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

# Copia o código
COPY . /app
WORKDIR /app

# Inicia o servidor
CMD exec php -S 0.0.0.0:$PORT
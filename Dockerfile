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
CMD exec php -S 0.0.0.0:$PORTFROM php:8.3-cli

# Instala dependências do sistema + extensões PHP necessárias
RUN apt-get update && apt-get install -y \
    git unzip curl \
    libpq-dev \
    && docker-php-ext-install pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

# Instala o Composer (versão oficial)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Define o diretório de trabalho
WORKDIR /app

# Copia primeiro os arquivos do Composer (melhor cache de layers)
COPY composer.json composer.lock ./

# Instala as dependências do Composer (isso resolve o erro do vendor/autoload.php)
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Agora copia o restante do código da aplicação
COPY . .

# Expõe a porta que o Render vai usar
EXPOSE $PORT

# Inicia o servidor PHP embutido (php -S) na porta definida pelo Render
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT} -t ."]
FROM php:8.2-cli

# Copia todos os arquivos para /app
COPY . /app
WORKDIR /app

# Expõe a porta que o Render usa
EXPOSE $PORT

# Comando que o Render executa
CMD exec php -S 0.0.0.0:$PORT
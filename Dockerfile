# Usando PHP 8.3 (Versão Estável)
FROM php:8.3-fpm

# Argumentos definidos no docker-compose
ARG user
ARG uid

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Limpar cache para diminuir o tamanho da imagem
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar extensões do PHP necessárias para o Laravel
# bcmath é CRUCIAL para trabalhar com dinheiro
RUN pecl install redis \
    && docker-php-ext-enable redis \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Obter o Composer mais recente
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiar configuração customizada do PHP-FPM
COPY ./docker/php-fpm/www.conf /usr/local/etc/php-fpm.d/www.conf

# Criar diretório de logs do PHP-FPM
RUN mkdir -p /var/log/php-fpm && \
    chown -R www-data:www-data /var/log/php-fpm

# Criar usuário do sistema para rodar o Composer e Artisan
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# Definir diretório de trabalho
WORKDIR /var/www

USER $user

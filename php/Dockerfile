FROM php:8.2-fpm-alpine

# Устанавливаем системные зависимости
RUN apk add --no-cache \
    postgresql-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev

# Устанавливаем расширения PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        gd \
        mbstring \
        zip \
        opcache

# Копируем конфигурацию PHP
COPY php.ini /usr/local/etc/php/conf.d/custom.ini

# Создаем пользователя для безопасности (не root)
RUN addgroup -g 1000 appuser && \
    adduser -u 1000 -G appuser -s /bin/sh -D appuser

# Рабочая директория
WORKDIR /var/www/html

# Меняем владельца файлов
RUN chown -R appuser:appuser /var/www/html

# Переключаемся на непривилегированного пользователя
USER appuser

EXPOSE 9000

CMD ["php-fpm"]
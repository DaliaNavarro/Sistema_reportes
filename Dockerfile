FROM php:8.3-apache

RUN apt-get update && apt-get install -y \
    libzip-dev \
    libxml2-dev \
    libpng-dev \
    libonig-dev \
    unzip \
    && docker-php-ext-install \
    pdo \
    pdo_mysql \
    zip \
    xml \
    mbstring \
    gd \
    && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite headers

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json ./

RUN composer install --no-interaction --prefer-dist

COPY . .

RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' \
    /etc/apache2/sites-available/000-default.conf \
    /etc/apache2/apache2.conf

RUN printf '<Directory /var/www/html/public>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>\n' > /etc/apache2/conf-available/sistema-reportes.conf

RUN a2enconf sistema-reportes

RUN mkdir -p \
    /var/www/html/storage/memorandums \
    /var/www/html/storage/generated

RUN chown -R www-data:www-data /var/www/html/storage

EXPOSE 80

CMD ["apache2-foreground"]

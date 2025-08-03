FROM php:8.1-apache

RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install zip pdo pdo_mysql

RUN a2enmod rewrite

RUN echo 'ServerName localhost\n\
<VirtualHost *:80>\n\
    ServerName localhost\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        Options Indexes FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . .

# Install dependencies (including dev dependencies for testing)
RUN composer install --optimize-autoloader

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache \
    && chmod -R 755 /var/www/html/public

RUN cp .env.example .env

RUN sed -i 's/APP_DEBUG=true/APP_DEBUG=true/' .env \
    && sed -i 's/DB_HOST=127.0.0.1/DB_HOST=mysql/' .env \
    && sed -i 's/DB_DATABASE=laravel/DB_DATABASE=hellochef/' .env \
    && sed -i 's/DB_USERNAME=root/DB_USERNAME=root/' .env \
    && sed -i 's/DB_PASSWORD=/DB_PASSWORD=password/' .env

RUN php artisan key:generate

EXPOSE 80

CMD ["apache2-foreground"] 
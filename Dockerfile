FROM php:8.2-apache

# PDO MySQL eklentisi
RUN docker-php-ext-install pdo_mysql

# PHP ayarları
RUN { \
      echo 'date.timezone = Europe/Istanbul'; \
      echo 'display_errors = Off'; \
      echo 'log_errors = On'; \
    } > /usr/local/etc/php/conf.d/magaza.ini

WORKDIR /var/www/html
COPY . .

# Sipariş logları için yazma izni
RUN mkdir -p logs && chown -R www-data:www-data logs

EXPOSE 80

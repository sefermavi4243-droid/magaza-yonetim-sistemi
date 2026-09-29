FROM php:8.2-apache

# PDO MySQL eklentisi
RUN docker-php-ext-install pdo_mysql

# PHP ayarları
RUN { \
      echo 'date.timezone = Europe/Istanbul'; \
      echo 'display_errors = Off'; \
      echo 'log_errors = On'; \
      echo 'expose_php = Off'; \
    } > /usr/local/etc/php/conf.d/magaza.ini

# Apache: sürüm bilgisini gizle, log klasörünü dışarı kapat, güvenlik başlıkları
RUN a2enmod headers \
 && { \
      echo 'ServerTokens Prod'; \
      echo 'ServerSignature Off'; \
      echo '<Directory /var/www/html/logs>'; \
      echo '    Require all denied'; \
      echo '</Directory>'; \
      echo 'Header always set X-Content-Type-Options "nosniff"'; \
      echo 'Header always set X-Frame-Options "SAMEORIGIN"'; \
      echo 'Header always set Referrer-Policy "strict-origin-when-cross-origin"'; \
    } > /etc/apache2/conf-available/zz-magaza.conf \
 && a2enconf zz-magaza

WORKDIR /var/www/html
COPY . .

# Sipariş logları için yazma izni
RUN mkdir -p logs && chown -R www-data:www-data logs

EXPOSE 80

FROM php:7.3-apache

# set default ServerName
RUN touch /etc/apache2/conf-available/servername.conf \
  && echo 'ServerName app.money-tracker' > /etc/apache2/conf-available/servername.conf \
  && a2enconf servername

# install php extensions
RUN apt-get update \
  && apt-get upgrade -y
RUN apt-get install -y apt-utils curl  default-mysql-client libzip-dev zlib1g-dev libicu-dev g++ --no-install-recommends
RUN docker-php-ext-configure intl \
  && docker-php-ext-install pdo_mysql zip intl

# enable mod_rewrite apache module
RUN a2enmod rewrite

# setup vhost
ADD .docker/money-tracker.vhost.conf /etc/apache2/sites-enabled/000-default.conf

# setup web directory
WORKDIR /var/www/money-tracker
ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_DOCUMENT_ROOT /var/www/money-tracker/public
RUN mkdir -p $APACHE_DOCUMENT_ROOT \
  && chown -R "$APACHE_RUN_USER:$APACHE_RUN_GROUP" "${APACHE_DOCUMENT_ROOT}"

# alias for php artisan
# allows us to call artisan without prefixing it with "php"
RUN echo '#!/bin/bash\nphp /var/www/money-tracker/artisan "$@"' > /usr/local/bin/artisan \
  && chmod +x /usr/local/bin/artisan

# select a php.ini config file; we use php.ini-development as it has "display_errors = On"
RUN cp $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini \
  && sed -i "s/expose_php = On/expose_php = Off/" $PHP_INI_DIR/php.ini

# set php error logging
RUN echo 'error_log = /var/www/money-tracker/storage/logs/php_error.log' >> $PHP_INI_DIR/conf.d/php-error_log.ini

# set php timezone
RUN echo 'date.timezone = "UTC"' >> $PHP_INI_DIR/conf.d/php-date.timezone.ini

# install xdebug
ARG DISABLE_XDEBUG
RUN if [ "$DISABLE_XDEBUG" = false ]; \
  then \
    pecl install xdebug-2.7.2; \
    docker-php-ext-enable xdebug; \
    XDEBUG_INI=`php --ini | grep xdebug | tr -d ,` \
      && echo "" >> $XDEBUG_INI \
      && echo "xdebug.coverage_enable=1" >> $XDEBUG_INI \
      && echo "xdebug.idekey=PHPSTORMDOCKER" >> $XDEBUG_INI \
      && echo "xdebug.remote_autostart=1" >> $XDEBUG_INI \
      && echo "xdebug.remote_enable=1" >> $XDEBUG_INI \
      && echo "xdebug.remote_host=dockerhost" >> $XDEBUG_INI \
      && echo "xdebug.remote_port=9000" >> $XDEBUG_INI; \
  fi;

# allow external access to port 9000 for xdebug
EXPOSE 9000
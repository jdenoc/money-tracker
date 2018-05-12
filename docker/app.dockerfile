FROM php:5.6-apache

# install php extensions
RUN apt-get update \
	&& apt-get install -y libmcrypt-dev mysql-client --no-install-recommends \
    && docker-php-ext-install mcrypt pdo_mysql \
	&& pecl install xdebug-2.5.5 \
    && docker-php-ext-enable xdebug

# enable mod_rewrite apache module
RUN a2enmod rewrite

# setup vhost
ADD docker/money-tracker.vhost.conf /etc/apache2/sites-enabled/000-default.conf

# setup web directory
WORKDIR /var/www/money-tracker
ENV APACHE_DOCUMENT_ROOT /var/www/money-tracker/public
RUN mkdir -p  $APACHE_DOCUMENT_ROOT \
	&& chown -R "$APACHE_RUN_USER:$APACHE_RUN_GROUP" "${APACHE_DOCUMENT_ROOT}"

# alias for php artisan
# allows us to call artisan without prefixing it with "php"
RUN echo '#!/bin/bash\nphp /var/www/money-tracker/artisan "$@"' > /usr/bin/artisan \
    && chmod +x /usr/bin/artisan

# setting APP_ENV environment variable so we can use the .env.docker file
ENV APP_ENV docker

# set php timezone
RUN echo 'date.timezone = "UTC"' >> $PHP_INI_DIR/conf.d/php-timezone.ini

# override some xdebug settings
RUN XDEBUG_INI=`php --ini | grep xdebug | tr -d ,` \
	&& echo "" >> $XDEBUG_INI \
	&& echo "xdebug.coverage_enable=1" >> $XDEBUG_INI \
	&& echo "xdebug.idekey=DOCKER" >> $XDEBUG_INI \
	&& echo "xdebug.remote_autostart=1" >> $XDEBUG_INI \
	&& echo "xdebug.remote_enable=1" >> $XDEBUG_INI \
	&& echo "xdebug.remote_host=dockerhost" >> $XDEBUG_INI \
	&& echo "xdebug.remote_port=9000" >> $XDEBUG_INI

# allow external access to port 9000 for xdebug
EXPOSE 9000
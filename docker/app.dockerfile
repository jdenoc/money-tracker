FROM php:5.6-apache

# install php extensions
RUN apt-get update \
	&& apt-get install -y libmcrypt-dev mysql-client zlib1g-dev --no-install-recommends \
    && docker-php-ext-install mcrypt pdo_mysql zip \
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
RUN echo '#!/bin/bash\nphp /var/www/money-tracker/artisan "$@"' > /usr/local/bin/artisan \
    && chmod +x /usr/local/bin/artisan

# select a php.ini file
RUN cp $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini \
	&& sed -i "s/;always_populate_raw_post_data = -1/always_populate_raw_post_data = -1/" $PHP_INI_DIR/php.ini

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
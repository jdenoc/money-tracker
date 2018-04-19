FROM php:5.6-apache

# install php extensions
RUN apt-get update \
	&& apt-get install -y libmcrypt-dev mysql-client --no-install-recommends \
    && docker-php-ext-install mcrypt pdo_mysql

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
RUN echo '#!/bin/bash\nphp /var/www/money-tracker/artisan "$@"' > /usr/bin/artisan && \
    chmod +x /usr/bin/artisan

# setting APP_ENV environment variable so we can use the .env.docker file
ENV APP_ENV docker
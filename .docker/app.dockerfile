FROM php:8.1-apache-bullseye

ENV DOCKER_LOG_STDOUT /proc/1/fd/1
ENV DOCKER_LOG_STDERR /proc/1/fd/2

# install generic packages & extensions
RUN apt-get update --fix-missing \
  && apt-get upgrade -y
RUN apt-get install -y apt-utils curl zlib1g-dev libicu-dev g++ --no-install-recommends

# set default ServerName
COPY .docker/conf/apache2-servername.conf /etc/apache2/conf-available/servername.conf
RUN a2enconf servername

# modify apache logging
RUN APACHE_LOG_DIR=/var/log/apache2 \
  && rm -rf $APACHE_LOG_DIR/{access,error,other_vhosts_access}.log \
  && ln -sf $DOCKER_LOG_STDOUT $APACHE_LOG_DIR/access.log \
  && ln -sf $DOCKER_LOG_STDERR $APACHE_LOG_DIR/error.log \
  && ln -sf $DOCKER_LOG_STDOUT $APACHE_LOG_DIR/other_vhosts_access.log
COPY .docker/conf/apache2-dockerlog.conf /etc/apache2/conf-available/dockerlog.conf
RUN a2enconf dockerlog

# enable mod_rewrite apache module
RUN a2enmod rewrite

# setup vhost
COPY .docker/conf/money-tracker.vhost.conf /etc/apache2/sites-available/000-default.conf

# setup web directory
ENV WORK_DIR /var/www/money-tracker
WORKDIR $WORK_DIR
ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_DOCUMENT_ROOT $WORK_DIR/public
RUN mkdir -p $APACHE_DOCUMENT_ROOT \
  && chown -R "$APACHE_RUN_USER:$APACHE_RUN_GROUP" "${APACHE_DOCUMENT_ROOT}"

# create php log directory
ENV PHP_LOG_DIR=/var/log/php
RUN mkdir -p $PHP_LOG_DIR \
  && chown "$APACHE_RUN_USER:$APACHE_RUN_GROUP" "${PHP_LOG_DIR}"

# install php intl extension
RUN docker-php-ext-install intl

# install php pcntl extension
RUN docker-php-ext-install pcntl

# install php mysql extension
RUN apt-get install -y default-mysql-client --no-install-recommends
RUN docker-php-ext-install pdo_mysql

# install php zip extension
RUN apt-get install -y libzip-dev --no-install-recommends
RUN docker-php-ext-install zip

# install php gd extension
RUN apt-get install -y libpng-dev libjpeg-dev libfreetype6-dev --no-install-recommends
RUN docker-php-ext-configure gd --enable-gd --with-jpeg --with-freetype
RUN docker-php-ext-install gd

# install php igbinary & memcached extensions
RUN apt-get install -y libmemcached-dev --no-install-recommends \
  && pecl install igbinary-3.2.7 \
  && pecl install memcached-3.2.0
RUN docker-php-ext-enable igbinary \
  && docker-php-ext-enable memcached

# install xdebug
ARG ENABLE_XDEBUG
RUN if [ "$ENABLE_XDEBUG" = true ]; \
  then \
    XDEBUG_LOG=$PHP_LOG_DIR/xdebug.log \
      && touch $XDEBUG_LOG \
      && chgrp $APACHE_RUN_GROUP $XDEBUG_LOG \
      && chmod g+w $XDEBUG_LOG; \
    pecl install xdebug-3.1.3; \
    docker-php-ext-enable xdebug; \
    XDEBUG_INI=`php --ini | grep xdebug | tr -d ,` \
      && echo "" >> $XDEBUG_INI \
      && echo "[xdebug]" >> $XDEBUG_INI \
      && echo "xdebug.mode=develop,coverage,trace" >> $XDEBUG_INI \
      && echo "xdebug.start_with_request=yes" >> $XDEBUG_INI \
      && echo "xdebug.client_host=host.docker.internal" >> $XDEBUG_INI \
      && echo "xdebug.log=$XDEBUG_LOG" >> $XDEBUG_INI; \
  fi;

# allow external access to port 9003 for xdebug
EXPOSE 9003

# clean up after installs
RUN apt-get autoremove \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# alias for php artisan
# allows us to call artisan without prefixing it with "php"
RUN echo '#!/usr/bin/env bash\nphp ${WORK_DIR}/artisan "$@"' > /usr/local/bin/artisan \
  && chmod +x /usr/local/bin/artisan

# select a php.ini config file; we use php.ini-development as it has "display_errors = On"
RUN cp $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini
COPY .docker/conf/php-allow_url_fopen.ini $PHP_INI_DIR/conf.d/php-allow_url_fopen.ini
COPY .docker/conf/php-date_timezone.ini $PHP_INI_DIR/conf.d/php-date.timezone.ini
COPY .docker/conf/php-error_log.ini $PHP_INI_DIR/conf.d/php-error_log.ini
COPY .docker/conf/php-expose_php.ini $PHP_INI_DIR/conf.d/php-expose_php.ini

# health-check
COPY .docker/healthcheck/app-health-check.sh /usr/local/bin/app-health-check
RUN chmod +x /usr/local/bin/app-health-check
HEALTHCHECK --timeout=5s --retries=10 \
  CMD app-health-check
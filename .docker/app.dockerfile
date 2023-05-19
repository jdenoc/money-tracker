FROM php:8.0-apache

ENV DOCKER_LOG_STDOUT /proc/1/fd/1
ENV DOCKER_LOG_STDERR /proc/1/fd/2

# install generic packages & extensions
RUN apt-get update --fix-missing \
  && apt-get upgrade -y
RUN apt-get install -y apt-utils curl git zlib1g-dev g++ --no-install-recommends

# set default ServerName
RUN touch $APACHE_CONFDIR/conf-available/servername.conf \
  && echo 'ServerName app.money-tracker' > $APACHE_CONFDIR/conf-available/servername.conf \
  && a2enconf servername

# modify apache logging
RUN APACHE_LOG_DIR=/var/log/apache2 \
  && rm -rf $APACHE_LOG_DIR/{access,error,other_vhosts_access}.log \
  && ln -sf $DOCKER_LOG_STDOUT $APACHE_LOG_DIR/access.log \
  && ln -sf $DOCKER_LOG_STDERR $APACHE_LOG_DIR/error.log \
  && ln -sf $DOCKER_LOG_STDOUT $APACHE_LOG_DIR/other_vhosts_access.log
RUN echo 'LogFormat "%t [apache] %H %m %U %q | status:%>s" docker_log' > $APACHE_CONFDIR/conf-available/dockerlog.conf \
  && a2enconf dockerlog

# enable mod_rewrite apache module
RUN a2enmod rewrite

# setup vhost
COPY .docker/money-tracker.vhost.conf $APACHE_CONFDIR/sites-available/000-default.conf

# setup web directory
ENV WORK_DIR /var/www/money-tracker
WORKDIR $WORK_DIR
ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_DOCUMENT_ROOT $WORK_DIR/public
RUN mkdir -p $APACHE_DOCUMENT_ROOT \
  && chown -R "$APACHE_RUN_USER:$APACHE_RUN_GROUP" "${APACHE_DOCUMENT_ROOT}"

# create php log directory
ENV PHP_LOG_DIR /var/log/php
RUN mkdir -p $PHP_LOG_DIR \
  && chown "$APACHE_RUN_USER:$APACHE_RUN_GROUP" "${PHP_LOG_DIR}"

# install php intl extension
RUN apt-get install -y libicu-dev
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
RUN apt-get install -y libmemcached-dev \
  && pecl install igbinary-3.2.7 \
  && pecl install memcached-3.2.0
RUN docker-php-ext-enable igbinary \
  && docker-php-ext-enable memcached

# install xdebug
ARG DISABLE_XDEBUG
RUN if [ "$DISABLE_XDEBUG" = false ]; \
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
  && apt-get clean

# alias for php artisan
# allows us to call artisan without prefixing it with "php"
RUN echo '#!/usr/bin/env bash\nphp ${WORK_DIR}/artisan "$@"' > /usr/local/bin/artisan \
  && chmod +x /usr/local/bin/artisan

# select a php.ini config file; we use php.ini-development as it has "display_errors = On"
RUN cp $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini

RUN echo "expose_php = Off" > $PHP_INI_DIR/conf.d/php-expose_php.ini
RUN echo "allow_url_fopen = On" > $PHP_INI_DIR/conf.d/php-allow_url_fopen.ini
# set php error logging
RUN PHP_ERROR_LOG=$PHP_LOG_DIR/errors.log \
  && touch $PHP_ERROR_LOG \
  && chgrp $APACHE_RUN_GROUP $PHP_ERROR_LOG \
  && chmod g+w $PHP_ERROR_LOG
RUN echo "error_log = $PHP_ERROR_LOG" > $PHP_INI_DIR/conf.d/php-error_log.ini
# set php timezone
RUN echo 'date.timezone = "UTC"' > $PHP_INI_DIR/conf.d/php-date.timezone.ini

# install composer (LTS)
RUN EXPECTED_CHECKSUM=$(curl -s https://composer.github.io/installer.sig) \
  && COMPOSER_SETUP=/opt/composer-setup.php \
  && CHECKSUM_FILE=/opt/composer-setup.checksum \
  && echo "$EXPECTED_CHECKSUM $COMPOSER_SETUP" > $CHECKSUM_FILE \
  && curl -o $COMPOSER_SETUP https://getcomposer.org/installer \
  && sha384sum --check $CHECKSUM_FILE; \
  if [ $? -ne 0 ]; then \
    echo 'ERROR: Invalid installer checksum' \
    rm -rf $COMPOSER_SETUP \
    exit 1; \
  fi; \
  php $COMPOSER_SETUP --2.2 --install-dir=/usr/local/bin/ --filename=composer \
  && rm -rf $COMPOSER_SETUP

# health-check
COPY .docker/healthcheck/app-health-check.sh /usr/local/bin/app-health-check
RUN chmod +x /usr/local/bin/app-health-check
HEALTHCHECK --timeout=5s --retries=10 \
  CMD app-health-check
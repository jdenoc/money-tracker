FROM php:8.1-cli

# update stuff
RUN apt-get update --fix-missing \
  && apt-get upgrade -y \
  && apt-get install -y git

# host access
RUN apt-get install -y tini \
  && ln -s /usr/bin/tini /sbin/tini
RUN curl -L https://raw.githubusercontent.com/composer/docker/096f0c28275343aa6e530c7ead34dcef3b79323f/lts/docker-entrypoint.sh --output /docker-entrypoint.sh \
  && chmod +x /docker-entrypoint.sh
WORKDIR /app
ENTRYPOINT ["/docker-entrypoint.sh"]
CMD ["composer"]

# install php zip extension
RUN apt-get install -y libzip-dev --no-install-recommends
RUN docker-php-ext-install zip

# install php gd extension
RUN apt-get install -y libpng-dev libjpeg-dev libfreetype6-dev --no-install-recommends
RUN docker-php-ext-configure gd --enable-gd --with-jpeg --with-freetype
RUN docker-php-ext-install gd

# install php intl extension
RUN apt-get install -y libicu-dev
RUN docker-php-ext-install intl

# install composer (LTS)
RUN EXPECTED_CHECKSUM="$(php -r 'copy("https://composer.github.io/installer.sig", "php://stdout");')" \
  && php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
  && ACTUAL_CHECKSUM="$(php -r "echo hash_file('sha384', 'composer-setup.php');")"; \
  if [ "$EXPECTED_CHECKSUM" != "$ACTUAL_CHECKSUM" ]; \
  then \
      >&2 echo 'ERROR: Invalid installer checksum' \
      rm composer-setup.php \
      exit 1; \
  fi; \
  php composer-setup.php --2.2 --install-dir=/usr/local/bin/ --filename=composer \
  && php -r "unlink('composer-setup.php');"

FROM composer:2.1.11

# uses PHP 8.0

# install php gd extension
RUN apk add zlib-dev libpng-dev jpeg-dev libjpeg-turbo-dev freetype-dev
RUN docker-php-ext-configure gd --enable-gd --with-jpeg --with-freetype
RUN docker-php-ext-install gd
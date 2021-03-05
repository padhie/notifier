FROM php:7.4-fpm
WORKDIR "/application"

RUN apt-get update
RUN apt-get upgrade
RUN apt-get -y --no-install-recommends install nano git zip unzip iputils-ping
RUN apt-get -y --no-install-recommends install wget curl
RUN apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

# Install selected extensions and other stuff
RUN apt-get update
RUN apt-get upgrade
RUN apt-get -y --no-install-recommends install zlib1g-dev libicu-dev g++
RUN docker-php-ext-install mysqli pdo pdo_mysql
RUN docker-php-ext-configure intl
RUN docker-php-ext-install intl
RUN docker-php-ext-install opcache

# composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer && \
    php -r "unlink('composer-setup.php');"

# Install xdebug
RUN yes | pecl install xdebug \
    && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_autostart=off" >> /usr/local/etc/php/conf.d/xdebug.ini
RUN rm /usr/local/etc/php/conf.d/xdebug.ini && ln -s /application/tools/docker/php-fpm/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# set aliases
RUN echo 'alias l="ls -a"' >> ~/.bashrc
RUN echo 'alias ll="ls -l"' >> ~/.bashrc
RUN echo 'alias la="ls -la"' >> ~/.bashrc

FROM wl00581438/php8.0-oci8-node14:v1.0.2

ARG PROJECT_PATH=.
ARG ENV=.env
ARG SUPERVISOR_PATH=./supervisor
LABEL maintainer xxxx@xxxxx.com

RUN echo 'post_max_size = 100M' >> /usr/local/etc/php/conf.d/docker-php-memlimit.ini
RUN echo 'upload_max_filesize = 100M' >> /usr/local/etc/php/conf.d/docker-php-memlimit.ini
RUN echo 'memory_limit = 2G' >> /usr/local/etc/php/conf.d/docker-php-memlimit.ini

RUN sed -i 's/deb.debian.org/free.nchc.org.tw/g' /etc/apt/sources.list
RUN apt update && apt install -y \
    libzip-dev \
    zip \
    supervisor 
RUN docker-php-ext-install pcntl zip

WORKDIR /var/www/html/
# composer
COPY ${PROJECT_PATH}/composer.* ./
RUN composer install --no-scripts
# 修改cas
# RUN sed -i "s/_server\['base_url'\]\ \=\ 'https/_server\['base_url'\]\ \='http/g" ./vendor/apereo/phpcas/source/CAS/Client.php 

# source code
COPY ${PROJECT_PATH} ./
COPY ./${ENV} .env
# COPY ${SUPERVISOR_PATH}/*.conf /etc/supervisor/conf.d/

RUN mkdir -p ./logs/queue \
    && chown -R www-data:www-data ./logs

RUN chmod -R 777 storage

RUN php artisan optimize
RUN php artisan storage:link
RUN php artisan route:cache
RUN php artisan config:cache

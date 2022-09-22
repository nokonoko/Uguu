FROM php:8.1-alpine

ARG VERSION


RUN apk add --no-cache $PHPIZE_DEPS \
	php-fpm zlib-dev libzip-dev libgomp; \
	docker-php-ext-install intl pcntl gd exif zip pdo mysql dom; \
    apk del $PHPIZE_DEPS; \
    rm -rf /tmp/pear;

# Install the needed software
RUN apk add --no-cache sqlite nodejs git npm bash build-base supervisor curl wget nginx
#php8-fileinfo php8-pdo php8-tokenizer php8-dom php8-iconv php8-xml php8-simplexml \
#php8-sqlite3 php8-opcache php8-mbstring php8-phar php8-openssl

# Create the www-data user and group
#RUN set -x ; \
#  addgroup -g 82 -S www-data ; \
#  adduser -u 82 -D -S -G www-data www-data && exit 0 ; exit 1

# Copy supervisor conf file
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Set default workdir
WORKDIR  /var/www/

COPY docker/docker-entrypoint.sh .

# Decompress into Docker
COPY docker/uguuForDocker.tar.gz /var/www/
RUN mkdir /var/www/uguu
RUN tar -xvf uguuForDocker.tar.gz -C uguu

# Create the needed directories
RUN mkdir /var/www/files && \
    mkdir /var/www/db

# Create the Sqlite DB
RUN sqlite3 /var/www/db/uguu.sq3 -init /var/www/uguu/src/static/dbSchemas/sqlite_schema.sql && \
    chown -R www-data:www-data /var/www && \
    chmod -R 775 /var/www/

# Fix script paths
RUN chmod a+x /var/www/uguu/src/static/scripts/checkdb.sh && \
    chmod a+x /var/www/uguu/src/static/scripts/checkfiles.sh && \
    sed -i 's#/path/to/files/#/var/www/uguu/files/#g' /var/www/uguu/src/static/scripts/checkfiles.sh && \
    sed -i 's#/path/to/db/uguu.sq3#/var/www/db/uguu.sq3#g' /var/www/uguu/src/static/scripts/checkdb.sh

# Add scripts to cron
RUN echo "0,30 * * * * bash /var/www/uguu/src/static/scripts/checkfiles.sh" >> /var/spool/cron/crontabs/www-data && \
    echo "0,30 * * * * bash /var/www/uguu/src/static/scripts/checkdb.sh" >> /var/spool/cron/crontabs/www-data

# Copy Nginx Server conf
COPY docker/nginx/uguu.conf /etc/nginx/http.d/

# Copy SSL certs
COPY docker/ssl /etc/ssl

# Copy PHP config
COPY docker/php/php.ini /etc/php8/
COPY docker/php/www.conf /etc/php8/php-fpm.d/
RUN mkdir /var/run/php

# Copy Uguu config
COPY src/config.json /var/www/uguu/config.json

# Give permissions to www-data
RUN chown -R www-data:www-data /run /var/lib/nginx /var/log/nginx /etc/nginx /etc/php8 /var/log/php8 /var/run/php

# Change user to www-data
USER www-data

# Expose port 80 from the container
EXPOSE 80

# Expose port 443 from the container
EXPOSE 443

# Load entrypoint
ENTRYPOINT [ "bash", "/var/www/docker-entrypoint.sh" ]
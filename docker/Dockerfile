FROM --platform=linux/amd64 debian:bullseye-slim

# Install needed software
RUN apt-get update

RUN apt-get install -y lsb-release ca-certificates apt-transport-https software-properties-common gnupg2 curl cron socat
RUN echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | tee /etc/apt/sources.list.d/sury-php.list
RUN curl -fsSL  https://packages.sury.org/php/apt.gpg| gpg --dearmor -o /etc/apt/trusted.gpg.d/sury-keyring.gpg
RUN apt-get update
RUN apt-get upgrade -y
RUN curl -o nodejssetup.sh https://deb.nodesource.com/setup_19.x
RUN chmod a+x nodejssetup.sh
RUN ./nodejssetup.sh
RUN apt-get install -y nodejs gcc g++ make
RUN apt-get install -y build-essential nginx-full php8.1-fpm php8.1 sqlite3 php8.1-sqlite3 \
                       php8.1-curl php8.1-cli php8.1-lz4 \
                       php8.1-mcrypt php8.1-mysql php8.1-xdebug php8.1-zip \
                       php8.1-common php8.1-readline php8.1-bcmath php8.1-common php8.1-xml


ARG DOMAIN
ENV DOMAIN=$DOMAIN
ARG FILE_DOMAIN
ENV FILE_DOMAIN=$FILE_DOMAIN
ARG CONTACT_EMAIL
ENV CONTACT_EMAIL=$CONTACT_EMAIL
ARG MAX_SIZE
ENV MAX_SIZE=$MAX_SIZE

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
RUN sqlite3 /var/www/db/uguuDB.sq3 -init /var/www/uguu/src/static/dbSchemas/sqlite_schema.sql "" && \
    chown -R www-data:www-data /var/www && \
    chmod -R 775 /var/www/

# Add scripts to cron
RUN echo "0,30 * * * * bash /var/www/uguu/src/static/scripts/checkfiles.sh" >> /var/spool/cron/crontabs/www-data && \
    echo "0,30 * * * * bash /var/www/uguu/src/static/scripts/checkdb.sh" >> /var/spool/cron/crontabs/www-data

# Fix script paths
RUN chmod a+x /var/www/uguu/src/static/scripts/checkdb.sh && \
    chmod a+x /var/www/uguu/src/static/scripts/checkfiles.sh && \
    sed -i 's#/path/to/files/#/var/www/uguu/files/#g' /var/www/uguu/src/static/scripts/checkfiles.sh && \
    sed -i 's#/path/to/db/uguu.sq3#/var/www/db/uguu.sq3#g' /var/www/uguu/src/static/scripts/checkdb.sh

# Copy Nginx Server conf
COPY docker/nginx/uguu.conf /etc/nginx/sites-enabled/uguu.conf
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf

# Modify nginx values
RUN sed -i "s#XMAINDOMAINX#${DOMAIN}#g" /etc/nginx/sites-enabled/uguu.conf
RUN sed -i "s#XFILESDOMAINX#${FILE_DOMAIN}#g" /etc/nginx/sites-enabled/uguu.conf
RUN sed -i "s#client_max_body_size 128M#client_max_body_size ${MAX_SIZE}M#g" /etc/nginx/nginx.conf

# Modify php-fpm values
RUN sed -i "s#post_max_size = 8M#post_max_size = ${MAX_SIZE}M#g" /etc/php/8.1/fpm/php.ini
RUN sed -i "s#upload_max_filesize = 2M#upload_max_filesize = ${MAX_SIZE}M#g" /etc/php/8.1/fpm/php.ini

# Copy Uguu config
COPY src/config.json /var/www/uguu/config.json

# Expose port 80 from the container
EXPOSE 80

# Expose port 443 from the container
EXPOSE 443

#RUN service nginx restart
# Change user to www-data
#USER www-data

#RUN cd /var/www/uguu && make && make install

RUN curl -o acmeinstall.sh https://get.acme.sh
RUN chmod a+x acmeinstall.sh
RUN ./acmeinstall.sh
# Load entrypoint
ENTRYPOINT [ "bash", "/var/www/docker-entrypoint.sh" ]
FROM ubuntu:latest

ARG VERSION

RUN apt-get update && \
    apt install -y software-properties-common && \
    add-apt-repository ppa:ondrej/php && \
    apt-get install -y build-essential nginx-full php8.0-fpm php8.0 sqlite3 php8.0-sqlite3 nodejs certbot git npm cron gettext-base && \
    apt clean 

WORKDIR  /var/www/

COPY docker/docker-entrypoint.sh .

ADD https://github.com/nokonoko/Uguu/archive/refs/tags/v${VERSION}.tar.gz v${VERSION}.tar.gz

RUN tar xvf v${VERSION}.tar.gz && \
    mv Uguu-${VERSION}/ uguu

RUN mkdir /var/www/uguu/dist && \
    mkdir /var/www/db && \
    mkdir /var/www/files
    
RUN sqlite3 /var/www/db/uguu.sq3 -init /var/www/uguu/sqlite_schema.sql && \
    chown -R www-data:www-data /var/www/db /var/www/files && \
    chmod -R 775 /var/www/

RUN chmod a+x /var/www/uguu/checkdb.sh && \
    chmod a+x /var/www/uguu/checkfiles.sh && \
    sed -i 's#/path/to/files/#/var/www/uguu/files/#g' /var/www/uguu/checkfiles.sh && \
    sed -i 's#/path/to/db/uguu.sq3#/var/www/db/uguu.sq3#g' /var/www/uguu/checkdb.sh 

RUN echo "0,30 * * * * bash /var/www/uguu/checkfiles.sh" >> /var/spool/cron/crontabs/root && \
    echo "0,30 * * * * bash /var/www/uguu/checkdb.sh" >> /var/spool/cron/crontabs/root
      
COPY docker/uguu.conf /etc/nginx/conf.d/
     
COPY dist.json /var/www/uguu/_dist.json 

EXPOSE 80

ENTRYPOINT [ "bash", "/var/www/docker-entrypoint.sh" ]

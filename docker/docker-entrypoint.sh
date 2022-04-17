#!/bin/bash
envsubst < /var/www/uguu/_dist.json > /var/www/uguu/dist.json 
cd /var/www/uguu/
make
make install
service php8.0-fpm start
service cron start
nginx -g 'daemon off;'

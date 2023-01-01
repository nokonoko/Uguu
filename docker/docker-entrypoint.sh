#!/bin/bash
cd /var/www/uguu
npm install
make
make install
/root/.acme.sh/acme.sh --set-default-ca --server letsencrypt
/root/.acme.sh/acme.sh --issue -d $DOMAIN -w /var/www/uguu/dist/public/
/root/.acme.sh/acme.sh --issue -d $FILE_DOMAIN -w /var/www/files/
service nginx start
service php8.1-fpm start
tail -f /var/log/nginx/access.log
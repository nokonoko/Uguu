#!/bin/bash
cd /var/www/uguu || exit
npm install
make
make install
service nginx stop
rm /etc/nginx/sites-enabled/default
/root/.acme.sh/acme.sh --set-default-ca --server letsencrypt
/root/.acme.sh/acme.sh --issue --standalone -d "$DOMAIN" -d "$FILE_DOMAIN"
service nginx start
service php8.1-fpm start
tail -f /dev/null
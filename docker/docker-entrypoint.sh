#!/bin/bash
cd /var/www/uguu/
make
make install
/usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf

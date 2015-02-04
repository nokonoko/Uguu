#! /bin/sh
find /home/neku/www/files/ -mmin +30 -exec rm -f {} \;

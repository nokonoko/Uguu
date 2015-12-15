#! /bin/sh
find /home/neku/www/files/ -mmin +1440 -exec rm -f {} \;

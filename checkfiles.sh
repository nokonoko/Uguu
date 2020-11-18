#! /bin/sh
find /path/to/files/ -mmin +1440 -exec rm -f {} \;
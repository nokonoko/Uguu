#! /bin/sh
hours=$((XXX*60))
find /path/to/files/ -mmin +1440 -exec rm -f {} \;
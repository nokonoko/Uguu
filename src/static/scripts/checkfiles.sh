#! /bin/sh
hours=$((XXX*60))
find /path/to/files/ -mmin +$hours -exec rm -f {} \;
#! /bin/sh
find $(grep -oP '"CONFIG_FILES_PATH", "\K(.*)(?=")' config.php) -mtime +$(grep -oP '"CONFIG_MAX_RETENTION_TIME", "\K(.*)(?=")' config.php) -exec rm -f {} \;

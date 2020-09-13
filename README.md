# About
[Uguu.se](https://Uguu.se) no longer runs this code but instead a modified version of [Pomf](https://github.com/pomf/pomf), will be uploading that code soon.


# Tested with:
* Apache (PHP 5.4) on Ubuntu 14.04 LTS
* Apache (PHP 5.6) on Debian 8 Jessie
* Apache (PHP 5.6.33 (remi-php56)) on CentOS 6.9
* Nginx+PHP5-FPM (PHP 5.4) on Debian 7 Wheezy
* Nginx+PHP5-FPM (PHP 5.6) on Debian 8 Jessie
* Nginx+PHP7-FPM (PHP 7.0) on Debian 9 Stretch
* [Caddy](https://caddyserver.com/) + php7.0-fpm on Ubuntu 16.04.4 LTS

# Install:

* Deploy base code, for example with `git clone https://github.com/nokonoko/Uguu.git`
* Modify includes/config.php (copy config.template.php as a starting point) to set up the main options for Uguu.
* Some file extensions are blocked by default, this can be changed via includes/config.php's CONFIG_BLOCKED_EXTENSIONS value.
* Copy `rain/template/footer.template.html` as `rain/template/footer.html` and personalize the footer as you wish
* Execute check.sh regularly with cron to delete old files: `crontab -e` and add `0,15,30,45 * * * * cd /path/to/uguu/includes && bash check.sh` (or adapt if you know how cron works).
* Make the Uguu/public/files and Uguu/rain/cache directory modifiable by the web server user:
`chown -R www-data:www-data /path/to/Uguu/public/files` and `chown -R www-data:www-data /path/to/Uguu/rain/cache`
* Make sure the Uguu/public/files folder is not indexable, you may use a virtual host config similar to this one using Apache:
* If you intend to allow uploading files larger than 2 MB, you may also need to increase POST size limits in php.ini and webserver configuration. For PHP, modify upload_max_filesize and post_max_size values. The configuration option for Nginx webserver is client_max_body_size and LimitRequestBody for Apache.
```
<VirtualHost *:80>
        ServerName path.to.uguu

        DocumentRoot /var/www/Uguu/
        <Directory /var/www/Uguu/>
                AllowOverride All
                Require all granted
        </Directory>

        Alias "/files" "/var/www/Uguu/public/files/"
        <Directory /var/www/Uguu/public/files/>
          <Files *>
            SetHandler default-handler
          </Files>
          AllowOverride None
          Options -Indexes
          Require all granted
        </Directory>

</VirtualHost>
```

Or something like this using Nginx+PHP-FPM:

uguu.se
```
server{
    listen              104.243.35.197:80;
    server_name         uguu.se www.uguu.se;

    root                        /home/neku/www/uguu/;
    autoindex           off;
    index                       index.html index.php;

    location ~* \.php$ {
        fastcgi_pass unix:/var/run/php5-fpm.sock;
        fastcgi_intercept_errors on;
        fastcgi_index index.php;
        fastcgi_split_path_info ^(.+\.php)(.*)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

        error_page 404 /404.html;
        error_page 403 /404.html;
        location /404.html {
        root /home/neku/www;
        }
}
```

a.uguu.se (notice that scripts e.g PHP will NOT be executed from this subdomain)
```
server{
    listen          104.243.35.197:80;
    server_name     a.uguu.se www.a.uguu.se;

    root            /home/neku/www/files;
    autoindex       off;
    index           index.html;

        error_page      404 /404.html;
        error_page      403 /404.html;
        location /404.html {
        root /home/neku/www;
        }
}
```

Or something like this for usage with caddy:
```
uguu.se {
    fastcgi / /var/run/php/php7.0-fpm.sock php
    root /home/neku/www
}

a.uguu.se {
    root /home/neku/www/files
}
```


# Using the API

  * Leaving POST value 'name' empty will cause it to save using the original filename.
  * Leaving POST value 'randomname' empty will cause it to use original filename or custom name if 'name' is set to file.ext.

  * Putting anything into POST value 'randomname' will cause it to return a random filename + ext (xxxxxx.ext).
  * Putting a custom name into POST value 'name' will cause it to return a custom filename (yourpick.ext).

  E.g:
  * curl -i -F name=test.jpg -F file=@localfile.jpg http://path.to.uguu/api.php?d=upload (HTML Response)
  * curl -i -F name=test.jpg -F file=@localfile.jpg http://path.to.uguu/api.php?d=upload-tool (Plain text Response)

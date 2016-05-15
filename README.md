# About
[Uguu.se](http://uguu.se) source code, stores files and deletes after X amount of time.

# Tested with:
* Nginx+PHP5-FPM (PHP 5.4) on Debian 7 Wheezy
* Apache (PHP 5.4) on Ubuntu 14.04 LTS
* Apache (PHP 5.6) on Debian 8 Jessie
* Nginx+PHP5-FPM (PHP 5.6) on Debian 8 Jessie

# Install:

* Deploy base code, for example with `git clone https://github.com/nokonoko/Uguu.git`
* Modify includes/config.php (copy config.template.php as a starting point) to set up the main options for Uguu.
* Some file extensions are blocked by default, this can be changed via includes/config.php's CONFIG_BLOCKED_EXTENSIONS value.
* Copy `rain/template/footer.template.html` as `rain/template/footer.html` and personalize the footer as you wish
* Execute check.sh regularly with cron to delete old files: `crontab -e` and add `0,15,30,45 * * * * bash /path/to/check.sh` (or adapt if you know how cron works).
* Make the Uguu/public/files and Uguu/rain/cache directory modifiable by the web server user:
`chown -R www-data:www-data /path/to/Uguu/public/files` and `chown -R www-data:www-data /path/to/Uguu/rain/cache`
* Make sure the Uguu/public/files folder is not indexable, you may use a virtual host config similar to this one:

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

# Using the API

  * Leaving POST value 'name' empty will cause it to save using the original filename.
  * Leaving POST value 'randomname' empty will cause it to use original filename or custom name if 'name' is set to file.ext.

  * Putting anything into POST value 'randomname' will cause it to return a random filename + ext (xxxxxx.ext).
  * Putting a custom name into POST value 'name' will cause it to return a custom filename (yourpick.ext).

  E.g:
  * curl -i -F name=test.jpg -F file=@localfile.jpg http://path.to.uguu/api.php?d=upload (HTML Response)
  * curl -i -F name=test.jpg -F file=@localfile.jpg http://path.to.uguu/api.php?d=upload-tool (Plain text Response)

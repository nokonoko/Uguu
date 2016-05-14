# About
Temp file sharing application source code, stores files and deletes after X amount of time. Forked from Uguu.se available [here](https://github.com/nokonoko/uguu).

# Install
Tested with:
* Nginx+PHP5-FPM (PHP 5.4) on Debian 7 Wheezy
* Apache (PHP 5.4) on Ubuntu 14.04 LTS
* Apache (PHP 5.6) on Debian 8 Jessie
* Nginx+PHP5-FPM (PHP 5.6) on Debian 8 Jessie

Modify
* Modify includes/config.php (copy config.template.php as a starting point) to determine the path and URL to the app, the default retention time and other config points
* Execute check.sh with cron to delete old files: `crontab -e` and add `0,15,30,45 * * * * bash /path/to/check.sh` (or adapt if you know how cron works).
* Some extensions are blocked by default, this can be changed via includes/config.php's CONFIG_BLOCKED_EXTENSIONS value.

Make the uguu/public/files and uguu/rain/cache directory modifiable to the web server user:
`chown -R www-data:www-data /path/to/uguu/public/files` and `chown -R www-data:www-data /path/to/uguu/rain/cache`

# Using the API

  * Leaving POST value 'name' empty will cause it to save using the original filename.
  * Leaving POST value 'randomname' empty will cause it to use original filename or custom name if 'name' is set to file.ext.

  * Putting anything into POST value 'randomname' will cause it to return a random filename + ext (xxxxxx.ext).
  * Putting a custom name into POST value 'name' will cause it to return a custom filename (yourpick.ext).

  E.g:
  * curl -i -F name=test.jpg -F file=@localfile.jpg http://path.to.uguu/api.php?d=upload (HTML Response)
  * curl -i -F name=test.jpg -F file=@localfile.jpg http://path.to.uguu/api.php?d=upload-tool (Plain text Response)

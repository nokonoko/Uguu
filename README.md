# About
[Uguu.se](http://uguu.se) source code, stores files and deletes after 1 hour.

# Install
Tested with Nginx+PHP5-FPM (PHP 5.4) on Debian 7 Wheezy.

Modify core.php where to save the files, add check.sh to Cron, edit everything else to your likings.

Change php.ini and nginx.conf settings to allow bigger uploads.

# Todo

Proper design, commit new design and updated code (when finished, in preview phase).


# Using the API

  Leaving POST value 'name' empty will cause it to save using the original filename.
  Leaving POST value 'randomname' empty will cause it to use original filename or custom name if 'name' is set to file.ext.
  
  Putting anything into POST value 'randomname' will cause it to return a random filename + ext (xxxxxx.ext).
  Putting a custom name into POST value 'name' will cause it to return a custom filename (yourpick.ext).


This will probably get changed later since it's messy and unpractical.

# Contact

[neku@pomf.se](mailto:neku@pomf.se) or [@Nekunekus](https://twitter.com/nekunekus).

# Uguu

uguu is a simple file uploading and sharing platform.

## Features

- One click uploading, no registration required
- A minimal, modern web interface
- Drag & drop supported
- Upload API with multiple response choices
  - JSON
  - HTML
  - Text
  - CSV
- Supports [ShareX](https://getsharex.com/) and other screenshot tools

### Demo

See the real world example at [uguu.se](https://uguu.se).

## Requirements

Original development environment is Nginx + PHP7.3 + SQLite, but is confirmed to
work with Apache 2.4 and newer PHP versions.

## Install

For the purposes of this guide, we won't cover setting up Nginx, PHP, SQLite,
Node, or NPM. So we'll just assume you already have them all running well.

### Compiling

First you must get a copy of the uguu code.  To do so, clone this git repo.
You will need to recursively clone the repo to get the required PHP submodule,
and the optional user panel submodule.
```bash
git clone --recursive https://github.com/nokonoko/uguu
```
If you don't want either of the submodules run the following command,
```bash
git clone https://github.com/nokonoko/uguu
```

Assuming you already have Node and NPM working, compilation is easy. If you would like any additional submodules, or to exclude the default PHP submodule, use the `MODULES="..."` variable.

Run the following commands to do so.
```bash
cd uguu/
make
make install
```
OR
```bash
make install DESTDIR=/desired/path/for/site
```
After this, the uguu site is now compressed and set up inside `dist/`, or, if specified, `DESTDIR`.

## Configuring

Front-end related settings, such as the name of the site, and maximum allowable
file size, are found in `dist.json`.  Changes made here will
only take effect after rebuilding the site pages.  This may be done by running
`make` from the root of the site directory.

Back-end related settings, such as database configuration, and path for uploaded files, are found in `static/php/includes/settings.inc.php`.  Changes made here take effect immediately.

If you intend to allow uploading files larger than 2 MB, you may also need to
increase POST size limits in `php.ini` and webserver configuration. For PHP,
modify `upload_max_filesize` and `post_max_size` values. The configuration
option for nginx webserver is `client_max_body_size`.

Example nginx configs can be found in confs/.

## Using SQLite as DB engine

We need to create the SQLite database before it may be used by uguu.
Fortunately, this is incredibly simple.  

First create a directory for the database, e.g. `mkdir /var/db/uguu`.  
Then, create a new SQLite database from the schema, e.g. `sqlite3 /var/db/uguu/uguu.sq3 -init /home/uguu/sqlite_schema.sql`.
Then, finally, ensure the permissions are correct, e.g.
```bash
chown nginx:nginx /var/db/uguu
chmod 0750 /var/db/uguu
chmod 0640 /var/db/uguu/uguu.sq3
```

Finally, edit `php/includes/settings.inc.php` to indicate this is the database engine you would like to use.  Make the changes outlined below
```php
define('UGUU_DB_CONN', '[stuff]'); ---> define('UGUU_DB_CONN', 'sqlite:/var/db/uguu/uguu.sq3');
define('UGUU_DB_USER', '[stuff]'); ---> define('UGUU_DB_USER', null);
define('UGUU_DB_PASS', '[stuff]'); ---> define('UGUU_DB_PASS', null);
```

*NOTE: The directory where the SQLite database is stored, must be writable by the web server user*

### Apache

If you are running Apache and want to compress your output when serving files,
add to your `.htaccess` file:

    AddOutputFilterByType DEFLATE text/html text/plain text/css application/javascript application/x-javascript application/json

Remember to enable `deflate_module` and `filter_module` modules in your Apache
configuration file.

## Getting help

Hit me up at [@nekunekus](https://twitter.com/nekunekus) or email me at neku@pomf.se

## Credits

Uguu is based off [Pomf](http://github.com/pomf/pomf).

## License

Uguu is free software, and is released under the terms of the Expat license. See
`LICENSE`.

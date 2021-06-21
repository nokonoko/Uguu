<?php

/**
 * User configurable settings for Uguu.
 */

/*
 * PDO connection socket
 *
 * Database connection to use for communication. Currently, MySQL is the only
 * DSN prefix supported.
 *
 * @see http://php.net/manual/en/ref.pdo-mysql.connection.php PHP manual for
 * PDO_MYSQL DSN.
 * @param string UGUU_DB_CONN DSN:host|unix_socket=hostname|path;dbname=database
 */
define('UGUU_DB_CONN', 'sqlite:/path/to/db/uguu.sq3');

/*
 * PDO database login credentials
 */

/* @param string UGUU_DB_NAME Database username */
define('UGUU_DB_USER', 'NULL');
/* @param string UGUU_DB_PASS Database password */
define('UGUU_DB_PASS', 'NULL');

/** Log IP of uploads */
define('LOG_IP', 'no');

/*
 * File system location where to store uploaded files
 *
 * @param string Path to directory with trailing delimiter
 */
define('UGUU_FILES_ROOT', '/path/to/file/');

/*
 * Maximum number of iterations while generating a new filename
 *
 * Uguu uses an algorithm to generate random filenames. Sometimes a file may
 * exist under a randomly generated filename, so we count tries and keep trying.
 * If this value is exceeded, we give up trying to generate a new filename.
 *
 * @param int UGUU_FILES_RETRIES Number of attempts to retry
 */
define('UGUU_FILES_RETRIES', 15);

/*
 * The length of generated filename (without file extension)
 *
 * @param int UGUU_FILES_LENGTH Number of random alphabetical ASCII characters
 * to use
 */
define('UGUU_FILES_LENGTH', 8);

/*
 * URI to prepend to links for uploaded files
 *
 * @param string UGUU_URL URI with trailing delimiter
 */
define('UGUU_URL', 'https://url.to.subdomain.where.files.will.be.served.com/');

/*
 * URI for filename generation
 *
 * @param string characters to be used in generateName()
 */
define('ID_CHARSET', 'abcdefghijklmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ');

/*
 * Filtered mime types
 * @param string[] $FILTER_MIME allowed/blocked mime types
 */
//$FILTER_MIME = array("application/octet-stream", "application/msword", "text/html", "application/x-dosexec", "application/zip", "application/java", "application/java-archive", "application/pdf", "application/x-executable");
//$FILTER_EXT = array("exe", "scr", "com", "vbs", "bat", "cmd", "htm", "html", "zip", "jar", "msi", "apk", "pdf");

define('CONFIG_BLOCKED_EXTENSIONS', serialize(['exe', 'scr', 'com', 'vbs', 'bat', 'cmd', 'htm', 'html', 'jar', 'msi', 'apk', 'phtml', 'svg']));
define('CONFIG_BLOCKED_MIME', serialize(['application/msword', 'text/html', 'application/x-dosexec', 'application/java', 'application/java-archive', 'application/x-executable', 'application/x-mach-binary', 'image/svg+xml']));

/**
 * Filter mode: whitelist (true) or blacklist (false).
 *
 * @param bool $FILTER_MODE mime type filter mode
 */
$FILTER_MODE = false;
/**
 * Double dot file extensions.
 *
 * Uguu keeps the last file extension for the uploaded file. In other words, an
 * uploaded file with `.tar.gz` extension will be given a random filename which
 * ends in `.gz` unless configured here to ignore discards for `.tar.gz`.
 *
 * @param string[] $doubledots Array of double dot file extensions strings
 *                             without the first prefixing dot
 */
$doubledots = array_map('strrev', [
    'tar.gz',
    'tar.bz',
    'tar.bz2',
    'tar.xz',
    'user.js',
]);

<?php
//This is the configuration file for Uguu Temp File Sharing system
define("CONFIG_ROOT_URL", "http://path.to.uguu");
//Enable this if you use a subdomain for serving uploaded files
define("CONFIG_SUBUPLOAD_URL_ENABLED", "false");
//Only define this if the above is set to true
define("CONFIG_SUBUPLOAD_URL", "http://a.uguu.se");
//Path to uploaded files
define("CONFIG_FILES_PATH", "/path/to/uguu/public/files/");
//Path to Uguu's files
define("CONFIG_ROOT_PATH", "/path/to/uguu/");
//Max retention time in minutes
define("CONFIG_MAX_RETENTION_TIME", "60");
//Max retention time as a text to be displayed
define("CONFIG_MAX_RETENTION_TEXT", "1 hour");
//Length of the random chain appended to the filename
define("CONFIG_RANDOM_LENGTH", "12");
//Operate on a BLACKLIST or a WHITELIST when blocking file extensions
define("CONFIG_EXTENSION_BLOCKING_MODE", "BLACKLIST");
//This is the list of blocked extensions in BLACKLIST mode (default mode), you can remove extensions or add to this list as you like
define("CONFIG_BLOCKED_EXTENSIONS", serialize(array("exe", "scr", "rar", "zip", "com", "vbs", "bat", "cmd", "html", "htm", "msi", "php", "php5")));
//This is the list of allowed extensions in WHITELIST mode, you can remove extensions or add to this list as you like
define("CONFIG_ALLOWED_EXTENSIONS", serialize(array("txt", "pdf")));
//List of blocked MIME types, for when simple ext checks doesnt cut it
define ("CONFIG_BLOCKED_MIME", serialize(array("application/octet-stream", "application/msword", "text/html", "application/x-dosexec", "application/zip", "application/java", "application/java-archive")));
//https://wiki.gentoo.org/wiki/Handbook to set this string correctly, or just ignore it
define("VERYLO_NG_STRING_THATDOESNTREALLYD_O_ANYTHING", "ok");

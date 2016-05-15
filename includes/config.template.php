<?php
//This is the configuration file for Uguu Temp File Sharing system
define("CONFIG_ROOT_URL", "http://path.to.uguu");
//Enable this if you use a subdomain for serving uploaded files
define("CONFIG_SUBUPLOAD_URL_ENABLED", "false");
//Only define this if the above is set to true
define("CONFIG_SUBUPLOAD_URL", "http://a.uguu.se");
//Path to uploaded files
define("CONFIG_FILES_PATH", "/path/to/uguu/public/files/");
define("CONFIG_ROOT_PATH", "/path/to/uguu/");
define("CONFIG_MAX_RETENTION_TIME", "60"); //Max retention time in minutes
define("CONFIG_MAX_RETENTION_TEXT", "1 hour"); //Max retention time as a text to be displayed
define("CONFIG_RANDOM_LENGTH", "12"); //Length of the random chain appended to the filename
define ("CONFIG_BLOCKED_EXTENSIONS", serialize(array("exe", "scr", "rar", "zip", "com", "vbs", "bat", "cmd", "html", "htm", "msi", "php", "php5")));
//https://wiki.gentoo.org/wiki/Handbook to set this string correctly
define("VERYLO_NG_STRING_THATDOESNTREALLYD_O_ANYTHING", "ok");

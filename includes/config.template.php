<?php
//This is the configuration file for Uguu Temp File Sharing system
define("CONFIG_ROOT_URL", "http://path.to.uguu");
define("CONFIG_FILES_PATH", "/path/to/uguu/public/files/");
define("CONFIG_ROOT_PATH", "/path/to/uguu/");
define("CONFIG_MAX_RETENTION_TIME", "60"); //Max retention time in minutes
define("CONFIG_RANDOM_LENGTH", "12");
define ("CONFIG_BLOCKED_EXTENSIONS", serialize(array("exe", "scr", "rar", "zip", "com", "vbs", "bat", "cmd", "html", "htm", "msi")));

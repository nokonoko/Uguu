<?php

require_once 'settings.inc.php';

/* NOTE: we don't have to unref the PDO because we're not long-running */
$db = new PDO(UGUU_DB_CONN, UGUU_DB_USER, UGUU_DB_PASS);

<?php
require_once "/home/neku/www/page/public/rain/rain.tpl.class.php";
raintpl::configure( 'path_replace', false);
raintpl::configure( 'tpl_dir', '/home/neku/www/page/public/rain/template/');
raintpl::configure( 'cache_dir', '/home/neku/www/page/public/rain/cache/' );
$tpl = new RainTPL;
$title = "Temp File Hosting";
$tpl->assign("title", $title);
$tpl->draw("header");
$tpl->assign("filename", $n);
$tpl->draw("upload-done");
$tpl->draw("footer");
?>

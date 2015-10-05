<?php
require_once "rain/rain.tpl.class.php";
raintpl::configure( 'path_replace', false);
raintpl::configure( 'tpl_dir', 'rain/template/');
raintpl::configure( 'cache_dir', 'rain/cache/' );
$tpl = new RainTPL;
$title = "Temp File Hosting";
$tpl->assign("title", $title);
$tpl->draw("header");
$tpl->draw("error_meow");
$tpl->draw("footer");
?>

<?php
//Loading configuration file
require_once "includes/config.php";

require_once "rain/rain.tpl.class.php";

raintpl::configure( 'path_replace', false);
raintpl::configure( 'tpl_dir', 'rain/template/');
raintpl::configure( 'cache_dir', 'rain/cache/' );

$tpl = new RainTPL;

$title = "Temp File Hosting";
if(isset($_GET['info']))
    $title = "Info";

$tpl->assign("title", $title);
$tpl->draw("header");

if(isset($_GET['info'])) {
    $tpl->assign("url_filename", CONFIG_ROOT_URL);
    $tpl->draw("info");
} else {
    $tpl->draw("upload");
}

$tpl->draw("footer");
?>

<?php
require_once "rain/rain.tpl.class.php";
raintpl::configure( 'path_replace', false);
raintpl::configure( 'tpl_dir', 'rain/template/');
raintpl::configure( 'cache_dir', 'rain/cache/' );
$tpl = new RainTPL;
$title = "Temp File Hosting";
$tpl->assign("title", $title);
$tpl->draw("header");
if(CONFIG_SUBUPLOAD_URL_ENABLED == 'true'){
  $tpl->assign("url_filename", CONFIG_SUBUPLOAD_URL.'/'.$n);
}else{
  $tpl->assign("url_filename", CONFIG_ROOT_URL.'/public/files/'.$n);
}
$tpl->assign("retention_time", CONFIG_MAX_RETENTION_TEXT);
$tpl->draw("upload-done");
$tpl->draw("footer");
?>

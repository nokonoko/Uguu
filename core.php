<?php
function save_file ($file, $name){
    $rand_string = crc32(microtime(true).mt_rand(1000, 9000));
    $path='/home/neku/www/files/';
    $file_data=strip_tags($file);
    $file_name=$rand_string.'_'.$name;
    move_uploaded_file($file_data,$path.$file_name);
    echo 'http://a.uguu.se/'.$file_name;
}
?>

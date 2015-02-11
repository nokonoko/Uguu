<?php
function save_file ($file, $name){
    //Where to save
    $path='/home/neku/www/files/';
    //Generate prefix, put together name and remove tags/whitespace
    $file_name = strip_tags(preg_replace('/\s+/', '', $name));
    $file_name = gen_name($file_name);
    while(file_exists($path.$file_name)){
        $file_name = gen_name(file_name);
    }
    //Move the file to the above location with said filename
    move_uploaded_file($file,$path.$file_name);
    //Return url+filename to the user
    echo 'http://a.uguu.se/'.$file_name;
}
Function gen_name ($in){
    //Generate random prefix
    $chars = 'abcdefghijklmnopqrstuvwxyz';
    $name = '';
    for ($i = 0; $i < 6; $i++) {
    $name .= $chars[mt_rand(0, 25)];
        }
    return $name.'_'.$in;
}
?>

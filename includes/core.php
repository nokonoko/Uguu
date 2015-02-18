<?php
function save_file ($file, $name, $arg){
    //Where to save
    $path='/home/neku/www/files/';
    //Generate name depending on arg
    switch($arg){
        case 'random':
            $ext = pathinfo($file.$name, PATHINFO_EXTENSION);
            $file_name = gen_name('random', $ext);
            while(file_exists($path.$file_name)){
                $file_name = gen_name('random', $ext);
            }
            break;
        case 'custom_original':
            $name = strip_tags(preg_replace('/\s+/', '', $name));
            $file_name = gen_name('custom_original', $name);
            while(file_exists($path.$file_name)){
                $file_name = gen_name('custom_original', $name);
            }
            break;
    }
    //Move the file to the above location with said filename
    move_uploaded_file($file,$path.$file_name);
    //Return url+filename to the user
    echo 'http://a.uguu.se/'.$file_name;
}
function gen_name($arg, $in){
    $chars = 'abcdefghijklmnopqrstuvwxyz';
    $name = '';
    for ($i = 0; $i < 6; $i++) {
    $name .= $chars[mt_rand(0, 25)];
        }
    switch($arg){
        case 'random':
            return $name.'.'.$in;
            break;
        case 'custom_original':
            return $name.'_'.$in;
            break;
    }
}
?>

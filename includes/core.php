<?php
function save_file ($file, $name, $arg, $type){
    //Where to save
    $path='/home/neku/www/files/';
    $block = array('exe', 'scr', 'rar', 'zip', 'com', 'vbs', 'bat', 'cmd', 'html', 'htm', 'msi');
    //Generate name depending on arg
    switch($arg){
        case 'random':
            $ext = pathinfo($file.$name, PATHINFO_EXTENSION);
            $ext = strtolower($ext);
            if(in_array($ext, $block)){
                if($type==='normal'){
                include_once('error_meow.php');
                exit(0);
                }else{
                    exit('File type not allowed.');
                }
                }
            $file_name = gen_name('random', $ext);
            while(file_exists($path.$file_name)){
                $file_name = gen_name('random', $ext);
            }
            break;
        case 'custom_original':
            $name = stripslashes(str_replace('/', '', $name));
            $name = strip_tags(preg_replace('/\s+/', '', $name));
                $file_name = gen_name('custom_original', $name);
                $ext = pathinfo($file_name, PATHINFO_EXTENSION);
                $ext = strtolower($ext);
                if(in_array($ext, $block)){
                if($type==='normal'){
                include_once('error_meow.php');
                exit(0);
                }else{
                    exit('File type not allowed.');
                }
                }
            while(file_exists($path.$file_name)){
                $file_name = gen_name('custom_original', $name);
            }
            break;
    }
    //Move the file to the above location with said filename
    move_uploaded_file($file,$path.$file_name);
    //Check if html or plain text should be returned
    if($type==='tool'){
    //Return url+filename to the user (plain text)
    echo 'http://a.uguu.se/'.urlencode($file_name);
    exit(0);
    }elseif($type==='normal'){
    //Return url+filename to the user (HTML)
    $n=urlencode($file_name);
    include_once('/home/neku/www/page/public/upload-done.php');
    exit(0);
    }
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

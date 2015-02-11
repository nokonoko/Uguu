<?php
function save_file ($file, $name){
    //Generate a random prefix, strip tags and remove any whitespace
    $file_name = gen_name(strip_tags(preg_replace('/\s+/', '', $name)));
    //Where to save
    $path='/home/neku/www/files/';
    //Move the file to the above location with said filename
    move_uploaded_file($file,$path.$file_name);
    //Return url+filename to the user
    echo 'http://a.uguu.se/'.$file_name;
}
Function gen_name ($in){
    //Check so the file doesn't exist, and generate random prefix
    while(file_exists('/home/neku/www/files'.$name.'_'.$in)){
    $chars = 'abcdefghijklmnopqrstuvwxyz';
    $name = '';
    for ($i = 0; $i < 6; $i++) {
    $name .= $chars[mt_rand(0, 25)];
        }
    }
    return $name.'_'.$in;
}
?>

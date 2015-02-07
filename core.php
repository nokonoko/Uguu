<?php
function save_file ($file, $name){
    //Generate a random set of numbers to set in front of the filename
    $rand_string = crc32(microtime(true).mt_rand(1000, 9000));
    //Where to save
    $path='/home/neku/www/files/';
    //Remove any tags
    $file_data=strip_tags($file);
    //Put together the random string+filename
    $file_name=$rand_string.'_'.$name;
    //Move the file to the above location with said filename
    move_uploaded_file($file_data,$path.$file_name);
    //Return url+filename to the user
    echo 'http://a.uguu.se/'.$file_name;
}
?>

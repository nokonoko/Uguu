<?php
//Loading configuration file
require_once "config.php";

//Saving the file on the server
function save_file ($file, $name, $arg, $type,$withNewLine=false){
    //Generate name depending on arg
    switch($arg){
        case 'random':
            $ext = pathinfo($file.$name, PATHINFO_EXTENSION);
            $ext = strtolower($ext);
            if(in_array($ext, unserialize(CONFIG_BLOCKED_EXTENSIONS))){
              if($type==='normal'){
                include_once(CONFIG_ROOT_PATH.'error_meow.php');
                exit(0);
              }else{
                exit('File type not allowed.');
              }
            }
            $file_name = gen_name('random', $ext);
            while(file_exists(CONFIG_FILES_PATH.$file_name)){
              $file_name = gen_name('random', $ext);
            }
            break;
        case 'custom_original':
            $name = stripslashes(str_replace('/', '', $name));
            $name = strip_tags(preg_replace('/\s+/', '', $name));
                $file_name = gen_name('custom_original', $name);
                $ext = pathinfo($file_name, PATHINFO_EXTENSION);
                $ext = strtolower($ext);
                if(in_array($ext, unserialize(CONFIG_BLOCKED_EXTENSIONS))){
                 if($type==='normal'){
                   include_once(CONFIG_ROOT_PATH.'error_meow.php');
                   exit(0);
                 }else{
                   exit('File type not allowed.');
                 }
                }
            while(file_exists(CONFIG_FILES_PATH.$file_name)){
                $file_name = gen_name('custom_original', $name);
            }
            break;
    }
    //Move the file to the above location with said filename
    move_uploaded_file($file,CONFIG_FILES_PATH.$file_name);
    //Check if html or plain text should be returned
    if($type==='tool'){
    //Return url+filename to the user (plain text)
    if(CONFIG_SUBUPLOAD_URL_ENABLED == "true"){
    echo CONFIG_SUBUPLOAD_URL.'/'.urlencode($file_name);
    if ($withNewLine) echo "\n";
    }else{
    echo CONFIG_ROOT_URL.'/files/'.urlencode($file_name);
    if ($withNewLine) echo "\n";
    }
    exit(0);
    }elseif($type==='normal'){
    //Return url+filename to the user (HTML)
    $n=urlencode($file_name);
    include_once(CONFIG_ROOT_PATH.'upload-done.php');
    exit(0);
    }
}

#Generate a random name for the uploaded file
function gen_name($arg, $in){
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $name = '';
    for ($i = 0; $i < CONFIG_RANDOM_LENGTH; $i++) {
    $name .= $chars[mt_rand(0, 60)];
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

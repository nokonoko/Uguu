<?php
//Loading configuration file
require_once "includes/config.php";

//If the value d doesn't exist, redirect back to front page *1
if(isset($_GET['d'])) {
    //Include the core file with the functions
    include_once(CONFIG_ROOT_PATH.'includes/core.php');
    $withNewLine=false;
    switch ($_GET['d']) {
    	//Uploading with HTML response and errors
        case 'upload':
        //If no file is being posted, show the error page and exit.
        if(empty($_FILES['file']['name'])){
        	include_once(CONFIG_ROOT_PATH.'error.php');
        	exit(0);
        }
        //Set the name value to the original filename
	$name = $_FILES['file']['name'];
	$arg = 'custom_original';
	//If the value name contains a custom name, set the name value
	if(!empty($_POST['name'])){
	$name = $_POST['name'];}
	//If value contains anything, keep original filename
	if(!empty($_POST['randomname'])){
        $name = $_FILES['file']['name'];
	$arg = 'random';}
	//Call the save function which sends the file+name
	save_file($_FILES['file']['tmp_name'], $name, $arg, 'normal');
	break;
	//Uploading without HTML response or errors
	case 'upload-tool-with-new-line':
	$withNewLine=true;
	case 'upload-tool':
	        //If no file is being posted, show the error page and exit.
        if(empty($_FILES['file']['name'])){
		exit('You did not send a file, try again.');
        }
        //Set the name value to the original filename
	$name = $_FILES['file']['name'];
	$arg = 'custom_original';
	//If the value name contains a custom name, set the name value
	if(!empty($_POST['name'])){
	$name = $_POST['name'];}
	//If value contains anything, keep original filename
	if(!empty($_POST['randomname'])){
        $name = $_FILES['file']['name'];
	$arg = 'random';}
	//Call the save function which sends the file+name
	save_file($_FILES['file']['tmp_name'], $name, $arg, 'tool', $withNewLine);
	break;
        case 'extend-time':
            break;
	default:
	//If no correct valid argument for the api to perform on, tell them to enter a valid one
	exit('Please provide a valid argument. Example: curl -i -F name=test.jpg -F file=@localfile.jpg '.CONFIG_ROOT_URL.'/api.php?d=upload-tool');
	break;
    }
}else{
    //*1
    header('Location: index.html');
}

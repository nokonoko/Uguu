<?php
//If the value d doesn't exist, redirect back to front page *1
if(isset($_GET['d'])) {
    //Include the core file with the functions
    include_once('includes/core.php');
    switch ($_GET['d']) {
        case 'upload':
        //If no file is being posted, exit
        if(empty($_FILES['file']['name'])){
        exit('You fucked up, nothing to do.');}
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
	save_file($_FILES['file']['tmp_name'], $name, $arg);
	break;
        case 'extend-time':
            break;
	default:
	//If no correct valid argument for the api to perform on, tell them to enter a valid one
	exit('Please provide a valid argument. Example: curl -i -F name=test.jpg -F file=@localfile.jpg http://uguu.se/api.php?d=upload');
	break;
    }
}else{
    //*1
    header('Location: index.html');
}

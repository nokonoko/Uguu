<?php
if(isset($_GET['d'])) {
    include_once('core.php');
    switch ($_GET['d']) {

        case 'upload':

            if(!empty($_POST['name'])){
            	if(isset($_POST['autoext'])){
            		$oftheworld = explode(".", $_FILES["file"]["name"]);
            		$ext = end($oftheworld);
            		save_file($_FILES["file"]["tmp_name"], $_POST['name']. '.' .$ext);
            	}else{
                	save_file($_FILES["file"]["tmp_name"], $_POST['name']);
            	}
            }else{
                save_file($_FILES["file"]["tmp_name"], $_FILES["file"]["name"]);
            }
            break;

        case 'extend-time':
            break;
	default:
	exit('Please provide a valid argument. Example: curl -i -F name=test.jpg -F file=@localfile.jpg http://uguu.se/api.php?d=upload');
	break;
    }
}else{
    exit('Please provide a valid argument. Example: curl -i -F name=test.jpg -F file=@localfile.jpg http://uguu.se/api.php?d=upload');
}

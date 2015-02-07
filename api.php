<?php
if(isset($_GET['d'])) {
    include_once('core.php');
    switch ($_GET['d']) {

        case 'upload':
            $name = "";
            
            if(!empty($_POST['name'])){
                $name = $_POST['name'];
                
                if(isset($_POST['autoext'])){
                    $split = explode(".", $_FILES["file"]["name"]);
                    $name .= "." . end($split);
            	}
            }else{
                $name = $_FILES["file"]["name"];
            }
            
            save_file($_FILES["file"]["tmp_name"], $name);
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

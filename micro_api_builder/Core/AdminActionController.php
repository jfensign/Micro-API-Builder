<?php

require_once("AbstractController.php");

class AdminActionController extends AbstractController {

private static $file_path;
private static $instance, $crud;
private $user_model;
protected $user;

protected function __construct() {
  if(!array_key_exists('code', $_GET)) {
    exit('No Code');
  }
}

  protected function handleImageUpload($path, $postVal = "", $image=null) {

	if(!empty($_FILES) ) {
	  if($_FILES[!$image ? 'image' : $image]['name'] != "") {
	    $target_path =  $path . $_FILES[!$image ? 'image' : $image]['name']; 
	    $_POST[$postVal] = $target_path;
		
		if(!file_exists($target_path)) {
			if(move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
				header('Location: ' . $_SERVER['PHP_SELF']);
			} 
			else{
				echo "There was an error uploading the file, please try again!";
			}
		}
	  }
	}
  }

}

?>

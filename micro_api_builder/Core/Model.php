<?php

abstract class Model {

private static $instance;
private static $file_path;
protected $db;

//$encryptedmessage = encrypt("your message"); 
//echo decrypt($encryptedmessage); 

private function __construct() {}

//Singleton to ensure that class is instantiated once
public static function get_instance() {
	if(empty(self::$instance)) {
		self::$instance = new Model();
	}
	return self::$instance;
}

//Check if model exists in Module 
public static function load_model($model_name) {

	self::$file_path = App::MODEL_PATH . $model_name . App::PHP_EXT;

	if(!file_exists(self::$file_path)) {
		throw new Exception("Model $model_name.php could not be located");
	} 
	else {
		try {
			require_once(self::$file_path);
			$model_name = $model_name . "Model";
			$model = new $model_name();
	
			return $model;	
		} 
		catch(Exception $e) {
			echo $e->getMessage();	
		}
	}
} 

	public function show($scope) {
		return $this->db->show($scope);
	}

	public function getMeta($table, $args) { 
		return $this->db->findOne($table, $args);
	}

	public function findAll($table) {
		return $this->db->findAll($table);
	}
	
	public function findOne($table, $args) {
		return $this->db->findOne($table, $args);
	}
	
	public function findWhere($table, $args) {
	  return $this->db->findWhere($table, $args);
	}
	
	public function Create($table, $vals) {
		return $this->db->insert($table, $vals) ? TRUE : FALSE;
	}
	
	public function delete($table, $where) {
		return $this->db->delete($table, $where);
	}
	
	public function Update($table, $where, $vals) {
		return $this->db->update($table, $where, $vals);
	}
	
	public function Query($sql) {
		return $this->db->query($sql);
	}
	
  protected function handleImageUpload($image) {
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

?>
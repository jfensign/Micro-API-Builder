<?php
//Abstract controller
abstract class AbstractController {

//child controller's model
protected $model;
public static $_Instance;
public static $resourceObj;

protected function __construct() {}

public static function load_controller($file_path) {
	$tmp = explode("/", $file_path);
	$class = str_replace(".php", "", end($tmp)) . "Controller";
	require_once($file_path);
	unset($tmp);
	return $class::ResourceInstance();
}

protected function doUpdate($scope, array $where) {
	parse_str(file_get_contents("php://input"),$post_vars);
	return $this->model->update($scope, $where, $post_vars, FALSE);
}

protected function doCreate($scope) {
  parse_str(file_get_contents("php://input"),$post_vars);
	return $this->model->create($scope, $post_vars);
}

protected function doDelete($scope, array $where) {
	if($_SERVER['REQUEST_METHOD'] != "DELETE") App::Response(400);
	return $this->model->delete($scope, $where);
}

protected function sdfsdfsd() {
	$c = self::encrypt(md5(time() * rand(5, 10)));
	$d = self::decrypt($c);
}

protected function encrypt($str, $isURL=false) { 
  $encStr = trim(
    base64_encode(
      mcrypt_encrypt(
        MCRYPT_RIJNDAEL_256,
        App::SECURITY_SALT, 
        $str, 
        MCRYPT_MODE_ECB, 
        mcrypt_create_iv(
          mcrypt_get_iv_size(
            MCRYPT_RIJNDAEL_256, 
            MCRYPT_MODE_ECB
          ), 
          MCRYPT_RAND
       )
     )
   )
 ); 
 return $encStr;
} 
	
protected function decrypt($str, $isURL=false) { 
  $decStr = trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, "BRAZZLEBOX", base64_decode($str), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND))); 
  return $decStr;
}

}
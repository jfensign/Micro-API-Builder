<?php

class Authentication {

  public static $_Instance;
  protected $model;
  public $token;

  protected function __construct() {
  
	$this->user_model = App::load()->model('User');
	if(array_key_exists('code', $_GET)) {
		$this->user = (object)$this->user_model->findWhere('users', array('unique_secret'=>$this->Decrypt($_REQUEST['code'])));
	}
	else {
		$this->user = "guest";
	}

	apc_store("Client", $this->user);
  }
	
  public static function load_auth() {
	if(empty(self::$_Instance)) {
	  self::$_Instance = new Authentication();
	}
	return self::$_Instance;
  }
	
  public function keyCheck($APIkey, $isGET=TRUE) {
  
	if(!array_key_exists('key', $_GET) && $isGET==TRUE) App::Response(400, array("error" => "API was not provided"));
	$key = self::decrypt($APIkey);
	$user = $this->model->findWhere("users", array("apikey" => $key));
	if(!$user) App::Response(401, array("error" => "Invalid API key"));
	return $user;
  }
  
  public function tokenCheck($tokenID) {
	if(!array_key_exists('token', $_GET)) App::Response(400, array("error" => "Token ID is required"));
	$decryptedToken = $tokenID;
	$user = $this->model->findWhere("users", array("token"=>$tokenID));
	if(!$user) App::Response(401, array("error" => "Invalid Token"));
	return $user;
  }
	
  protected function Encrypt($str, $isURL=false) { 
    $encStr = trim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, App::SECURITY_SALT, $str, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)))); 
    return $encStr;
  } 
	
  protected function Decrypt($str, $isURL=false) { 
    $decStr = trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, App::SECURITY_SALT, base64_decode($str), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND))); 
    return $decStr;
  }

}
?>
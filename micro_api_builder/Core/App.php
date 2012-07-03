<?php

class App {

  const APP_NAME = "media.brazzlebox/";
  const APP_PATH = "http://localhost/{self::APP_NAME}/";
  const CONTROLLER_PATH = "Application/Controller";
  const MODEL_PATH = "Application/Model/";
  const ADMIN_CONTROLLER = "";
  const LOGS_PATH = "";
  const PHP_EXT = ".php";
  const SECURITY_SALT = "";
  const DB_HOST = "";
  const DB_USER = "";
  const DB_PASS = "";
  const MAIN_DB = "";
  const DB_DRIVER = "mysql";
  const PHOTOS_DIR = 'Media/Photos/';
  const VIDEOS_DIR = 'Media/Videos/';
  const DOCUMENTS_DIR = 'Media/Documents/';
  const AUDIO_DIR = 'Media/Audio/';
  
  private static $_Instance,
				 $_Method_Array = array(),
				 $Plural = TRUE,
				 $_Routes,
				 $_Real_Request_Controller,
				 $routeArray=array(),
				 $responseHandler;
  
  public static $HTTP_Method = NULL,
				$Path,
				$Request_Action,
				$Request_Timestamp,
				$Request_Data,
				$ControllerObject,
				$Controller,
				$Routes = array(),
				$Request_Args,
				$Response_Data = array(),
				$URI,
				$adminFlag = FALSE,
				$original_ResourceRequest,
				$user,
				$user_model;
				
				
  public static $httpStatusCodes = Array(
		    100 => 'Continue',
		    101 => 'Switching Protocols',
		    200 => 'OK',
		    201 => 'Created',
		    202 => 'Accepted',
		    203 => 'Non-Authoritative Information',
		    204 => 'No Content',
		    205 => 'Reset Content',
		    206 => 'Partial Content',
		    300 => 'Multiple Choices',
		    301 => 'Moved Permanently',
		    302 => 'Found',
		    303 => 'See Other',
		    304 => 'Not Modified',
		    305 => 'Use Proxy',
		    306 => '(Unused)',
		    307 => 'Temporary Redirect',
		    400 => 'Bad Request',
		    401 => 'Unauthorized',
		    402 => 'Payment Required',
		    403 => 'Forbidden',
		    404 => 'Not Found',
		    405 => 'Method Not Allowed',
		    406 => 'Not Acceptable',
		    407 => 'Proxy Authentication Required',
		    408 => 'Request Timeout',
		    409 => 'Conflict',
		    410 => 'Gone',
		    411 => 'Length Required',
		    412 => 'Precondition Failed',
		    413 => 'Request Entity Too Large',
		    414 => 'Request-URI Too Long',
		    415 => 'Unsupported Media Type',
		    416 => 'Requested Range Not Satisfiable',
		    417 => 'Expectation Failed',
		    500 => 'Internal Server Error',
		    501 => 'Not Implemented',
		    502 => 'Bad Gateway',
		    503 => 'Service Unavailable',
		    504 => 'Gateway Timeout',
		    505 => 'HTTP Version Not Supported'
		);

  private function __construct() {}
  
  //loads response dependencies
  public static function load() {
	require_once('Load.php');
	return new Load();
    }
  
  public static function Init(array $routes = NULL) {
	//create singleton instance
	if(!empty(self::$_Instance)) {
	  self::$_Instance = new App();
	}
	
	//set request path array
	self::$Path = str_replace(self::APP_PATH, "", $_SERVER['REQUEST_URI']);
	self::$Path = str_replace(self::APP_NAME, "", $_SERVER['REQUEST_URI']);
	self::$Path = str_replace("?" . $_SERVER["QUERY_STRING"], "", self::$Path);
	self::$Path = preg_split('[\\/]', self::$Path, -1, PREG_SPLIT_NO_EMPTY);
	self::$_Routes = $routes;
	
	$pathString = implode('/', self::$Path);
	$vals=array_values(self::$routeArray);
	foreach($vals as $key=>$val) {
		if(preg_match($val[0], $pathString, $matches)) {
			$s = str_replace($matches[0], '', $pathString);
			self::$Request_Args = explode('/', substr($s,1));
			self::$responseHandler = $val[1];
		}
	}
	//return application instance
	return self::$_Instance;
    }
	
  public static function setRoute($uri_pattern, $action, $httpMethod) {
	if($_SERVER['REQUEST_METHOD'] == $httpMethod) {
	  array_push(self::$routeArray, array($uri_pattern, $action, $httpMethod));
	}
  }
  
	public static function Response($status = 200, $body = '', $content_type = 'application/json')
	{
		$status_header = 'HTTP/1.1 ' . $status . ' ' . self::$httpStatusCodes[$status];
		// set the status
		header($status_header);
		// set the content type
		header('Content-type: ' . $content_type);

		// pages with body are easy
		if($body != '') {
			// send the body
			echo json_encode($body);
		}
		exit();
	}

  
  public static function Run() {
	//return data
	if(function_exists(self::$responseHandler)) {
	  call_user_func_array(self::$responseHandler, self::$Request_Args);
	}
	else {
	  self::Response(404, array('Status'=>'Not Found'));
	}
	exit();
    }
	
 protected function encrypt($str, $isURL=false) { 
  $encStr = trim(
    base64_encode(
      mcrypt_encrypt(
        MCRYPT_RIJNDAEL_256,
        self::SECURITY_SALT, 
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
	
 protected static function Decrypt($str, $isURL=false) { 
  $decStr = trim(
    mcrypt_decrypt(
      MCRYPT_RIJNDAEL_256, 
      self::SECURITY_SALT, 
      base64_decode($str),
      MCRYPT_MODE_ECB, 
      mcrypt_create_iv(
        mcrypt_get_iv_size(
          MCRYPT_RIJNDAEL_256, 
          MCRYPT_MODE_ECB
        ), 
        MCRYPT_RAND
      )
    )
  ); 
  return $decStr;
 }
}
?>
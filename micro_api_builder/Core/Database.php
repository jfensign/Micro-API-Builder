<?php

//require configuration to get db setting/constructor arguments


class TacoDB {

  private static $_Instance,
				 $_dbDriver,
				 $_Conn;
							  
  private function __construct() {
	self::$_dbDriver = $this->{App::DB_DRIVER}(); 
  }

  //Makes this a singleton.
  public static function OpenConn() {
	if(empty(self::$_instance)) {
		self::$_Instance = new self();
	}
	
	return self::$_Instance;
  }
  
  private function mongo() {
	return new TacoDB_Mongo();
  }
  
  private function mysql() {
	return new TacoDB_MySQL();
  }
  
  private function apc() {
	return new TacoDB_APC();
  }
  
  public function selectDB($dbName) {
	self::$_Conn = self::$_dbDriver->setDB($dbName);
	return self::$_Conn;
  }
  
  public function selectCollection($tableName) {
	self::$_Conn = self::$_dbDriver->setTable($tableName);
	return self::$_Conn;
  }
  
  public function findAll($collection, $format = "") {
	self::$_Conn = self::$_dbDriver->find_all($collection, $format);
	return self::$_Conn;
  }
  
  public function findOne($collection, $args, $format = "") {
	self::$_Conn = self::$_dbDriver->find_one($collection, $args, $format);
	return self::$_Conn;
  }
  
  public function findWhere($collection, $args, $format = "") {
	self::$_Conn = self::$_dbDriver->find_where($collection, $args, $format);
	return self::$_Conn;
  }
  
  public function insert($collection, $args, $isHTML=FALSE) {

	self::$_Conn = self::$_dbDriver->insert($collection, $args, $isHTML);
	return self::$_Conn;
  }
  
  public function insertMarkup() {
    self::$_Conn = self::$_dbDriver->insertMarkup($collection, $args);
	return self::$_Conn;
  }
  
  public function update($collection, $where, $args) {
	self::$_Conn = self::$_dbDriver->update($collection, $where, $args);
	return self::$_Conn;
  }
  
  public function delete($collection, $where) {
	self::$_Conn = self::$_dbDriver->delete($collection, $where);
	return self::$_Conn;
  }
  
  public function dump() {
	self::$_Conn = self::$_dbDriver->dump();
	return self::$_Conn;
  }
  
  public function show($collection, $format="") {
	self::$_Conn = self::$_dbDriver->show($collection, $format);
	return self::$_Conn;
  }
  
  public function query($sql) {
    self::$_Conn = self::$_dbDriver->query($sql);
	return self::$_Conn;
  }
  
}

class TacoDB_Mongo {

	private $_Conn,
			$_DB,
			$_Coll,
			$_Mongo,
			$_result;
	
	public function __construct() {
	  if(!$this->connected) {
		try {
		  $this->_Conn = new Mongo(App::DB_HOST,
			                array( 
				            "username" => App::DB_USER,
				            "password" => App::DB_PASS));
				            
		   return $this->_Conn->connect();
		}
		catch(MongoException $e) {
			echo $e->getMessage();
		}
	  }
	}
	
	public function setDB($db_name) {
	  try {
		$this->_DB = $this->_Conn->selectDB($db_name);
		return $this->_DB;
	  }
	  catch(MongoException $e) {
		echo $e->getMessage();
	  }	
	}
	
	public function setTable($coll_name) {
	  try {
		$this->_Coll = $this->_DB->selectCollection($coll_name);
	  }
	  catch(MongoException $e) {
		echo $this->getMessage();
	  }
	}
	
	public function find_one($collection, $args, $format = "") {
		try {
			$this->_result = $this->_DB->$collection->findOne($args);
			return $this->formatReturnResult($format);
		}
		catch(MongoException $e) {
			echo $e->getMessage();
		}
	}
	
	public function insert($collection, $args) {
		$this->_DB->$collection->insert($args, true);
		$this->_DB->$collection->ensureIndex(array("id" => 1), array("unique" => 1, "dropDups" => 1));
	}
	
	private function formatReturnResult($format = "") {
		
		$returnResult = NULL;
		
		switch($format) {
			case "object":
				$returnResult = (object) $this->_result; //converts array to stdType Object
			break;
			case "json":
				$returnResult = json_encode($this->_result); //returns json object
			break;
			default:
				$returnResult = $this->_result; //returns plain array w/ _id
			break;
		}	
		
		return $returnResult;
		
	}
}

class TacoDB_MySQL {
	
	private $_Conn,
			$_DB,
			$_Table,
			$_resultResource;
			
	public function __construct() {
	  $this->_Conn = mysql_connect(
		 App::DB_HOST,
		 App::DB_USER,
		 App::DB_PASS
	  ) or die(mysql_error());	//connect to db			
	  
	  mysql_select_db(App::MAIN_DB);
	}
	
	public function setDB($db_name) {
		try { //select db
		  mysql_select_db(
			$db_name, $this->_Conn
		  );
		}
		catch(Excpetion $e) {
		  echo mysql_error();
		}
	}
	
	public function setTable($table_name) {
		$this->_Table = $table_name;
	}
	
	public function show($table, $returnType) {

		$sql = "SHOW COLUMNS FROM $table";
		
		$this->resultResource = mysql_query($sql)
								or die(mysql_error());
								
		while($row = $this->formatReturnResult($returnType)) {
			$data[] = $row;
		}
		
		return !empty($data) ? $data : FALSE;
		
	}
	
	public function insert($table, $args, $isMarkup=false) {
		if(!$isMarkup):
		  $args = $this->sanitizeQueryParams($args);
		endif;
		
		$sql = "INSERT INTO $table
				(" .
				implode(", ", array_keys($args))
				. ") VALUES (" .
				implode(", ", array_values($args))
				. ")";
				
		$this->resultResource = mysql_query($sql)
								or die(mysql_error());
								
		return mysql_insert_id();
	}
	
	public function update($table_name, $where, $args, $isMarkup=false) {
	   if(!$isMarkup) {
		 $args = $this->sanitizeQueryParams($args);
	   }
		
		$sql = "UPDATE $table_name
				SET ". 
				str_replace("&", ", ", urldecode(http_build_query($args))) . 
				" WHERE " .
				str_replace("&", " AND ", urldecode(http_build_query($where)));

		$this->resultResource = mysql_query($sql)
								or die(mysql_error());
								
		return $this->resultResource;
	}
	
	public function delete($table, $args) {
		$args = $this->sanitizeQueryParams($args);
		
		$sql = "DELETE FROM $table 
		 WHERE " . str_replace("&", " AND ", urldecode(http_build_query($args)));
		 
		 $this->resultResource = mysql_query($sql)
								 or die(mysql_error());
								 
		return $this->resultResource;
	}
	
	public function find_where($table, $args, $returnType = "") {

		$args = $this->sanitizeQueryParams($args);
		$sql = "SELECT * 
		        FROM $table
				WHERE " .
				str_replace("&", " AND ", urldecode(http_build_query($args)));

		$this->resultResource = mysql_query($sql)
								or die(mysql_error());
		while($row = $this->formatReturnResult($returnType)) {
			$data[] = $row;
		}
		
		return @$data != NULL && @!empty($data) ? $data : FALSE;
	}
	
	public function find_one($table, $args, $returnType = "") {
		$args = $this->sanitizeQueryParams($args);
		$sql = "SELECT * 
					  FROM $table
				      WHERE " .
					  str_replace("&", " AND ", urldecode(http_build_query($args)));
		
		$this->resultResource = mysql_query($sql)
								or die(mysql_error());
		
		return $this->formatReturnResult($returnType);
	}
	
	public function find_all($table, $format = "") {
		$data = array();
		$sql = "SELECT * FROM $table";
		$this->resultResource = mysql_query($sql)
								or die(mysql_error());
		
		while($row = $this->formatReturnResult($format)) {
			$data[] = $row;
		}
		
		return $data != NULL && !empty($data) ? $data : FALSE;
	}
	
	private function sanitizeQueryParams(array $queryParts = NULL, $isMarkup=false) {

	    if($isMarkup) {
		  foreach($queryParts as $key=>$val) {
		    $queryParts[$key] = htmlspecialchars($val);
		  }
		}
	
		//array_map("mysql_real_escape_string", $queryParts);
		
		foreach($queryParts as $key=>$val) {
			
			if(gettype($val) === "string") {
				$queryParts[$key] = "'"  . filter_var($val) . "'";
			}
			elseif(gettype($val) === "integer") {
				$queryParts[$key] = intval($val);
			}
		}
		
		return $queryParts;
	}
	
	public function query($sql) {

		$data = false;
		$this->resultResource = mysql_query($sql)
								or die(mysql_error());
								
		while($row = $this->formatReturnResult()) {
		  $data[] = $row;
		}
		
		return $data;
	}
	
	private function formatReturnResult($type = "") {
		$returnVals;
		switch($type) {
			case "object":
				$returnVals = mysql_fetch_object($this->resultResource);
			break;
			case "count":
				$returnVals = mysql_num_rows($this->resultResource);
			break;
			case "json":
				return json_encode($this->resultResource);
			default:
				$returnVals = mysql_fetch_assoc($this->resultResource);
			break;
		}
		 
		return $returnVals;
	}
}
?>
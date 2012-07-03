<?php

abstract class App {
  
  public static $Instance = NULL;
  
  private function __construct() {}
  
  
  public function getInstance() {
    if(empty(self::$Instance)) {
	  self::$Instance = new App();
	}
	
	return self::$Instance;
  }
  
  public function GET() {
    return new Rest
  }
  
  public function POST() {
  
  }
  
  public function PUTS() {
  
  }
  
  public function DELETE() {
    
  }
  
}

?>
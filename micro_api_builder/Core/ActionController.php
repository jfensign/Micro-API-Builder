<?php

require_once("AbstractController.php");

class ActionController extends AbstractController{

private static $file_path;
private static $instance, $crud;
public static $tokenID;

protected function __construct() {
	self::$tokenID = parent::__construct();
	return self::$tokenID;
}

}

?>
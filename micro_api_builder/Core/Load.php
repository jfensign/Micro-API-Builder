<?php
//bootstrap file
class Load {

public function __construct() {}

public static function module($module_name, $dm_admin = FALSE) {
	require_once('Module.php');
	return Module::load_module($module_name, $dm_admin);
}

public static function controller($controller_name, $method, $is_admin) {
	$controller_scope = ($is_admin == FALSE ? 'ActionController' : 'AdminActionController' );
	require_once('ActionController.php');
	require_once('AdminActionController.php');
	require_once( $controller_scope . App::PHP_EXT);
	return $controller_scope::load_controller($controller_name, $method);
}

public static function view($view_name, $vars = NULL, $isTMP = FALSE) {
	require_once('Template.php');
	return new Template($view_name, $vars, $isTMP);
}

public static function authenticator() {
	require_once('Authentication.php');
	return Authentication::load_auth();
}

public static function model($model_name) {
	require_once('Model.php');
	return Model::load_model($model_name);
}

public static function database() {
	require_once('Database.php');
	return TacoDB::OpenConn();
}

public static function library($library) {
	require_once("Library.php");
	return new Library($library);
}

public static function xml($doc) {
	require_once("XML.php");
	return XML::load_xml($doc);
}

public function navigation() {
	require_once("Navigation.php");
	return Navigation::get_instance();
}

public static function scripts($script) {
	require_once("Scripts.php");
	return Scripts::get_instance();
}

public static function styles($style) {
	require_once("Styles.php");
	return Styles::get_instance();
}
}
?>

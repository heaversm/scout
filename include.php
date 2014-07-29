<?php
ini_set('mongo.native_long', 1);
error_reporting(E_ALL);
header( 'Content-Type: text/html; charset=UTF-8' );
mb_internal_encoding('UTF-8');
date_default_timezone_set('America/New_York');

require_once('syndrome/config/SyndromeConfig.php');
require_once('app/config/Config.php');
$app_config = new Config();

function __autoload($class_name) {
	$class_name = str_replace('_', '/', $class_name);
	$file_name = $class_name.'.php';
	
	if(file_exists(Config::$vendors.$file_name)) {
		require_once(Config::$vendors.$file_name);
	} else if(file_exists(Config::$syndrome_vendors.$file_name)) {
		require_once(Config::$syndrome_vendors.$file_name);
	}
	
	if(file_exists(Config::$library.'/'.$file_name)) {
		require_once(Config::$library.'/'.$file_name);
	} else if(file_exists(Config::$syndrome_library.'/'.$file_name)) {
		require_once(Config::$syndrome_library.'/'.$file_name);
	}
	
	if(file_exists(Config::$controller.'/'.$file_name)) {
		require_once(Config::$controller.'/'.$file_name);
	} else if(file_exists(Config::$syndrome_controller.'/'.$file_name)) {
		require_once(Config::$syndrome_controller.'/'.$file_name);
	}

	if(file_exists(Config::$base_controller.'/'.$file_name)) {
		require_once(Config::$base_controller.'/'.$file_name);
	} else if(file_exists(Config::$syndrome_base_controller.'/'.$file_name)) {
		require_once(Config::$syndrome_base_controller.'/'.$file_name);
	}
}

$browser = Helper_Browser::getBrowser();
if($browser['browser'] == 'msie') {
	header('X-UA-Compatible: IE=Edge,chrome=1');
}

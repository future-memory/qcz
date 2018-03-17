<?php

//
//error_reporting(E_ALL|E_STRICT);
ini_set("display_errors", "Off");


date_default_timezone_set('Asia/Shanghai');

include_once dirname(__FILE__) . '/database.php';
include_once dirname(__FILE__) . '/constants.php';

if(defined('ENV') && ENV=='test'){
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);

	define('ES_HOST', '120.77.2.178');
	define('ES_PORT', '9200');
}else{
	ini_set("display_errors", "Off");
	
	define('ES_HOST', '10.26.215.116');
	define('ES_PORT', '9200');
}

$main_config = array(
	'timezone'        => 'Asia/Chongqing',
	'base_path'       => BASE_ROOT,
	'view_path'       => APP_ROOT.'/template',
	'controller_path' => APP_ROOT.'/controllers',
	'cookie'          => array(
		'pre'    => 'QCZ_',
		'domain' => '',
		'path'   => '/',
	),
	'uc_cookie'=>array(
			'pre'    => 'QCZ_',
			'domain' => 'shop.com',
			'path'   => '/',
	),
	'components'      => array(
		'database' => array(
			'class'  => 'DataBase',
			'config' => get_db_config(),
			'driver' => 'db_driver_mysqli_slave',
		),
		'memory' => array(
			'class'  => 'Memory',
			'config' => get_redis_config(),
			'driver' => 'memory_driver_redis'
		),
		'request' => array(
			'class'                => 'Request',
			'enableCsrfValidation' => true,
		),
		'log' => array(
			'class'   => 'Log',
			'logPath' => BASE_ROOT . '/data/log',
		),
	),
);

return $main_config;
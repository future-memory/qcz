<?php

$config = array();

if(defined('ENV') && ENV=='test'){
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
}

$config['founders'] = 'johz';

$config['skip_actions'] = array('login', 'web_login', 'dologin', 'callback');

return $config;

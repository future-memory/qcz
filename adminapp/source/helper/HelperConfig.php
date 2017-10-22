<?php

class HelperConfig
{
	public static $config = array();
	public static $loaded_config = array();

	public static function load_config($key)
	{
		if(isset(self::$loaded_config[$key])) {
			return self::$loaded_config[$key];
		}

		self::$loaded_config[$key] = array();

		//私有config
		$config_path = APP_ROOT.'/config';;
		$config_file = $config_path.DIRECTORY_SEPARATOR.$key.'.php';
		if(is_file($config_file)){
			$tmp = include($config_file);
			self::$loaded_config[$key] = array_merge((array)$tmp, self::$loaded_config[$key]);
		}

		//共用config  存在相同key时 不覆盖私有
		$config_path = BASE_ROOT.'/config';
		$config_file = $config_path.DIRECTORY_SEPARATOR.$key.'.php';
		if(is_file($config_file)){
			$tmp = include($config_file);
			self::$loaded_config[$key] = array_merge((array)$tmp, self::$loaded_config[$key]);
		}


		return self::$loaded_config[$key];
	}

	//HelperConfig::get_config('global::founders') or HelperConfig::get_config('global::a/b/c')
	public static function get_config($key)
	{
		if(isset(self::$config[$key])) {
			return self::$config[$key];
		}

		$arr = explode('::', $key);
		if(empty($arr) || !isset($arr[0]) || empty($arr[0])){
			return null;
		}

		$fkey   = $arr[0];
		$config = self::load_config($fkey);

		//返回整个config
		if(!isset($arr[1]) || empty($arr[1])){
			return $config;
		}

		$key = explode('/', $arr[1]);
		foreach ($key as $k) {
			if (!isset($config[$k])) {
				return null;
			}
			$config = $config[$k];
		}

		return $config;
	}

}
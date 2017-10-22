<?php

/*
* @author: 4061470@qq.com
*/

class HelperCookie
{
	//字段串过滤
    public static function get($key) 
    {
		$pre = self::get_cookie_pre();
		$key = $pre.$key;
		return isset($_COOKIE[$key]) ? htmlspecialchars($_COOKIE[$key]) : '';
	}

	public static function set($key, $value='', $expire=0, $httponly=false,$without_pre=false)
	{
		//清空
		if($value == '' || $expire < 0) {
			$value = '';
			$expire = -1;
		}

		$now    = time();
		$config = Nice::app()->getProperty('cookie');
		$expire = $expire > 0 ? $now + $expire : ($expire < 0 ? $now - 31536000 : 0);
		$path   = isset($config['path']) ? $config['path'] : '/';
		$domain = isset($config['domain']) ? $config['domain'] : '';
		$secure = $_SERVER['SERVER_PORT'] == 443 ? 1 : 0;
		if(!$without_pre){
			$pre    = self::get_cookie_pre();
			$key    = $pre.$key;
		}

		@setcookie($key, $value, $expire, $path, $domain, $secure, $httponly);
	}

	public static function get_cookie_pre($domain='',$path='',$pre='')
	{
		$config = array();
		if(class_exists('Nice')){
			$config = Nice::app()->getProperty('cookie');
		}
		$domain = empty($domain) ? '' : $domain;//isset($config['domain']) ? $config['domain'] : '';
		$path   = empty($path) ? (isset($config['path']) ? $config['path'] : '/') : $path;
		if(substr($path, 0, 1) != '/') {
			$path = '/'.$path;
		}
		$pre = empty($pre) ? (isset($config['pre']) ? $config['pre'] : '') : $pre;
		$pre = $pre.substr(md5($path.'|'.$domain), 0, 4).'_';

		return $pre;
	}

}
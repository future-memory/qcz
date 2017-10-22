<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: discuz_error.php 31845 2012-10-17 03:21:58Z zhangguosheng $
 */


class HelperLang
{
	public static $lang = array();

	public static function lang($file, $langvar = null, $vars = array(), $default = null) 
	{
		$arr = explode('/', $file);
		$path = isset($arr[1]) ? $arr[0] : '';
		$file = isset($arr[1]) ? $arr[1] : $arr[0];

		$key = $path == '' ? $file : $path.'_'.$file;
		if(!isset(self::$lang[$key])) {
			include BASE_ROOT.'/source/language/'.($path == '' ? '' : $path.'/').'lang_'.$file.'.php';
			self::$lang[$key] = $lang;
		}

		if(defined('IN_MOBILE') && !defined('TPL_DEFAULT')) {
			include BASE_ROOT.'/source/language/mobile/lang_template.php';
			self::$lang[$key] = array_merge(self::$lang[$key], $lang);
		}

		$return  = $langvar !== null ? (isset(self::$lang[$key][$langvar]) ? self::$lang[$key][$langvar] : null) : self::$lang[$key];
		$return  = $return === null ? ($default !== null ? $default : $langvar) : $return;
		$searchs = $replaces = array();

		if($vars && is_array($vars)) {
			foreach($vars as $k => $v) {
				$searchs[] = '{'.$k.'}';
				$replaces[] = $v;
			}
		}

		$return = str_replace($searchs, $replaces, $return);

		return $return;
	}
}
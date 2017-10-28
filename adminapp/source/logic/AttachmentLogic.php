<?php

class AttachmentLogic extends Logic
{
	private static $path   = null;
	public static $modules = array('app', 'forum', 'album', 'misc', 'temp', 'shop', 'live');
	public static $default_module = 'misc';

	/**
	 * 目录
	 */
	public static function  check_path($path)
	{
		$path = trim($path);
		$path = trim($path, '/');
		if(!$path){
			return false;
		}

		$paths = explode('/', $path);
		if(count($path_arr) >= 5){
			return false;
		}

		$module = $paths[0];
		if(!in_array($module, self::$modules)){
			return false;
		}

		foreach ($paths as $folder){
			if(preg_match('/^[0-9a-z]{1,40}$/iu', $folder)){
				self::$path .= $folder.'/';
				if(!is_dir(SITE_ROOT.'/data/attach/'.self::$path)){
					@mkdir(SITE_ROOT.'/data/attach/'.self::$path, 0755);
				}
				if(!is_dir(SITE_ROOT.'/data/attach/'.self::$path)){
					self::$path = '';
					return false;
				}
			}
		}

		return self::$path;
	}

	//生成保存的实际路径
	public function get_filepath($module, $filename)
	{
        $path = $module.'/'.date('Y/m/d/His');
        $ext  = substr($filename, strrpos($filename, '.'));
        $name = $path.HelperAuth::random(6).$ext;

        return $name;
	}

	//上传
	public function upload($path, $file, $allow_size=1000000)
	{
		if(!self::check_path($path)){
			return false;
		}

		$target = SITE_ROOT.'/data/attach/'.trim($path, '/');

		if(@copy($file['tmp_name'], $target)) {
			return true;
		}

		if(move_uploaded_file($file['tmp_name'], $target)) {
			return true;
		}

		return false;
	}


}

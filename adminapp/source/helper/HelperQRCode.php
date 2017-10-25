<?php
/*
* @author: 4061470@qq.com
*/
class HelperQRCode
{
	private static $qr_path;
	/**
	 * 生成加LOGO的二堆码
	 */
	public static function qrcode($text, $logo=false, $mod='qrcode')
	{
		if (!$text)	{
			return false;
		}
		if ($logo){
			$file_logo = file_get_contents($logo);
			if (!$file_logo){
				return false;
			}
		}
		require_once BASE_ROOT.'/source/sdk/qrcode/qrcode.php';
		$qrcode = new QRcode();
		$filename = md5($text.$logo);
		if(empty(self::$qr_path)){
			self::$qr_path = self::set_qr_path($mod);
		}
		if(empty(self::$qr_path)){
			return false;
		}

		$path = self::$qr_path.$filename.'.png';

		if (!file_exists(BASE_ROOT.'/data/attach/'.$path)){
			$errorCorrectionLevel = 'L';
			$matrixPointSize = 10;

			$qrcode->png($text, BASE_ROOT.'/data/attach/'.$path, $errorCorrectionLevel, $matrixPointSize, 2);

			$qr_content = null;
			if($logo && $file_logo){
				$qr_content     = file_get_contents(BASE_ROOT.'/data/attach/'.$path);
				$QR             = imagecreatefromstring();
				$logo           = imagecreatefromstring($file_logo);
				$QR_width       = imagesx($QR);
				$QR_height      = imagesy($QR);
				$logo_width     = imagesx($logo);
				$logo_height    = imagesy($logo);
				$logo_qr_width  = $QR_width / 5;
				$scale          = $logo_width / $logo_qr_width;
				$logo_qr_height = $logo_height / $scale;
				$from_width     = ($QR_width - $logo_qr_width) / 2;
				imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);
				imagepng($QR, BASE_ROOT.'/data/attach/'.$path);
			}
		}

		//上传到又拍云
		if(file_exists(BASE_ROOT.'/data/attach/'.$path)){
			$qr_content = $qr_content==null ? file_get_contents(BASE_ROOT.'/data/attach/'.$path) : $qr_content;
			ObjectCreater::create('HelperUpyun')->upload($qr_content, $path);
			unlink(BASE_ROOT.'/data/attach/'.$path);
		}

		return $path;
	}

	/**
	 * 二维码保存路径
	 */
	public static function  set_qr_path($mod, $path=null)
	{
		if(empty($path)){
			$date_path = strpos($mod, '/') !== false ? false : true;
			$path = '';
			if($date_path){
				$path = $mod.'/'.date("Ym").'/';
			}else{
				$path =  $mod;
			}
		}
		$path_arr = explode('/', $path);
		self::$qr_path = '';
		if(count($path_arr) >= 5){
			return false;
		}

		foreach ($path_arr as $folder){
			if(preg_match('/^[0-9a-z]{1,40}$/iu', $folder)){
				self::$qr_path .= $folder.'/';
				if(!is_dir(BASE_ROOT.'/data/attach/'.self::$qr_path)){
					@mkdir(BASE_ROOT.'/data/attach/'.self::$qr_path,0755);
				}
				if(!is_dir(BASE_ROOT.'/data/attach/'.self::$qr_path)){
					self::$qr_path = '';
					return false;
				}
			}
		}

		return self::$qr_path;
	}
}
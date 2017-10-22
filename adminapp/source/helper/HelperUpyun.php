<?php

/*
* @author: 4061470@qq.com
*/

class HelperUpyun
{
	/*
	 * 最大允许数量
	 */	
	private $max_file_num = 9;

	/*
	 * 允许网友上传的 文件存储模块 
	 */
	private $public_modules = array('forum', 'group', 'album', 'portal', 'temp', 'category', 'profile', 'tag', 'topic');
	/*
	 * 文件存储模块
	 */
	private $modules = array('app','forum', 'group', 'album', 'portal', 'common', 'temp', 'category', 'profile', 'shop', 'live', 'recommend', 'tag', 'topic');
	/*
	 * 日期文件夹 存储模块
	 */
	private $date_modules = array('app', 'forum', 'group', 'album', 'portal', 'common', 'temp', 'category', 'profile', 'shop', 'live', 'recommend', 'tag', 'topic');
	/*
	 * 允许上传的文件后缀
	 */
	private $allow_ext = array('attach', 'jpg', 'jpeg', 'gif', 'png', 'swf', 'bmp', 'txt', 'zip', 'rar', 'mp3', 'apk', 'cab', 'chm', 'pdf', 'tar', 'eif', 'gz', 'bzip2', 'cfc');
	/*
	 * 图片后缀
	 */
	private $img_ext = array('jpg', 'jpeg', 'gif', 'png', 'bmp');

	/*
	 * 默认图片模块
	 */
	private $default_module = 'forum';

	public $code_message = array(
		0 => 'ok',
		1 => '上传文件无效',
		2 => '上传文件大小不符',
		3 => '上传文件拓展名错误',
		4 => '存储模块错误',
		);

	/**
	 * 又拍云上传表单参数签名
	 */
	public function upyun_sign($uid, $module, $file_num, $maxsize=2097152)
	{
		// 后台允许上传所有模块
		// $module = in_array($module, $this->public_modules) ? $module : $this->default_module;
		$module = in_array($module, $this->modules) ? $module : $this->default_module;
		//允许上传的图片后缀
		$img_exts_str = implode(',', $this->img_ext);
		//图片大小范围
		$length_range = '1,'.$maxsize ;
		//签名有效时长
		$policy_expire = UPYUN_POLICY_EXPIRE;
	
		//上传文件个数
		$file_num = $file_num>1 ? $file_num : 1;
		$file_num = $file_num > $this->max_file_num ? $this->max_file_num : $file_num;

		$path = '/'.$module.'/'.date('Y/m/d/His');
		$data = array();
		$data['url'] = 'https://'.UPYUN_CDN_DOMAIN.'/'.UPYUN_CDN_BUCKET;


		for($num=0;$num<$file_num;$num++){
			$savekey = $path.HelperAuth::random(16);
			$params  = array(
					'bucket' => UPYUN_CDN_BUCKET,
					'expiration' => time() + $policy_expire,
					'save-key' => $savekey.'{.suffix}',
					'allow-file-type' => $img_exts_str,
					'content-length-range' => $length_range
			);
			$policy    = base64_encode(json_encode($params));
			$signature = md5($policy.'&'.UPYUN_CDN_API_KEY);
			$fields    = array('signature'=>$signature, 'policy'=>$policy);
			$data['signs'][] = $fields;
		}
		return $data;
	}

	public function set_file_info($fileinfo)
	{
		$fileinfo_arr = json_decode($fileinfo,true);
		$this->fileinfos = array();
		$attachments = array();
		if(!empty($fileinfo_arr)){
			foreach ($fileinfo_arr as $file){
				$tip = $oss->set_fileinfo_without_upload($file,true);
				if($tip){
					$fileinfo_new = $oss->get_upload_fileinfo();
					if(empty($fileinfo_new['attachment']) || in_array($fileinfo_new['attachment'], $attachments)){
						continue;
					}
					$attachments[] = $fileinfo_new['attachment'];
					$fileinfo_new['description'] = 'pic from app';
					$this->fileinfos[] = $fileinfo_new;
				}
			}
		}
		$file_num = count($fileinfo_arr);
		$oss->set_aids($file_num);
		$this->aids = $oss->get_aids();
		$attachs = '';
		foreach ($this->aids as $key=>$aid){
			if($key === 0){
				$this->cover_aid = $aid;
			}
			$attachs .= '[attach]'.$aid.'[/attach]';
		}
		return $attachs;
	}

	/**
	 * 又拍云cdn推送 刷新
	 */
	public static function refresh_cdn($urls)
	{
		$date     = gmdate('D, d M Y H:i:s \G\M\T');
		$url_str  = is_array($urls) ? implode('\n', $urls) : $urls;
		$sign_str = $url_str.'&'.CDN_BUCKET.'&'.$date.'&'.md5(CDN_KEY);
		$auth     = 'UpYun '.CDN_BUCKET.':'.CDN_OPERATOR.':'.md5($sign_str);
		$headers  = array();
		array_push($headers, "Authorization: {$auth}");
		array_push($headers, "Date: {$date}");
		$data = array('purge'=>$url_str);
		return HelperUtils::http_post(CDN_PUSH_URL, $data,$headers);
	}

	/**
	 * 直接上传又拍云
	 * file_content 文件内容
	 * file_path url中去掉域名部分，包括目录 
	 */
	public function upload($file_content, $file_path)
	{
		require_once BASE_ROOT.'/source/sdk/upyun/upyun.class.php';
		$content_types = array(
			'png'  => 'image/png',
			'jpg'  => 'image/jpeg',
			'gif'  => 'image/gif',
			'jpeg' => 'image/jpeg',
			'bmp'  => 'application/x-bmp',
			'mp3'  => 'audio/mpeg',
			'js'   => 'application/javascript'
		);

		$upyun = new UpYun(UPYUN_CDN_BUCKET, UPYUN_CDN_OPERATOR, UPYUN_CDN_KEY);
		$file_path = '/'.ltrim($file_path, '/');
		$name = basename($file_path);
		$ext = pathinfo($file_path, PATHINFO_EXTENSION);
		$ext = strtolower($ext);

		$headers = array('Content-type'=>$content_types[$ext]);

		try {
			$upyun->writeFile($file_path, $file_content, true, $headers);
		}catch(Exception $e) {
			HelperLog::writelog('upyun', $e->getCode().':'.$e->getMessage());
			return false;
		}

		return array(
			'size'       => strlen($file_content),
			'name'       => $name,
			'ext'        => $ext,
			'isimage'    => in_array($ext, $this->img_ext) ? 1 : 0,
			'attachment' => $file_path,
			'url'        => FILE_DOMAIN . ltrim($file_path, '/'),
		);
	}

	/**
	 * [save description]
	 * @param  [type]  $file         [description]
	 * @param  string  $type         空默认为temp
	 * @param  integer $allow_size   小于等于0不限制
	 * @param  string  $special_type 上传 mp3 js等
	 * @return [type]                [description]
	 */
	public function save($file, $type='app', $allow_size = 102400, $special_type = '', $thumb_width=0) {
		// 是否是上传file元素
		if (!is_array($file) || !isset($file['name']) || !isset($file['type']) || !isset($file['tmp_name']) || !isset($file['error']) || !isset($file['size'])) {
			return $this->code_message[1];
		}
		// 是否上传成功
		if ((int)$file['error'] !== 0 || !file_exists($file['tmp_name'])) {
			return $this->code_message[1];
		}

		// 判断大小
		if ($allow_size > 0 && (int)$file['size'] > $allow_size) {
			return $this->code_message[2];
		}

		// 文件类型
		$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
		if ($special_type) {
			if ($ext !== $special_type) {
				return $this->code_message[3];
			}
		} else {
			if (!in_array($ext, $this->img_ext)) {
				return $this->code_message[3];
			}
		}

		empty($type) || !in_array($type, $this->modules) && $type = 'temp';

		// 判断模块
		if ($type !== 'app' && !in_array($type, $this->modules)) {
			return $this->code_message[4];
		}
		// 获得存储文件路径
		$dir = $this->get_dir($type, in_array($type, $this->date_modules));
		$basename = $this->get_file_basename();
		$file_path = $dir . $basename . '.' . $ext;
		$file_content = file_get_contents($file['tmp_name']);


		$attach =  $this->upload($file_content, $file_path);

		return $attach;
	}

	/**
	 * 获取云存储文件目录
	 */
	public function get_dir($module='temp', $date_dir=false, $dir=''){
		if(!empty($dir) && preg_match('/^([0-9a-z_]{1,20}\/){1,5}/iu', $dir)){//目录最深不过5层
			return $dir;
		}elseif(preg_match('/^[0-9a-z_]{1,20}$/iu', $module) && in_array($module, $this->modules)){
			return $module.'/'.($date_dir ? date('Y/m/d/') : ''); 
		}
	}

	/**
	 * 获取目标文件名
	 */
	public function get_file_basename($filename=''){
		if(!empty($filename)){
			return $filename;
		}else{
			return date('His').strtolower(HelperAuth::random(16));
		}
	}
}
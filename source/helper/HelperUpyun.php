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
		);

		$upyun     = new UpYun(UPYUN_CDN_BUCKET, UPYUN_CDN_OPERATOR, UPYUN_CDN_KEY);
		$file_path = '/'.ltrim($file_path, '/');
		$name = basename($file_path);
		$ext       = pathinfo($file_path, PATHINFO_EXTENSION);
		$headers   = array('Content-type'=>$content_types[$ext]);

		try {
			$upyun->writeFile($file_path, $file_content, true, $headers);
		}catch(Exception $e) {
			HelperLog::writelog('upyun', $e->getCode().':'.$e->getMessage().$file_path.var_export($headers, true));
			return false;
		}

		return array(
			'size'       => strlen($file_content),
			'name'       => $name,
			'ext'        => $ext,
			'isimage'    => in_array($ext, $this->img_ext) ? 1 : 0,
			'attachment' => $file_path
		);
	}


}
<?php
//与业务逻辑相关，但有独立的函数都写这里

class HelperBiz 
{
	//检查是否开启了模块
	public static function check_module_access($module) 
	{
		$allow  = array('portal','group','follow','collection','guide','feed','blog','doing','album','share','wall','homepage','ranklist');
		$status = $module && in_array($module, $allow) ? ObjectCreater::create('SettingLogic')->get($module.'status') : 0;
		return $status;
	}

	//根据tid获取附件tableid
	public static function get_attach_tableid($tid) 
	{
		$tid = (string)$tid;
		return intval($tid{strlen($tid)-1});
	}

	//获取缩略图
	public static function get_img_thumb_name($fileStr, $extend='.thumb.jpg', $holdOldExt=true) 
	{
		if(empty($fileStr)) {
			return '';
		}
		if(!$holdOldExt) {
			$fileStr = substr($fileStr, 0, strrpos($fileStr, '.'));
		}
		$extend = strstr($extend, '.') ? $extend : '.'.$extend;
		return $fileStr.$extend;
	}


	public static function periodscheck($periods, $showmessage = 1) 
	{
		$settings = ObjectCreater::create('SettingLogic')->muti_get(array('postignorearea','postignoreip',$periods));

		if(($periods == 'postmodperiods' || $periods == 'postbanperiods') && ($settings['postignorearea'] || $settings['postignoreip'])) {
			$clientip = HelperUtils::getClientIP();
			if($settings['postignoreip']) {
				foreach(explode("\n", $settings['postignoreip']) as $ctrlip) {
					if(preg_match("/^(".preg_quote(($ctrlip = trim($ctrlip)), '/').")/", $clientip)) {
						return false;
						break;
					}
				}
			}
			if($settings['postignorearea']) {
				$location = $whitearea = '';
				$location = trim(HelperUtils::convertip($clientip, "./"));
				if($location) {
					$whitearea = preg_quote(trim($settings['postignorearea']), '/');
					$whitearea = str_replace(array("\\*"), array('.*'), $whitearea);
					$whitearea = '.*'.$whitearea.'.*';
					$whitearea = '/^('.str_replace(array("\r\n", ' '), array('.*|.*', ''), $whitearea).')$/i';
					if(@preg_match($whitearea, $location)) {
						return false;
					}
				}
			}
		}

		$group_filed = ObjectCreater::create('GroupLogic')->get_cur_group_fields();

		if(!$group_filed['disableperiodctrl'] && $settings[$periods]) {
			$now = date('G.i');
			foreach(explode("\r\n", str_replace(':', '.', $settings[$periods])) as $period) {
				list($periodbegin, $periodend) = explode('-', $period);
				if(($periodbegin > $periodend && ($now >= $periodbegin || $now < $periodend)) || ($periodbegin < $periodend && $now >= $periodbegin && $now < $periodend)) {
					$banperiods = str_replace("\r\n", ', ', $settings[$periods]);

					return true;
				}
			}
		}
		return false;
	}

	public static function get_url_list_from_message($message) 
	{
		$return = array();

		(strpos($message, '[/img]') || strpos($message, '[/flash]')) && $message = preg_replace("/\[img[^\]]*\]\s*([^\[\<\r\n]+?)\s*\[\/img\]|\[flash[^\]]*\]\s*([^\[\<\r\n]+?)\s*\[\/flash\]/is", '', $message);
		if(preg_match_all("/((https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|thunder|qqdl|synacast){1}:\/\/|www\.)[^ \[\]\"']+/i", $message, $urllist)) {
			foreach($urllist[0] as $key => $val) {
				$val = trim($val);
				$return[0][$key] = $val;
				if(!preg_match('/^http:\/\//is', $val)) $val = 'http://'.$val;
				$tmp = parse_url($val);
				$return[1][$key] = $tmp['host'];
				if($tmp['port']){
					$return[1][$key] .= ":$tmp[port]";
				}
			}
		}
		return $return;
	}


	/**
	 * 检查跳转域名白名单
	 * @param string $url
	 * @return boolean
	 */
	public static function check_redirect_url($url){
		$info   = parse_url($url);
		if(!empty($info['host'])){
			$domain = substr($info['host'], strpos($info['host'], '.'));
			$rootdm = trim(substr(DOMAIN, strpos(DOMAIN, '.')), '/');
			if($domain==$rootdm){
				return true;
			}
		}
		return false;
	}
	/**
	 * 跳转来源
	 */
	public static function get_referer($default_referer=''){
		$referer = '';
		if(!empty($_SERVER['HTTP_REFERER']) && self::check_redirect_url($_SERVER['HTTP_REFERER'])){
			$referer = $_SERVER['HTTP_REFERER'];
		}elseif(self::check_redirect_url($default_referer)){
			$referer = $default_referer;
		}
		return $referer;
	}

}

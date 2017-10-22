<?php

/*
* @author: 4061470@qq.com
*/

class HelperUtils
{
	//字段串过滤
    public static function strFilter($value) 
    {
		return is_string($value) ? htmlspecialchars($value) : $value;
	}

	public static function jsonHtmlFilter($data)
	{
		array_walk_recursive($data, "HelperUtils::strFilter");
		return $data;
	}

	//获取客户端ip
	public static function getClientIP()
	{
		$ip  = $_SERVER['REMOTE_ADDR'];
		$xfw = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : null;
		$ips = explode(',', $xfw);
		$xip = isset($ips[0]) && $ips[0] ? trim($ips[0]) : null;

		if(preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $xip)){
			$ip = $xip;
		}elseif(isset($_SERVER['HTTP_X_REAL_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_X_REAL_IP'])){
			$ip = $_SERVER['HTTP_X_REAL_IP'];
		}elseif (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
			foreach ($matches[0] AS $xip) {
				if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
					$ip = $xip;
					break;
				}
			}
		}

		return $ip;

	}

	//导出csv 不支持匿名函数，只能先这样了
    public static function export_csv_start($filename, $title_arr)
    {
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-type: text/csv; charset=gbk");
        header("Content-Disposition: attachment; filename=" . $filename);
        header("Content-Transfer-Encoding: binary");
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header('Cache-Control:   must-revalidate,   post-check=0,   pre-check=0');
        header("Pragma: no-cache");
        
        //标题栏
        echo iconv('utf-8', 'gbk//ignore', join(',', $title_arr)) , "\n";
    
    }

	//导出csv end 不支持匿名函数，只能先这样了
    public static function export_csv_end()
    {
        flush();
        ob_flush();
        echo PHP_EOL;//否则，文件内容乱码
        exit;//否则，带有php文件内容        
    }

    //检查url格式
	public static function check_url($value)
	{
		$pattern  ='/^(http||https):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)/i';
		$pattern2 ='/^\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)/i';
		return (is_string($value) && strlen($value)<2000 && (preg_match($pattern,$value) || preg_match($pattern2,$value)));
	}


	public static function dintval($int, $allowarray = false) 
	{
		$ret = intval($int);
		if($int == $ret || !$allowarray && is_array($int)) return $ret;
		if($allowarray && is_array($int)) {
			foreach($int as &$v) {
				$v = self::dintval($v, true);
			}
			return $int;
		} elseif($int <= 0xffffffff) {
			$l = strlen($int);
			$m = substr($int, 0, 1) == '-' ? 1 : 0;
			if(($l - $m) === strspn($int,'0987654321', $m)) {
				return $int;
			}
		}
		return $ret;
	}

	//反序列化
	public static function dunserialize($data) 
	{
		if(($ret = unserialize($data)) === false) {
			$ret = unserialize(stripslashes($data));
		}
		return $ret;
	}	

	//时间格式 只保留个性化格式  普通格式直接用 date
	public static function dgmdate($timestamp) 
	{
		static $lang;
		$lang = HelperLang::lang('core', 'date');
		$format = empty($format) || $format == 'dt' ? 'Y-m-d H:i' : ($format == 'd' ? 'Y-m-d' : ($format == 't' ? 'H:i' : $format));

		$today = TIMESTAMP - (TIMESTAMP) % 86400;
		$time  = TIMESTAMP - $timestamp;
		$days  = intval(($today - $timestamp) / 86400);

		if($timestamp >= $today) {
			if($time > 3600) {
				return intval($time / 3600).'&nbsp;'.$lang['hour'].$lang['before'];
			} elseif($time > 1800) {
				return $lang['half'].$lang['hour'].$lang['before'];
			} elseif($time > 60) {
				return intval($time / 60).'&nbsp;'.$lang['min'].$lang['before'];
			} elseif($time > 0) {
				return $time.'&nbsp;'.$lang['sec'].$lang['before'];
			} elseif($time == 0) {
				return $lang['now'];
			} else {
				return date('Y-m-d H:i',$timestamp);
			}
		} elseif(($days) >= 0 && $days < 7) {
			if($days == 0) {
				return $lang['yday'].'&nbsp;'.gmdate('H:i', $timestamp);
			} elseif($days == 1) {
				return $lang['byday'].'&nbsp;'.gmdate('H:i', $timestamp);
			} else {
				return ($days + 1).'&nbsp;'.$lang['day'].$lang['before'];
			}
		}elseif(($days >= 7) && $days < 365 ) {
		    //7天以上 一年以内的 用**月**日
            return date('n月j日', $timestamp);
        }elseif($days>365){
            //一年以上的
            return date('Y年n月j日',$timestamp);
        }else {
			return date('Y-m-d H:i',$timestamp);
		}
	}	

	//头像
	public static function avatar($uid, $size = 'middle', $update=true) 
	{
		$sizes = array(
			'big'    => 'w200h200',
			'middle' => 'w100h100',
			'small'  => 'w50h50'
		);

		$size = isset($sizes[$size]) ? $sizes[$size] : 'w100h100';
		$uid  = abs(intval($uid));
		$tmp  = str_pad($uid,10,'0',STR_PAD_RIGHT);
		$dir1 = substr($tmp, 0, 2);
		$dir2 = substr($tmp, 2, 2);
		$dir3 = substr($tmp, 4, 2);
		$dir4 = substr($tmp, 6, 2);
		$dir5 = substr($tmp, 8, 2);
		$file = USER_AVATAR.$dir1.'/'.$dir2.'/'.$dir3.'/'.$dir4.'/'.$dir5.'/'.$uid.'/'.$size;
		$file = $update==true ? $file."?t=".time() : $file;

		return $file;
	}

	//discuz 函数
	public static function getstatus($status, $position) 
	{
		$t = $status & pow(2, $position - 1) ? 1 : 0;
		return $t;
	}

	//discuz 函数
	public static function setstatus($position, $value, $baseon = null) 
	{
		$t = pow(2, $position - 1);
		if($value) {
			$t = $baseon | $t;
		} elseif ($baseon !== null) {
			$t = $baseon & ~$t;
		} else {
			$t = ~$t;
		}
		return $t & 0xFFFF;
	}

	//检查跳转url的合法性
	public static function check_redirect_url($url)
	{
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

	//检查计算符号的合法性
	public static function check_glue($glue = '=') 
	{
		return in_array($glue, array('=', '<', '<=', '>', '>=', '!=', '+', '-', '|', '&', '<>')) ? $glue : '=';
	}	

	//implode
	public static function dimplode($array) 
	{
		if(!empty($array)) {
			$array = array_map('addslashes', $array);
			return "'".implode("','", is_array($array) ? $array : array($array))."'";
		} else {
			return 0;
		}
	}

	//单位转换
	public static function sizecount($size) 
	{
	        $size = intval($size);
		if($size >= 1073741824) {
			$size = round($size / 1073741824 * 100) / 100 . ' GB';
		} elseif($size >= 1048576) {
			$size = round($size / 1048576 * 100) / 100 . ' MB';
		} elseif($size >= 1024) {
			$size = round($size / 1024 * 100) / 100 . ' KB';
		} else {
			$size = $size . ' Bytes';
		}
		return $size;
	}

	public static function convertip($ip) 
	{
		$return = '';
		if(preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/", $ip)) {
			$iparray = explode('.', $ip);
			if($iparray[0] == 10 || $iparray[0] == 127 || ($iparray[0] == 192 && $iparray[1] == 168) || ($iparray[0] == 172 && ($iparray[1] >= 16 && $iparray[1] <= 31))) {
				$return = '- LAN';
			} elseif($iparray[0] > 255 || $iparray[1] > 255 || $iparray[2] > 255 || $iparray[3] > 255) {
				$return = '- Invalid IP Address';
			} else {
				$tinyipfile = BASE_ROOT.'/data/ipdata/tinyipdata.dat';
				$fullipfile = BASE_ROOT.'/data/ipdata/wry.dat';
				if(@file_exists($tinyipfile)) {
					$return = self::convertip_tiny($ip, $tinyipfile);
				} elseif(@file_exists($fullipfile)) {
					$return = self::convertip_full($ip, $fullipfile);
				}
			}
		}

		return $return;
	}

	public static function convertip_tiny($ip, $ipdatafile) 
	{
		static $fp = NULL, $offset = array(), $index = NULL;

		$ipdot    = explode('.', $ip);
		$ip       = pack('N', ip2long($ip));
		$ipdot[0] = (int)$ipdot[0];
		$ipdot[1] = (int)$ipdot[1];

		if($fp === NULL && $fp = @fopen($ipdatafile, 'rb')) {
			$offset = @unpack('Nlen', @fread($fp, 4));
			$index  = @fread($fp, $offset['len'] - 4);
		} elseif($fp == FALSE) {
			return  '- Invalid IP data file';
		}

		$length = $offset['len'] - 1028;
		$start  = @unpack('Vlen', $index[$ipdot[0] * 4] . $index[$ipdot[0] * 4 + 1] . $index[$ipdot[0] * 4 + 2] . $index[$ipdot[0] * 4 + 3]);

		for($start = $start['len'] * 8 + 1024; $start < $length; $start += 8) {
			if ($index{$start} . $index{$start + 1} . $index{$start + 2} . $index{$start + 3} >= $ip) {
				$index_offset = @unpack('Vlen', $index{$start + 4} . $index{$start + 5} . $index{$start + 6} . "\x0");
				$index_length = @unpack('Clen', $index{$start + 7});
				break;
			}
		}

		@fseek($fp, $offset['len'] + $index_offset['len'] - 1024);
		if($index_length['len']) {
			return '- '.@fread($fp, $index_length['len']);
		} else {
			return '- Unknown';
		}
	}

	public static function convertip_full($ip, $ipdatafile) 
	{
		if(!$fd = @fopen($ipdatafile, 'rb')) {
			return '- Invalid IP data file';
		}

		$ip = explode('.', $ip);
		$ipNum = $ip[0] * 16777216 + $ip[1] * 65536 + $ip[2] * 256 + $ip[3];

		if(!($DataBegin = fread($fd, 4)) || !($DataEnd = fread($fd, 4)) ) return;
		@$ipbegin = implode('', unpack('L', $DataBegin));
		if($ipbegin < 0) $ipbegin += pow(2, 32);
		@$ipend = implode('', unpack('L', $DataEnd));
		if($ipend < 0) $ipend += pow(2, 32);
		$ipAllNum = ($ipend - $ipbegin) / 7 + 1;

		$BeginNum = $ip2num = $ip1num = 0;
		$ipAddr1  = $ipAddr2 = '';
		$EndNum   = $ipAllNum;

		while($ip1num > $ipNum || $ip2num < $ipNum) {
			$Middle= intval(($EndNum + $BeginNum) / 2);
			fseek($fd, $ipbegin + 7 * $Middle);
			$ipData1 = fread($fd, 4);
			if(strlen($ipData1) < 4) {
				fclose($fd);
				return '- System Error';
			}
			$ip1num = implode('', unpack('L', $ipData1));
			if($ip1num < 0) $ip1num += pow(2, 32);

			if($ip1num > $ipNum) {
				$EndNum = $Middle;
				continue;
			}

			$DataSeek = fread($fd, 3);
			if(strlen($DataSeek) < 3) {
				fclose($fd);
				return '- System Error';
			}
			$DataSeek = implode('', unpack('L', $DataSeek.chr(0)));
			fseek($fd, $DataSeek);
			$ipData2 = fread($fd, 4);
			if(strlen($ipData2) < 4) {
				fclose($fd);
				return '- System Error';
			}
			$ip2num = implode('', unpack('L', $ipData2));
			if($ip2num < 0) $ip2num += pow(2, 32);

			if($ip2num < $ipNum) {
				if($Middle == $BeginNum) {
					fclose($fd);
					return '- Unknown';
				}
				$BeginNum = $Middle;
			}
		}

		$ipFlag = fread($fd, 1);
		if($ipFlag == chr(1)) {
			$ipSeek = fread($fd, 3);
			if(strlen($ipSeek) < 3) {
				fclose($fd);
				return '- System Error';
			}
			$ipSeek = implode('', unpack('L', $ipSeek.chr(0)));
			fseek($fd, $ipSeek);
			$ipFlag = fread($fd, 1);
		}

		if($ipFlag == chr(2)) {
			$AddrSeek = fread($fd, 3);
			if(strlen($AddrSeek) < 3) {
				fclose($fd);
				return '- System Error';
			}
			$ipFlag = fread($fd, 1);
			if($ipFlag == chr(2)) {
				$AddrSeek2 = fread($fd, 3);
				if(strlen($AddrSeek2) < 3) {
					fclose($fd);
					return '- System Error';
				}
				$AddrSeek2 = implode('', unpack('L', $AddrSeek2.chr(0)));
				fseek($fd, $AddrSeek2);
			} else {
				fseek($fd, -1, SEEK_CUR);
			}

			while(($char = fread($fd, 1)) != chr(0))
			$ipAddr2 .= $char;

			$AddrSeek = implode('', unpack('L', $AddrSeek.chr(0)));
			fseek($fd, $AddrSeek);

			while(($char = fread($fd, 1)) != chr(0))
			$ipAddr1 .= $char;
		} else {
			fseek($fd, -1, SEEK_CUR);
			while(($char = fread($fd, 1)) != chr(0))
			$ipAddr1 .= $char;

			$ipFlag = fread($fd, 1);
			if($ipFlag == chr(2)) {
				$AddrSeek2 = fread($fd, 3);
				if(strlen($AddrSeek2) < 3) {
					fclose($fd);
					return '- System Error';
				}
				$AddrSeek2 = implode('', unpack('L', $AddrSeek2.chr(0)));
				fseek($fd, $AddrSeek2);
			} else {
				fseek($fd, -1, SEEK_CUR);
			}
			while(($char = fread($fd, 1)) != chr(0))
			$ipAddr2 .= $char;
		}
		fclose($fd);

		if(preg_match('/http/i', $ipAddr2)) {
			$ipAddr2 = '';
		}
		$ipaddr = "$ipAddr1 $ipAddr2";
		$ipaddr = preg_replace('/CZ88\.NET/is', '', $ipaddr);
		$ipaddr = preg_replace('/^\s*/is', '', $ipaddr);
		$ipaddr = preg_replace('/\s*$/is', '', $ipaddr);
		if(preg_match('/http/i', $ipaddr) || $ipaddr == '') {
			$ipaddr = '- Unknown';
		}

		return '- '.$ipaddr;
	}

	public static function cutstr($string, $length, $dot = ' ...') 
	{
		if(strlen($string) <= $length) {
			return $string;
		}

		$string = str_replace(array('&amp;', '&quot;', '&lt;', '&gt;'), array('&', '"', '<', '>'), $string);
		$strcut = '';

		$n = $tn = $noc = 0;
		while($n < strlen($string)) {

			$t = ord($string[$n]);
			if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
				$tn = 1; $n++; $noc++;
			} elseif(194 <= $t && $t <= 223) {
				$tn = 2; $n += 2; $noc += 2;
			} elseif(224 <= $t && $t < 239) {
				$tn = 3; $n += 3; $noc += 2;
			} elseif(240 <= $t && $t <= 247) {
				$tn = 4; $n += 4; $noc += 2;
			} elseif(248 <= $t && $t <= 251) {
				$tn = 5; $n += 5; $noc += 2;
			} elseif($t == 252 || $t == 253) {
				$tn = 6; $n += 6; $noc += 2;
			} else {
				$n++;
			}

			if($noc >= $length) {
				break;
			}
		}
		if($noc > $length) {
			$n -= $tn;
		}

		$strcut = substr($string, 0, $n);
		$strcut = str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $strcut);

		return $strcut.$dot;
	}
	
	public static function daddslashes($string, $force = 1) 
	{
	    if(is_array($string)) {
	        $keys = array_keys($string);
	        foreach($keys as $key) {
	            $val = $string[$key];
	            unset($string[$key]);
	            $string[addslashes($key)] = self::daddslashes($val, $force);
	        }
	    } else {
	        $string = addslashes($string);
	    }
	    return $string;
	}

	public static function checkrobot($useragent = '') 
	{
		static $kw_spiders  = array('bot', 'crawl', 'spider' ,'slurp', 'sohu-search', 'lycos', 'robozilla');
		static $kw_browsers = array('msie', 'netscape', 'opera', 'konqueror', 'mozilla');

		$useragent = strtolower(empty($useragent) ? $_SERVER['HTTP_USER_AGENT'] : $useragent);
		if(strpos($useragent, 'http://') === false && self::dstrpos($useragent, $kw_browsers)) return false;
		if(self::dstrpos($useragent, $kw_spiders)) return true;
		return false;
	}

	public static function dstrpos($string, &$arr, $returnvalue = false) 
	{
		if(empty($string)) return false;
		foreach((array)$arr as $v) {
			if(strpos($string, $v) !== false) {
				$return = $returnvalue ? $v : true;
				return $return;
			}
		}
		return false;
	}

	public static function checkmobile($check_browser_only=false) {
		$mobile = array();
		static $mobilebrowser_list =array('iphone', 'android', 'phone', 'mobile', 'wap', 'netfront', 'java', 'opera mobi', 'opera mini',
					'ucweb', 'windows ce', 'symbian', 'series', 'webos', 'sony', 'blackberry', 'dopod', 'nokia', 'samsung',
					'palmsource', 'xda', 'pieplus', 'meizu', 'midp', 'cldc', 'motorola', 'foma', 'docomo', 'up.browser',
					'up.link', 'blazer', 'helio', 'hosin', 'huawei', 'novarra', 'coolpad', 'webos', 'techfaith', 'palmsource',
					'alcatel', 'amoi', 'ktouch', 'nexian', 'ericsson', 'philips', 'sagem', 'wellcom', 'bunjalloo', 'maui', 'smartphone',
					'iemobile', 'spice', 'bird', 'zte-', 'longcos', 'pantech', 'gionee', 'portalmmm', 'jig browser', 'hiptop',
					'benq', 'haier', '^lct', '320x320', '240x320', '176x220','x11');
		$pad_list = array('pad', 'gt-p1000');

		$useragent = strtolower($_SERVER['HTTP_USER_AGENT']);

		if(self::dstrpos($useragent, $pad_list)) {
			return false;
		}
		if(($v = self::dstrpos($useragent, $mobilebrowser_list, true))) {
			return true;
		}
		if($check_browser_only){
			return false;
		}
		$brower = array('mozilla', 'chrome', 'safari', 'opera', 'm3gate', 'winwap', 'openwave', 'myop');
		if(self::dstrpos($useragent, $brower)) return false;

		if($_GET['mobile'] === 'yes') {
			return true;
		} else {
			return false;
		}
	}

	//获取参数
	public static function get_param($key, $default = null)
	{
		return Nice::app()->getComponent('Request')->getParam($key, $default);
	}

	public static function get_pic_url($filepath, $type='app')
	{
		// $filepath = preg_replace('/^(http||https):/i', '', $filepath);
		if(!$filepath){
			return null;
		}
		if(self::check_url($filepath, $type)){
			return $filepath;
		}

		$remote = 0;
		$path   = $filepath;
		if(strrpos($filepath, '|')!==false){
			$arr    = explode('|', $filepath);
			$remote = intval($arr[0]);
			$path   = $arr[1];
		}
		$img_url = FILE_CDN_URL . ltrim(strpos($path, $type)!==false ? '' : $type.'/', '/') . ltrim($path, '/');
		return $img_url;
	}

	/**
	 * [get_http_or_https_url description]
	 * @param  [type] $url  [description]
	 * @param  string $type http or https or //
	 * @return [type]       [description]
	 */
	public static function get_http_or_https_url($url, $type='//') {
		return preg_replace('/^(http||https):\/\//i', $type, $url);
	}

	/**
	 * 获取二维数组指定列
	 * @param array $input
	 * @param string $column_key
	 */
	public static function column_array($input , $column_key,$index_key=null){
		$data = array();
		if(function_exists('column_array')){
			$data = column_array($input,$column_key,$index_key);
		}else{
			foreach ((array)$input as $k=>$v){
				if(isset($index_key)&& isset($v[$index_key]) && isset($v[$column_key])){
					$data[$v[$index_key]] = $v[$column_key];
				}elseif(isset($v[$column_key])){
					$data[] = $v[$column_key];
				}
			}
		}
		return $data;
	}

	public static function fileext($filename)
	{
		return addslashes(strtolower(substr(strrchr($filename, '.'), 1, 10)));
	}

	/**
	 * 判断字符串开头是否存在 不存在则加上
	 */
	public static function concat_if_no_exist($str,$toconcat){
		return strpos($str, $toconcat) !== false || empty($str) ? $str : $toconcat.$str;
	}

	public static function filter_evil_net_pic($message)
	{
		$message = self::dhtmlspecialchars($message);
		$pattern = '/\[img(.*)\]\s*([^\[\<\r\n]+?)\s*\[\/img\]/ies';
		preg_match_all($pattern, $message, $matches);
	
		$replaces = array();
		if(!empty($matches) && !empty($matches[2])){
			foreach($matches[2] as $src) {
				if(!helper_util::check_net_pic($src)){
					$replaces[] = $src;
				}
			}
		}
	
		$message = !empty($replaces) ? str_replace($replaces, '', $message) : $message;
	
		return $message;
	}
	
	public static function dhtmlspecialchars($string, $flags = null, $replace_and=false) 
	{
		if(is_array($string)) {
			foreach($string as $key => $val) {
				$string[$key] = self::dhtmlspecialchars($val, $flags, $replace_and);
			}
		} else {
			if($flags === null) {
				$string = $replace_and ? str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string) : str_replace(array('"', '<', '>'), array('&quot;', '&lt;', '&gt;'), $string);

				if(strpos($string, '&amp;#') !== false) {
					$string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4}));)/', '&\\1', $string);
				}
			} else {
				if(PHP_VERSION < '5.4.0') {
					$string = htmlspecialchars($string, $flags);
				} else {
					if(strtolower(CHARSET) == 'utf-8') {
						$charset = 'UTF-8';
					} else {
						$charset = 'ISO-8859-1';
					}
					$string = htmlspecialchars($string, $flags, $charset);
				}
			}
		}
		return $string;
	}

	// 过滤代码      & 不过滤成&amp;
	public static function dhtmlspecialchars2($string, $flags = null) 
	{
		if(is_array($string)) {
			foreach($string as $key => $val) {
				$string[$key] = dhtmlspecialchars($val, $flags);
			}
		} else {
			if($flags === null) {
				$string = str_replace(array('"', '<', '>'), array('&quot;', '&lt;', '&gt;'), $string);
				if(strpos($string, '&amp;#') !== false) {
					$string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4}));)/', '&\\1', $string);
				}
			} else {
				if(PHP_VERSION < '5.4.0') {
					$string = htmlspecialchars($string, $flags);
				} else {
					if(strtolower(CHARSET) == 'utf-8') {
						$charset = 'UTF-8';
					} else {
						$charset = 'ISO-8859-1';
					}
					$string = htmlspecialchars($string, $flags, $charset);
				}
			}
		}
		return $string;
	}

	public static function remaintime($time) 
	{
		$days = intval( $time / 86400 );
		$time -= $days * 86400;
		$hours = intval( $time / 3600 );
		$time -= $hours * 3600;
		$minutes = intval( $time / 60 );
		$time -= $minutes * 60;
		$seconds = $time;
		return array(
			(int) $days,
			(int) $hours,
			(int) $minutes,
			(int) $seconds
		);
	}



	/**
	 * 服务端发起https请求 get
	 * @param unknown $url
	 * @return mixed
	 */
	public static function https_get($url, $headers=array(), $timeout=5)
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); // 获取数据返回
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, TRUE); // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

		if($headers){
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}

	/**
	 * 服务器端发起https 请求  post
	 * @param unknown $url
	 * @param unknown $data
	 * @param string $headers
	 * @param number $timeout
	 * @return mixed
	 */
	public static function https_post($url, $data, $headers=null, $timeout=5)
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

		if($headers){
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}

		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}

	/**
	 * 服务端发起http请求 get
	 * @param unknown $url
	 * @param unknown $headers
	 * @return mixed
	 */
	public static function http_get($url, $headers=array(), $timeout=5)
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE) ; // 获取数据返回
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, TRUE) ; // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回

		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

		if($headers){
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}

		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}


	/**
	 * 服务器端发起http 请求  post
	 * @param unknown $url
	 * @param unknown $data
	 * @param string $headers
	 * @param number $timeout
	 * @return mixed
	 */
	public static function http_post($url, $data, $headers=null, $timeout=5)
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

		if($headers){
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}

		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}
	/**
	 * 服务器端发起http 请求  post 返回带header
	 * @param unknown $url
	 * @param unknown $data
	 * @param string $headers
	 * @param number $timeout
	 * @return mixed
	 */
	public static function http_post_with_header($url, $data, $referer='',$headers=null, $timeout=5)
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_REFERER, $referer);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
	
		if($headers){
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}
	/**
	 * 服务器端发起http 请求  post 返回带header
	 * @param unknown $url
	 * @param unknown $data
	 * @param string $headers
	 * @param number $timeout
	 * @return mixed
	 */
	public static function http_post_with_client($url, $data, $referer='',$headers=null, $cookies='',$agent='',$timeout=5)
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, $agent);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_REFERER, $referer);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
	
		if($headers){
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}
		if($cookies){
			curl_setopt($ch, CURLOPT_COOKIE, $cookies);
		}
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}
	
}
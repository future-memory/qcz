<?php

/*
* @author: 4061470@qq.com
*/

class HelperAuth
{
	 public static function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) 
	 {
		$len = 4;
		$key         = md5($key ? $key : self::get_authkey());
		$keya        = md5(substr($key, 0, 16));
		$keyb        = md5(substr($key, 16, 16));
		$keyc        = $len ? ($operation == 'DECODE' ? substr($string, 0, $len): substr(md5(microtime()), -$len)) : '';

		$cryptkey = $keya.md5($keya.$keyc);
		$key_length = strlen($cryptkey);

		$string = $operation == 'DECODE' ? base64_decode(substr($string, $len)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
		$string_length = strlen($string);

		$result = '';
		$box = range(0, 255);

		$rndkey = array();
		for($i = 0; $i <= 255; $i++) {
			$rndkey[$i] = ord($cryptkey[$i % $key_length]);
		}

		for($j = $i = 0; $i < 256; $i++) {
			$j = ($j + $box[$i] + $rndkey[$i]) % 256;
			$tmp = $box[$i];
			$box[$i] = $box[$j];
			$box[$j] = $tmp;
		}

		for($a = $j = $i = 0; $i < $string_length; $i++) {
			$a = ($a + 1) % 256;
			$j = ($j + $box[$a]) % 256;
			$tmp = $box[$a];
			$box[$a] = $box[$j];
			$box[$j] = $tmp;
			$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
		}

		if($operation == 'DECODE') {
			if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
				return substr($result, 26);
			} else {
				return '';
			}
		} else {
			return $keyc.str_replace('=', '', base64_encode($result));
		}
	}

	public static function get_authkey($ext='')
	{
		$saltkey = self::get_saltkey();
		$authkey = md5(AUTH_KEY.$saltkey.$ext);
		return $authkey;	
	}

	public static function get_saltkey()
	{
		$saltkey = HelperCookie::get('saltkey');
		if(!$saltkey){
			$saltkey = self::random(8);
			 HelperCookie::set('saltkey', $saltkey, 2592000, 1);
		}

		return $saltkey;		
	}

	public static function random($length, $numeric = 0) 
	{
		$seed = base_convert(md5(microtime().$_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);
		$seed = $numeric ? (str_replace('0', '', $seed).'012340567890') : ($seed.'zZ'.strtoupper($seed));
		if($numeric) {
			$hash = '';
		} else {
			$hash = chr(rand(1, 26) + rand(0, 1) * 32 + 64);
			$length--;
		}
		$max = strlen($seed) - 1;
		for($i = 0; $i < $length; $i++) {
			$hash .= $seed{mt_rand(0, $max)};
		}
		return $hash;
	}
	/**
	 * pkcs转pem
	 * @param unknown $der
	 * @return string
	 */
	public static  function pkcs8_to_pem($der)	{
		static $BEGIN_MARKER = "-----BEGIN PRIVATE KEY-----";
		static $END_MARKER   = "-----END PRIVATE KEY-----";
	
		$value   = ($der);
		//$value = base64_encode($der);
		$pem     = $BEGIN_MARKER . "\n";
		$pem     .= chunk_split($value, 64, "\n");
		$pem     .= $END_MARKER . "\n";
	
		return $pem;
	}
	/**
	 * 私钥生成签名
	 * @param string $content
	 * @param string $key
	 * @return string|boolean
	 */
	public  static function pri_sign($content,$key)	{
		if(!empty($key) && !empty($content)){
			$key = self::pkcs8_to_pem($key);
			$key = openssl_pkey_get_private($key);
	
			$res = '';
			openssl_sign($content, $res, $key);
	
			if (!empty($res)) {
				return rawurlencode(base64_encode($res));
			}
		}
		return false;
	}
	/**
	 * 公钥生成签名
	 * @param unknown $content
	 * @return string|boolean
	 */
	public static function pub_sign($content,$key){
		if(!empty($key) && !empty($content)){
			$key = $this->pkcs8_to_pem($key);
			$key = openssl_get_publickey($key);
	
			$res = '';
			openssl_public_encrypt($content, $res, $key);
	
			if (!empty($res)) {
				openssl_free_key($key);
				return rawurlencode(base64_encode($res));
			}
		}
		return false;
	}

}
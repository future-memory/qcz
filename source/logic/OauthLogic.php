<?php
class OauthLogic extends Logic
{
	public function get_weixin_auth_url($callback=null)
	{
		$config = HelperConfig::get_config('global::weixin');
		$callback = $callback ? $callback : $config['callback_url'];

		$url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$config['appid'].'&redirect_uri='.urlencode($callback).'&response_type=code&scope=snsapi_userinfo&state=1#wechat_redirect';

		return $url;
	}

	public function get_weixin_token($code)
	{
		$config = HelperConfig::get_config('global::weixin');
		$url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$config['appid'].'&secret='.$config['secret'].'&code='.$code.'&grant_type=authorization_code';
		$res  = HelperUtils::https_get($url);
		$json = json_decode($res, true);
		if(!$json){
			HelperLog::writelog('oauth_weixin', $config['appid'].':'.$config['secret'].var_export($res, true));
			return false;
		}

		return $json;
	}

	public function get_weixin_user($access_token, $openid)
	{
		$config = HelperConfig::get_config('global::weixin');

		$url    = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';
		
		$res    = HelperUtils::https_get($url);
		$json   = json_decode($res, true);

		return $json;
	}

	//weibo 
	public function get_weibo_auth_url($callback=null)
	{
		$config = HelperConfig::get_config('global::weibo');

		include_once(BASE_ROOT . '/source/sdk/saetv2.ex.class.php');
		$callback = $callback ? $callback : $config['callback_url'];

		$obj = new SaeTOAuthV2($config['appid'], $config['secret']);
		$url = $obj->getAuthorizeURL($callback);

		return $url;
	}

	public function get_weibo_token($code)
	{
		$config = HelperConfig::get_config('global::weibo');

		include_once(BASE_ROOT . '/source/sdk/saetv2.ex.class.php');

		$obj  = new SaeTOAuthV2($config['appid'], $config['secret']);
		$keys = array('code'=>$code, 'redirect_uri'=>$config['callback_url']);
		$res  = $obj->getAccessToken('code', $keys);

		return $res;
	}

	public function get_weibo_user($uid, $token)
	{
		$config = HelperConfig::get_config('global::weibo');
		include_once(BASE_ROOT . '/source/sdk/saetv2.ex.class.php');

		$obj = new SaeTClientV2($config['appid'], $config['secret'], $token);
		$res = $obj->show_user_by_id($uid);

		return $res;
	}

	public function get_weapp_token($code)
	{
		$cache_token = ObjectCreater::create('MemberDao')->get_session_token_cache($code);
		if($cache_token){
			HelperLog::writelog('weapp_token', 'cache_token:'.var_export($cache_token, true));
			return $cache_token;
		}

		$config = HelperConfig::get_config('global::weapp');

		$url = 'https://api.weixin.qq.com/sns/jscode2session?appid='.$config['appid'].'&secret='.$config['secret'].'&js_code='.$code.'&grant_type=authorization_code';
		$res  = HelperUtils::https_post($url);
		$json = json_decode($res, true);
		if(!$json || !$json['openid']){
			HelperLog::writelog('weapp_token', var_export($res, true));
			return false;
		}

		ObjectCreater::create('MemberDao')->set_weapp_session_cache($code, $json);

		return $json;
	}

	//获取微信用户信息
	public function get_weapp_user_info($session_key, $iv, $encryptedData)
	{
		include_once BASE_ROOT."/source/sdk/weapp/wxBizDataCrypt.php";
		$config = HelperConfig::get_config('global::weapp');

		$data = null;
		$pc   = new WXBizDataCrypt($config['appid'], $session_key);
		$errCode = $pc->decryptData($encryptedData, $iv, $data);

		if ($errCode == 0) {
		    return json_decode($data, true);
		}

		//保存错误码
		HelperLog::writelog('weapp_token', var_export($errCode, true));
	}


	//bbs-uc
	public function get_bbsuc_auth_url($callback=null, $referer=null)
	{
        $config = HelperConfig::get_config('global::bbsuc');
		$callback = urlencode($callback ? $callback : $config['callback_url']);

        $referer  = $referer ? $referer : HelperBiz::get_referer();
        $referer  = urlencode($referer ? $referer : DOMAIN);

        $login_url = SSO_DOMAIN.'index.php?mod=sso&action=login&useruri='.$callback.'&referer='.$referer;

        return $login_url;		
	}

	//微信js
	public function get_wx_js_conf($url)
	{
		$token = $this->get_weixin_js_token();
		$ticket = $this->get_weixin_js_ticket($token);

		$noncestr = HelperAuth::random(18);

		$str = 'jsapi_ticket='.$ticket.'&noncestr='.$noncestr.'&timestamp='.TIMESTAMP.'&url='.$url;
		$sig = sha1($str);

		return array(
			'noncestr'  => $noncestr,
			'timestamp' => TIMESTAMP,
			'signature' => $sig
		);
	}

	//微博js
	public function get_wb_js_conf($url)
	{
		$ticket = $this->get_weibo_js_ticket();
		$config = HelperConfig::get_config('global::weibo');
		$noncestr = HelperAuth::random(18);

		$str = 'jsapi_ticket='.$ticket.'&noncestr='.$noncestr.'&timestamp='.TIMESTAMP.'&url='.$url;
		$sig = sha1($str);

		return array(
			'appid'     => $config['appid'],
			'noncestr'  => $noncestr,
			'timestamp' => TIMESTAMP,
			'signature' => $sig
		);
	}

	//获取token
	public function get_weixin_js_ticket()
	{
		$config = HelperConfig::get_config('global::weixin');

		$cache_ticket = ObjectCreater::create('MemberDao')->get_js_ticket_cache($config['appid']);
		if($cache_ticket){
			return $cache_ticket;
		}

		$url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token='.$token;
		$res  = HelperUtils::https_get($url);
		$json = json_decode($res, true);
		if(!$json || !$json['ticket']){
			HelperLog::writelog('oauth_weixin_js', $token.var_export($res, true));
			return false;
		}

		ObjectCreater::create('MemberDao')->set_js_ticket_cache($config['appid'], $json['ticket'], $json['expires_in']);

		return $json['ticket'];
	}


	//获取token
	public function get_weixin_js_token()
	{
		$config = HelperConfig::get_config('global::weixin');

		$cache_token = ObjectCreater::create('MemberDao')->get_js_token_cache($config['appid']);
		if($cache_token){
			return $cache_token;
		}

		$url = 'https://api.weixin.qq.com/cgi-bin/token?appid='.$config['appid'].'&secret='.$config['secret'].'&grant_type=client_credential';
		$res  = HelperUtils::https_get($url);
		$json = json_decode($res, true);
		if(!$json || !$json['access_token']){
			HelperLog::writelog('oauth_weixin_js', $config['appid'].':'.$config['secret'].var_export($res, true));
			return false;
		}

		ObjectCreater::create('MemberDao')->set_js_token_cache($config['appid'], $json['access_token'], $json['expires_in']);

		return $json['access_token'];
	}

	//获取微博js ticket
	public function get_weibo_js_ticket()
	{
		$config = HelperConfig::get_config('global::weibo');

		$cache_ticket = ObjectCreater::create('MemberDao')->get_js_ticket_cache($config['appid']);
		if($cache_ticket){
			return $cache_ticket;
		}

		$url = 'https://api.weibo.com/oauth2/js_ticket/generate?client_id='.$config['appid'].'&client_secret='.$config['secret'];
		$res  = HelperUtils::https_post($url);
		$json = json_decode($res, true);
		if(!$json || !$json['js_ticket']){
			HelperLog::writelog('oauth_weibo_js', var_export($res, true));
			return false;
		}

		ObjectCreater::create('MemberDao')->set_js_ticket_cache($config['appid'], $json['js_ticket'], $json['expire_time']);

		return $json['ticket'];
	}

}
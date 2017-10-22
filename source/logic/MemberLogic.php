<?php
/**
 * @property MemberDao $_dao
 * @property LockLogic $lock
 */

require_once BASE_ROOT.'/config/sso.php';

class MemberLogic extends Logic
{
	public $cur_member   = null;
	private $authkey     = '@uth4$tory';
	private $bbs_authkey = 'M@eiz*u2?0>17';
	public $source = array(1=>'bbs', 2=>'weixin', 3=>'weibo');

	public function __construct()
	{
		$this->_dao = ObjectCreater::create('MemberDao');
	}	

	//获取当前用户
	public function get_current_member()
	{
		if($this->cur_member===null){
			$this->init_member();
		}
		return $this->cur_member;
	}

	//获取登录用户
	public function init_member()
	{
		$this->cur_member = array('id'=>0, 'uid'=>0);
		$token = HelperCookie::get('auth');
		if($token){
			$mid  = HelperAuth::authcode($token, 'DECODE', $this->authkey);
			$user = $mid ? $this->_dao->fetch($mid) : array();

			if(!empty($user)) {
				$this->cur_member = array(
					'id'       => $user['id'],
					'uid'      => $user['id'],
					'avatar'   => $user['avatar'],
					'username' => $user['username'],
					'clientip' => HelperUtils::getClientIP(),
				);
			}
		}else{
			$this->init_member_by_bbstoken();
		}
	}


	//使用bbstoken 获取登录用户
	public function init_member_by_bbstoken()
	{
		$token_orgi = isset($_SERVER['HTTP_BBSTOKEN']) ? $_SERVER['HTTP_BBSTOKEN'] : HelperUtils::get_param('bbstoken');
		$token = rawurldecode($token_orgi);
		if($token){
			$auth = explode("\t", HelperAuth::authcode($token, 'DECODE', $this->bbs_authkey));
			list($discuz_pw, $discuz_uid) = empty($auth) || count($auth) < 2 ? array('', '') : $auth;
			if($discuz_uid){
				$info = $this->get_logined_member_by_token($token_orgi);
				if($info && $info['code']==200){
			    	$data = $this->get_member_by_uid($info['data']['uid'], 1);
			    	$mid  = isset($data['id']) ? $data['id'] : 0;
			    	if(!$data){
			        	$data = array(
							'source'   => 1,
							'uid'      => $info['data']['uid'],
							'username' => $info['data']['username'],
							'avatar'   => HelperUtils::avatar($info['data']['uid'])
			        	);
			        	$mid = ObjectCreater::create('MemberLogic')->insert_member($data);
			    	}

			    	$mid && ObjectCreater::create('MemberLogic')->set_member_logined($mid);

					$this->cur_member = array(
						'id'       => $data['id'],
						'uid'      => $data['uid'],
						'avatar'   => $data['avatar'],
						'username' => $data['username'],
					);
				}
			}
		}	
	}

	//登录
	public function set_member_logined($mid)
	{
		HelperCookie::set('auth', HelperAuth::authcode("{$mid}", 'ENCODE', $this->authkey), 0, true);
	}

	//ticket获取当前登录用户信息
	public function get_logined_member_by_ticket($ticket, $all_userinfo = '')
	{
		if(!$ticket){
			return null;
		}
		$tosign = 'ticket='.$ticket.'&key='.TICKET_SIGN_KEY;
		$sign   = base64_encode(hash_hmac("sha1", $tosign, TICKET_SIGN_KEY, true));

		$url    = SSO_DOMAIN.'index.php?mod=sso&action=get_login_user&ticket='.urlencode($ticket).'&sign='.urlencode($sign);
		if ($all_userinfo === 'all_userinfo') {
			$url .= '&all_userinfo=1';
		}
		$json   = HelperUtils::https_get($url);
		$result = @json_decode($json, true);

	    return $result;
	}

	//保存用户
	public function insert_member($data)
	{
		$this->_dao->delete_member_cache($data['uid'], $data['source']);
		return $this->_dao->insert($data, true);
	}

	//调整授权
	public function gologin($referer = null)
	{
		$url = '';
		$source = $this->get_member_source();
	
		//保存返回的地址
		$referer = HelperUtils::check_url($referer) ? $referer : DOMAIN.ltrim($referer, '/');

		if($source==2){
			$callback = DOMAIN.'index.php?mod=story&action=wxcallback';
			$url = ObjectCreater::create('OauthLogic')->get_weixin_auth_url($callback);
			HelperCookie::set('oauth_referer', urlencode($referer));
		}else if($source==3){
			$callback = DOMAIN.'index.php?mod=oauth&action=wbcallback';
			$url = ObjectCreater::create('OauthLogic')->get_weibo_auth_url($callback);
			HelperCookie::set('oauth_referer', urlencode($referer));
		}else{
	        $callback = DOMAIN.'index.php?mod=oauth&action=bbsuccallback';
	        //$referer  = DOMAIN.'index.php?mod=oauth&action=share';
			$url = ObjectCreater::create('OauthLogic')->get_bbsuc_auth_url($callback, $referer);
		}
		header('location: '.$url);
	}

	public function get_member_source()
	{
		$user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
		$source = 1;
		if(strpos($user_agent, 'micromessenger')){
			$source = 2;
		}else if(strpos($user_agent, 'weibo')){
			$source = 3;
		}
		return $source;
	}



}
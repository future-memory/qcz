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
		
		$token = isset($_SERVER['HTTP_AUTHTOKEN']) ? $_SERVER['HTTP_AUTHTOKEN'] : HelperCookie::get('auth');
		$token = rawurldecode($token);
		if($token){
			$mid  = HelperAuth::authcode($token, 'DECODE', $this->authkey);
			$info = $this->_dao->get_member_token_info($mid);
			$tmp  = rawurlencode($token);
			$user = $mid && !empty($info) && $info['token']==$tmp ? $this->_dao->fetch($mid) : array();
			if(!empty($user)) {
				$this->cur_member = array(
					'id'       => $user['id'],
					'uid'      => $user['id'],
					'avatar'   => $user['avatar'],
					'username' => $user['username'],
					'clientip' => HelperUtils::getClientIP(),
				);
			}
		}
	}

	//登录
	public function set_member_logined($mid, $expire=86400, $setcookie=false)
	{
		$token = HelperAuth::authcode("{$mid}", 'ENCODE', $this->authkey);
		$token = rawurlencode($token);
		$data  = array(
			'token'  => $token, 
			'ip'     => HelperUtils::getClientIP()
        );

		$this->_dao->set_member_token_info($mid, $data, false, $expire);

		$setcookie && HelperCookie::set('auth', $token, 0, true);

		return $token;
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

	//保存用户
	public function update_member($mid, $data)
	{
		$this->_dao->delete_member_cache($data['uid'], $data['source']);
		return $this->_dao->update($mid, $data);
	}

	//获取用户来源
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

	//调整授权
	public function gologin($referer = null)
	{
		$url = '';
		$source = $this->get_member_source();
	
		//保存返回的地址
		$referer = HelperUtils::check_url($referer) ? $referer : DOMAIN.ltrim($referer, '/');

		if($source==2){
			$callback = DOMAIN.'index.php?mod=oauth&action=wxcallback';
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


	private function update_credit($uid, $credit, $op, $id=0) 
	{
		$res = $this->_dao->update_credit($uid, $credit);
		if($res){
			$credit_log = array(
				'uid'       => $uid,
				'operation' => $op,
				'relatedid' => $id,
				'credit'    => $credit,
				'dateline'  => TIMESTAMP,
			);
			return ObjectCreater::create('CreditLogDao')->insert_log($credit_log);
		}
		return false;
	}



}
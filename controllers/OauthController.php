<?php

class OauthController extends BaseController 
{
	private $_src_arr = array(
		'fm' => 1,
		'wx' => 2,
		'wb' => 3,
		'wa' => 4
	);

	public function __construct()
	{
		$this->logic = ObjectCreater::create('OauthLogic');
	}

	//bbs-uc 登录后回调
	public function bbsuccallback()
	{
        $ticket  = $this->get_param('ticket');
        $referer = $this->get_param('referer');

        $logic  = ObjectCreater::create('MemberLogic');
        $member = $logic->get_logined_member_by_ticket($ticket);

        if(!$member || !isset($member['code']) || $member['code'] != 200){
        	HelperLog::writelog('bbsauth', var_export($member, true));
        	die('登录失败，请稍候再试！');
        }

    	$data = $logic->get_member_by_uid($member['data']['uid'], $this->_src_arr['fm']);
    	$mid  = $data['id'];
    	if(!$data){
        	$data = array(
				'source'   => $this->_src_arr['fm'],
				'uid'      => $member['data']['uid'],
				'username' => $member['data']['username'],
				'avatar'   => HelperUtils::avatar($member['data']['uid'])
        	);
        	$mid = ObjectCreater::create('MemberLogic')->insert_member($data);
    	}

    	ObjectCreater::create('MemberLogic')->set_member_logined($mid);
        
        header('location: '.$referer);
	}

	//微信授权回调
	public function wxcallback()
	{
		$code = $this->get_param('code');

		try {
			$tmp = ObjectCreater::create('OauthLogic')->get_weixin_token($code);
			$tmp = ObjectCreater::create('OauthLogic')->get_weixin_user($tmp['access_token'], $tmp['openid']);
		} catch (Exception $e) {
			HelperLog::writelog('wxauth', var_export($e, true));
			die('微信授权失败，请稍候再试！');
		}

        if(!$tmp || !isset($tmp['openid']) || !$tmp['openid']){
        	HelperLog::writelog('wxauth', var_export($tmp, true));
        	die('微信授权失败，请稍候再试！');
        }

    	$data = ObjectCreater::create('MemberLogic')->get_member_by_uid($tmp['openid'], $this->_src_arr['wx']);
    	$mid  = isset($data['id']) ? $data['id'] : null;
    	if(!$data){
        	$data = array(
				'source'   => $this->_src_arr['wx'],
				'uid'      => $tmp['openid'],
				'username' => $tmp['nickname'],
				'avatar'   => str_replace('http://', 'https://', $tmp['headimgurl'])
        	);
        	$mid = ObjectCreater::create('MemberLogic')->insert_member($data);
    	}

    	ObjectCreater::create('MemberLogic')->set_member_logined($mid);

    	$referer = urldecode(HelperCookie::get('oauth_referer'));
    	HelperCookie::set('oauth_referer', '' , -1);

    	header('location: '.$referer);
	}

	//微博授权回调
	public function wbcallback()
	{
		$code = $this->get_param('code');

		try {
			$tmp = $this->logic->get_weibo_token($code);
			$tmp = $this->logic->get_weibo_user($tmp['uid'], $tmp['access_token']);
		} catch (Exception $e) {
			HelperLog::writelog('wbauth', var_export($e, true));
			die('微博授权失败，请稍候再试！');
		}

        if(!$tmp || !isset($tmp['id']) || !$tmp['id']){
        	HelperLog::writelog('wbauth', var_export($tmp, true));
        	die('微博授权失败，请稍候再试！');
        }

    	$data = ObjectCreater::create('MemberLogic')->get_member_by_uid($tmp['id'], $this->_src_arr['wb']);
    	$mid  = $data['id'];
    	if(!$data){
        	$data = array(
				'source'   => $this->_src_arr['wb'],
				'uid'      => $tmp['id'],
				'username' => $tmp['name'],
				'avatar'   => str_replace('http://', 'https://', $tmp['profile_image_url'])
        	);
        	$mid = ObjectCreater::create('MemberLogic')->insert_member($data);
    	}

    	ObjectCreater::create('MemberLogic')->set_member_logined($mid);

    	$referer = urldecode(HelperCookie::get('oauth_referer'));
    	HelperCookie::set('oauth_referer', '' , -1);

    	header('location: '.$referer);
	}

	public function weapp_login()
	{
		$code = $this->get_param('code');

		$res = $this->logic->get_weapp_token($code);
		$this->throw_error(!$res || !isset($res['openid']), array('code'=>500, 'message'=>'登录失败'));

		$data = ObjectCreater::create('MemberLogic')->get_member_by_uid($res['openid'], $this->_src_arr['wa']);
    	$mid  = isset($data['id']) ? intval($data['id']) : 0;
    	if(!$mid){
        	$tmp = array(
				'source' => $this->_src_arr['wa'],
				'uid'    => $res['openid'],
        	);
        	ObjectCreater::create('MemberLogic')->insert_member($tmp);
    	} 	

    	$this->render_json(array('code'=>200, 'message'=>'ok'));
	}

	//todo
	public function weapp_reg()
	{
		$iv = $this->get_param('iv');
		$code = $this->get_param('code');
		$encryptedData = $this->get_param('encryptedData');

		$this->throw_error(!$iv || !$code || !$encryptedData, array('code'=>400, 'message'=>'参数错误'));

		$res = $this->logic->get_weapp_token($code);
		$this->throw_error(!$res || !isset($res['openid']), array('code'=>500, 'message'=>'登录失败'));

		$data = ObjectCreater::create('MemberLogic')->get_member_by_uid($res['openid'], $this->_src_arr['wa']);
		$mid  = $data['id'];

		$user = $this->logic->get_weapp_user_info($res['session_key'], $iv, $encryptedData);
		$this->throw_error(!$user || !isset($user['openId']) || $user['openId']!=$res['openid'], array('code'=>502, 'message'=>'登录失败'));

		$tmp = array(
			'source'   => $this->_src_arr['wa'],
			'username' => $user['nickName'],
			'avatar'   => $user['avatarUrl']
    	);
    	ObjectCreater::create('MemberLogic')->update_member($mid, $tmp);

    	$expire = 86400; 
    	$token  = ObjectCreater::create('MemberLogic')->set_member_logined($mid, $expire);

    	$this->render_json(array('code'=>200, 'data'=>array('token'=>$token, 'expireAt'=>$expire+TIMESTAMP)));
	}

	//获取微信js签名
	public function get_wb_js_conf()
	{
		$url = $this->get_param('url');
		$cb  = strip_tags($this->get_param('callback'));

		$data = $this->logic->get_wb_js_conf($url);

		$this->render_json(array('code'=>200, 'data'=>$data, 'message'=>'ok'), false, $cb);
	}

	//获取微信js签名
	public function get_wx_js_conf()
	{
		$url = $this->get_param('url');
		$cb  = strip_tags($this->get_param('callback'));

		$data = $this->logic->get_wx_js_conf($url);

		$this->render_json(array('code'=>200, 'data'=>$data, 'message'=>'ok'), false, $cb);
	}


}
<?php

class OauthController extends BaseController 
{

	public function __construct()
	{
		$this->logic = ObjectCreater::create('OauthLogic');
	}

	//bbs-uc 登录后回调
	public function bbsuccallback()
	{
        $ticket  = $this->get_param('ticket');
        $referer = $this->get_param('referer');

        $member = ObjectCreater::create('MemberLogic')->get_logined_member_by_ticket($ticket);

        if(!$member || !isset($member['code']) || $member['code'] != 200){
        	HelperLog::writelog('bbsauth', var_export($member, true));
        	die('登录失败，请稍候再试！');
        }

    	$data = ObjectCreater::create('MemberLogic')->get_member_by_uid($member['data']['uid'], 1);
    	$mid  = $data['id'];
    	if(!$data){
        	$data = array(
				'source'   => 1,
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

    	$data = ObjectCreater::create('MemberLogic')->get_member_by_uid($tmp['openid'], 2);
    	$mid  = $data['id'];
    	if(!$data){
        	$data = array(
				'source'   => 2,
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
			$tmp = ObjectCreater::create('OauthLogic')->get_weibo_token($code);
			$tmp = ObjectCreater::create('OauthLogic')->get_weibo_user($tmp['uid'], $tmp['access_token']);
		} catch (Exception $e) {
			HelperLog::writelog('wbauth', var_export($e, true));
			die('微博授权失败，请稍候再试！');
		}

        if(!$tmp || !isset($tmp['id']) || !$tmp['id']){
        	HelperLog::writelog('wbauth', var_export($tmp, true));
        	die('微博授权失败，请稍候再试！');
        }

    	$data = ObjectCreater::create('MemberLogic')->get_member_by_uid($tmp['id'], 3);
    	$mid  = $data['id'];
    	if(!$data){
        	$data = array(
				'source'   => 3,
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
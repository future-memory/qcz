<?php
/**
 * @property MemberDao $_dao
 *
 */

class AdminLogic extends Logic
{
	/*
	 * 登录过期时间
	 */
	private $login_ttl = 86400;
	public $cur_member = null;

	public function __construct()
	{
		$this->_dao = ObjectCreater::create('AdminMemberDao');
	}

	//获取当前domain
	public function get_current_domain()
	{
        $member = $this->get_current_member();
        $domain = $member['domain'];
        $cookie = HelperCookie::get('current_domain');
        $domain = $domain=='www' && $cookie ? $cookie : $domain;

        return $domain;
	}

	public function get_current_member()
	{
		if($this->cur_member===null){
			$this->init_cur_member_by_token();
		}

		return $this->cur_member;
	}

	public function init_cur_member_by_token()
	{
		$this->cur_member = array();
		$token = HelperCookie::get('admin_auth');
		$token = rawurldecode($token);
		if($token){
			$key  = $this->get_auth_key();
			$data = explode("\t", HelperAuth::authcode($token, 'DECODE', $key));	

			list($pwd, $uid) = empty($data) || count($data) < 2 ? array('', '') : $data;

			if($uid){
				$token_info = $this->_dao->get_admin_token($uid);
				if(!empty($token_info) && $token_info['token']==rawurlencode($token)){
					//没过期才初始化用户
					$user = $this->_dao->fetch($uid, true);
					if(!empty($user) && $user['password'] == $pwd) {
						$this->cur_member = $user;
					}
				}
			}
		}

		//游客
		$this->cur_member['uid']      = isset($this->cur_member['uid']) ? $this->cur_member['uid'] : 0;
		$this->cur_member['role_id']  = isset($this->cur_member['role_id']) ? $this->cur_member['role_id'] : 0;
		$this->cur_member['clientip'] = HelperUtils::getClientIP(); 
	}


	public function get_auth_key($depend_agent=true)
	{
		$authkey = AUTH_KEY;
		$saltkey = isset($_SERVER['HTTP_USER_AGENT']) && $depend_agent ? md5($_SERVER['HTTP_USER_AGENT']) : '';
		return md5($authkey.$saltkey);
	}

	//生成admin校验token，并保存到cookie中
	public function set_admin_logined($member, $expire=1800)
	{
		$member = isset($member['data']) ? $member['data'] : $member;
		$key    = $this->get_auth_key();
		$uid    = isset($member['uid']) ? $member['uid'] : '';
		$pwd    = isset($member['password']) ? $member['password'] : null;
		$token  = HelperAuth::authcode("{$pwd}\t{$uid}\t".TIMESTAMP, 'ENCODE', $key, $expire);
		$token  = rawurlencode($token);
		
		$res    = $this->_dao->set_admin_token($member['uid'], array('token' => $token), $expire);

		HelperCookie::set('admin_auth', $token, $expire, true);

		return $token;
	}

	public function get_admin_online() {
		$onlines = ObjectCreater::create('CommonAdmincpSessionDao')->fetch_all_by_panel(1);

		$current_onlines = array();
		$tokens = $this->_dao->get_all_admin_token();
		foreach ($tokens as $key => $value) {
			$uid = explode('_', $value)[2];
			$current_onlines[$uid] = $this->_dao->get_admin_token($uid);
		}
		$onlines = array_unique(array_merge(array_keys($onlines), array_keys($current_onlines)));

		return ObjectCreater::create('MemberDao')->fetch_all($onlines, false);;
	}

	public function check_founder($user) 
	{
		$founders = HelperConfig::get_config('global::founders');
		$founders = str_replace(' ', '', $founders);
		if(!$user['uid']) {
			return false;
		} elseif(empty($founders)) {
			return true;
		} elseif(strpos(",$founders,", ",$user[username],")!==false) {
			return true;
		} else {
			return false;
		}
	}

	public function check_admin_login()
	{
		$action = Nice::app()->getComponent('Request')->getParam('action');
		$wants_json = Nice::app()->getComponent('Request')->getParam('wants_json');
		$skip_actions = HelperConfig::get_config('global::skip_actions');

		if(in_array($action, $skip_actions)){
			return true;
		}

    	$member = $this->get_current_member();

    	if(!$member['uid']){
    		if ($wants_json) {
    			echo json_encode(array(
    				'code' => 402,
    				'message' => 'not login',
    				'returl' => 'index.php?action=login',
    				'hint' => '登陆'
    				));
    			exit;
    		}
    		header('Location: index.php?action=login');
    		exit();
    	}
    	$founder = $this->check_founder($member);
    	if($founder){
    		return true;
    	}

    	//刷新
    	$this->_dao->refresh_admin_token($member['uid']);
    	
    	return true;
	}

	//检查操作是否有权限
	public function check_access() 
	{
		$action = Nice::app()->getComponent('Request')->getParam('action');
		$wants_json = Nice::app()->getComponent('Request')->getParam('wants_json');
		$skip_actions = HelperConfig::get_config('global::skip_actions');

		if(in_array($action, $skip_actions)){
			return true;
		}

		if (Nice::app()->getController() === 'index') {
			return true;
		}

    	$member = $this->get_current_member();
    	if(!$member['uid']){
    		if ($wants_json) {
    			echo json_encode(array(
    				'code' => 402,
    				'message' => 'not login',
    				'returl' => 'index.php?action=login',
    				'hint' => '登陆'
    				));
    			exit;
    		}
    		header('Location: index.php?action=login');
    	}

    	$founder = $this->check_founder($member);
    	if($founder){
    		return true;
    	}

		$admin   = $this->_dao->fetch($member['uid']);
		$perms   = $this->load_admin_perms($admin);
		$checked = $this->check_perm($perms);

		if($checked === false){
			echo json_encode(array(
				'code' => 502,
				'message' => '无权限进行此操作'
				));
			exit;
    		// header('Location: /index.php?action=login');
    	}
	}

	//同上 实际的检查
	public function check_perm($perms=null) 
	{
		if(isset($perms['all'])) {

			$member = $this->get_current_member();
			if (!empty($member)) {
				$role_id = $member['role_id'];
			}

			if (!$this->check_founder($member)) {
				$menu_list = ObjectCreater::create('MenuLogic')->get_perm_menu_list();
				$menu_mod = ObjectCreater::create('MenuLogic')->get_mod_list();
				if (!in_array(Nice::app()->getController(), $menu_mod)) {
					return false;
				}
			}
			
			return $perms['all'];
		}
		$mod_allow = array();
		foreach ($perms as $perms_k => $perms_v) {
			if ($perms_v == true) {
				$mod_allow[] = $perms_k;
			}
		}

		$is_post = Nice::app()->getComponent('Request')->isPostRequest();

		if($is_post && !in_array('_allowpost', $mod_allow)) {
			return false;
		}

		if(in_array(Nice::app()->getController(), $mod_allow)) {
			return true;
		}

		return false;
	}

	//加载权限
	public function load_admin_perms($admin) 
	{
		$perms = array();
		if($admin['role_id']) {
			$perm_list = ObjectCreater::create('AdminRolePermDao')->fetch_all_by_role($admin['role_id']);
			foreach($perm_list as $perm) {
				$perms[$perm['perm']] = true;
			}			
		} else {
			$perms['all'] = true;
		}
		return $perms;
	}

	//保存操作日志
	public function writelog() 
	{
		$member = $this->get_current_member();
		$domain = $this->get_current_domain();
		$domain = $domain ? $domain : 'www';
		if (!$member['uid'] || empty($member['username'])) {
			return;
		}

		$mod    = Nice::app()->getController();
		$action = Nice::app()->getAction();
		$extlog = $this->implodearray(array('GET' => $_GET, 'POST' => $_POST), array('formhash', 'submit', 'addsubmit', 'admin_password', 'sid', 'action'));

		HelperLog::writelog('cplog_'.$domain, implode("\t", $this->clearlogstring(array(TIMESTAMP, $member['username'], $member['role_id'], $member['clientip'], 'mod=' . $mod . '&action=' . $action, $extlog))));
	}

	//格式化日志内容
	public function clearlogstring($str) 
	{
		if(!empty($str)) {
			if(!is_array($str)) {
				$str = HelperUtils::dhtmlspecialchars(trim($str));
				$str = str_replace(array("\t", "\r\n", "\n", "   ", "  "), ' ', $str);
			} else {
				foreach($str as $key => $val) {
					$str[$key] = $this->clearlogstring($val);
				}
			}
		}
		return $str;
	}

	//拼接数组
	public function implodearray($array, $skip = array()) 
	{
		$return = '';
		if(is_array($array) && !empty($array)) {
			foreach ($array as $key => $value) {
				if(empty($skip) || !in_array($key, $skip, true)) {
					if(is_array($value)) {
						$return .= "$key={".$this->implodearray($value, $skip)."}; ";
					} elseif(!empty($value)) {
						$return .= "$key=$value; ";
					} else {
						$return .= '';
					}
				}
			}
		}
		return $return;
	}

   public function login($login_name, $passwd, $client_time, $is_keepalive = false, $vcode=null)//TODO, 
    {
        $login_name  = addslashes(trim($login_name));
        $passwd      = addslashes(trim($passwd));
        $times_key   = 'login_times_'.HelperUtils::getClientIP();
        $login_times = ObjectCreater::create('KVDataLogic')->get_val($times_key);

        BizException::throw_exception(LOGIN_TIMES_TO_FORBIDDEN < $login_times, array('code'=>403, 'message'=>'抱歉，你的帐号被锁定，请一个小时后再试！'));

        $need_vcode = $login_times+1 > LOGIN_TIMES_TO_VERIFY;
        $extra = $need_vcode ? array('need_vcode'=>1) : null;

        ObjectCreater::create('KVDataLogic')->set_data($times_key, $login_times+1);

        if(LOGIN_TIMES_TO_VERIFY < $login_times){
            $tcode = HelperCookie::get('vcode');
            BizException::throw_exception(strtolower($tcode)!=strtolower($vcode), array('code'=>403, 'message'=>'验证码错误！'), $extra);
        }

        $row = $this->_dao->get_member_by_username($login_name);
        $pwd = md5($passwd.PASS_SLAT);

		BizException::throw_exception(empty($row) || $row['password'] != $pwd, array('code'=>403, 'message'=>'登录失败！'), $extra);

		$expire = $is_keepalive ? 86400 * 30 : 50000; 
        $this->set_admin_logined($row, $expire);

        //成功之后清空login次数
        ObjectCreater::create('KVDataLogic')->delete($times_key);

    }

	public function clear_cookies()
	{
		$cookie_pre = HelperCookie::get_cookie_pre();
		$pre_length = strlen($cookie_pre);
		foreach($_COOKIE as $key => $val) {
			if(substr($key, 0, $pre_length) == $cookie_pre) {
				$name_key = substr($key, $pre_length);
				HelperCookie::set($name_key);
			}
		}
	}


	/**
	 * 退出登录
	 * @return boolean
	 */
	public function logout()
	{
		$config = Nice::app()->getProperty('uc_cookie');
		foreach ($_COOKIE as $key =>$cookie){
			if(substr($key, 0,strlen($config['pre'])) == $config['pre']){
				HelperCookie::set($key,'',0,false,true);
			}
		}
		$this->clear_cookies();
		$this->cur_member = null;
		return true;
	}
}
<?php
class LotteryController extends BaseController 
{
	private $id = 0;
	private $from;
	private $skin;
	private $log_data   = array();
	public $_my_limit   = 40;
	private $_member    = array();
	private $_shtml_dir = '';
	private $uc_cookiepre;

	public function __construct() 
	{
		$this->logic = ObjectCreater::create('LotteryLogic');
	}

	//促销抽奖
	public function promotion() 
	{
		$promotion  = ObjectCreater::create('LotteryPromotionLogic')->get_current_promotion();

		if(!$promotion){
			die('当前没有活动，谢谢关注');
		}

		$member = ObjectCreater::create('MemberLogic')->get_current_member();

		if(!$member || !$member['id']){
			ObjectCreater::create('MemberLogic')->gologin('/lottery/promotion');
		}		

		$lottery    = $this->logic->get_activity_info($promotion['lottery_id']);
		$award_list = $this->logic->get_award_list($promotion['lottery_id']);

		include(APP_ROOT . '/template/lottery/promotion.php');
	}

	public function check()
	{
		$pid  = (int)$this->get_param('pid', 0, true);
		$imei = $this->get_param('imei');

		$this->throw_error(!$imei || !$pid, array('code'=>400, 'message'=>'参数错误！'));

		ObjectCreater::create('LotteryPromotionLogic')->check_imei($pid, $imei);

		$this->render_json(self::$WEB_SUCCESS_RT);
	}


	public function drawing()
	{
		$result = self::$WEB_SUCCESS_RT;

    	$this->_check_referer();

		$pid = (int)$this->get_param('pid', 0, true);
		$id  = (int)$this->get_param('id', 0, true);

		$imei = $this->get_param('imei');

		$this->throw_error(!$imei || !$pid || !$id, array('code'=>400, 'message'=>'参数错误！'));

		$member = ObjectCreater::create('MemberLogic')->get_current_member();
		$this->throw_error(!$member['uid'], array('code'=>401, 'message'=>'请先登录！'));

		ObjectCreater::create('LotteryPromotionLogic')->check_imei($pid, $imei, true);

		$data = $this->logic->draw($id, false);

		$result['message'] = $data['message'];
		unset($data['code'], $data['message']);
		$result['data']    = $data;

        $this->render_json($result);		
	}

	//获取地址
	public function single_address() 
	{
		//判断是否登录
		$member = ObjectCreater::create('MemberLogic')->get_current_member();
		$this->throw_error(!$member['uid'], array('code'=>401, 'message'=>'请先登录！'));	

		$data = $this->logic->get_address_by_uid($member['uid']);
		$data['phone'] = $member['phone'];

		//兑换商城触发更新电话
		$phone = ObjectCreater::create('MemberLogic')->sync_phone();
		$phone && $data['phone'] = $phone;
		
		$data['id'] = $data['id'] ? $data['id'] : ($data['phone'] ? 1 : null);

		$this->render_json(array('code'=>200, 'data'=>$data, 'message'=>'sucess'));
	}

	//抽奖
	public function draw() 
	{
		$result = self::$WEB_SUCCESS_RT;

    	$this->_check_referer();

    	$member = ObjectCreater::create('MemberLogic')->get_current_member();
		$this->throw_error(!$member['uid'], array('code'=>401, 'message'=>'请先登录！'));

		$id   = (int)$this->get_param('id', 0, true);
		$data = $this->logic->draw($id);


		$result['message'] = $data['message'];
		unset($data['code'], $data['message']);
		$result['data']    = $data;

        $this->render_json($result);
	}

	//奖品列表
	public function award_list() {
		$this->_check_referer();

		$id = (int)$this->get_param('id', 0);
		
		$this->throw_error(!$id, array('code'=>400, 'message'=>'请求错误！'));

		$list = $this->logic->get_award_list($id);

		$this->render_json(array('code'=>200, 'data'=>array('list'=>$list)));		
	}

	//获奖列表
	public function win_list() {
		$this->_check_referer();

		$page  = (int)$this->get_param('page', 1);
		$limit = (int)$this->get_param('limit', 100);
		$id    = (int)$this->get_param('id', 0);

		$type = (string)$this->get_param('type', 0);

		// 过滤
		$type = trim(preg_replace('/[^,0-9]/', '', $type), ',');
		if (strpos($type, ',') !== false) {
		    $type = explode(',', $type);
		} else {
			$type = (int)$type;
		}
		if (is_array($type) && count($type) === 0) {
			$type = 0;
		}

		$callback = $this->get_param('callback', null);

		$this->throw_error(!$id, array('code'=>400, 'message'=>'请求错误！'), $callback);

		$start = $page > 1 ? ($page-1) * $limit : 0;
		$list = $this->logic->get_win_list($id, $start, $limit, $type);
		$json = array('code'=>200, 'data'=>array('list'=>$list,
				'user'=>ObjectCreater::create('MemberLogic')->get_current_member()
		));
		
		$callback ? $this->render_jsonp($json, $callback) : $this->render_json($json);
	}

	public function my_win() 
	{
		$id = (int)$this->get_param('id', 0);
		$this->throw_error(!$id, array('code'=>400, 'message'=>'请求错误！'));

		//判断是否登录
		$member = ObjectCreater::create('MemberLogic')->get_current_member();
		$this->throw_error(!$member['uid'], array('code'=>401, 'message'=>'请先登录！'));

		$list = $this->logic->get_win_list_by_activityid_uid($id);
		$list = array_values($list);

		$this->render_json(array('code'=>200, 'data'=>array('list'=>$list)));		
	}	

	public function get_count() {
		$id = (int)$this->get_param('id', 0);
		$this->throw_error(!$id, array('code'=>400, 'message'=>'请求错误！'));
		$count = $this->logic->get_lottery_count_by_activityid($id);

	    $this->render_json(array('code'=>200, 'data'=>array('count'=>$count)));	
	}


	//接口
	public function get_user_count() 
	{
		$id = (int)$this->get_param('id', 0);
		$this->throw_error(!$id, array('code'=>400, 'message'=>'请求错误！'));
		//判断是否登录
		$member = ObjectCreater::create('MemberLogic')->get_current_member();
		$this->throw_error(!$member['uid'], array('code'=>401, 'message'=>'请先登录！'));
		
		$chance = $this->logic->get_user_count($id);

		$data = array(
			'uid'    => $member['uid'],
			'chance' => $chance,
		);

		$this->render_json(array('code'=>200, 'data'=>$data));
	}

	public function update_address() {
		$page = (int)$this->get_param('page', 1);

		//判断是否登录
		$member = ObjectCreater::create('MemberLogic')->get_current_member();
		$this->throw_error(!$member['uid'], array('code'=>401, 'message'=>'请先登录！'));	
		
		$this->_check_referer();

		$win_id  = (int)$this->get_param('win_id', 0 , true);
		$name    = htmlspecialchars($this->get_param('name'));
		$phone   = htmlspecialchars($this->get_param('phone'));	
		$address = htmlspecialchars($this->get_param('address'));

		$this->throw_error(!$win_id || !$name || !$phone || !$address, array('code'=>400, 'message'=>'参数错误！'));
		$this->throw_error(mb_strlen($name)>60, array('code'=>401, 'message'=>'您输入的姓名超出长度限制！'));
		$this->throw_error(mb_strlen($address)>490, array('code'=>402, 'message'=>'您输入的地址超出长度限制！'));

		$result = $this->logic->update_address_by_winid($win_id, $name, $phone, $address, $page);

		$this->render_json(array('code'=>200, 'message'=>'sucess'));
	}

	public function send_code()
	{
		$win_id = (int)$this->get_param('win_id', 0);

		$goods_list = $this->get_param('goods_list');
		$goods_list = is_array($goods_list) ? $goods_list : @json_decode($goods_list, true);

		$this->throw_error(!$win_id && empty($goods_list), array('code'=>400, 'message'=>'参数错误！'));

		$this->logic->send_code($win_id, $goods_list);

		$this->render_json(array('code'=>200, 'message'=>'验证码发送至您帐号绑定的手机号，请注意查收'));
	}

	public function after_action($controller, $data=array()) {
		$this->logic->save_log($data);
	}


}
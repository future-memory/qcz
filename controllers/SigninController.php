<?php

class SigninController extends BaseController 
{
	public $cur_member          = array();
	private $_log_data          = array();
	protected $handle_exception = false;

	public function __construct()
	{
		$this->logic      = ObjectCreater::create('SigninLogic');
		$this->cur_member = ObjectCreater::create('MemberLogic')->get_current_member();
	}

	/**
	 * @api {get} /index.php?mod=signin&action=index 签到页面
	 * @apiName signinpage
	 * @apiGroup Signin
	 * @apiVersion 2.0.0
	 * @apiDescription 显示对话界面
	 * @apiPermission Logined
	 * 
	*/
	public function index()
	{
		$member    = $this->cur_member;
		$last_days = 0;
		$avatar    = $this->cur_member['avatar'];
		if($member['uid']){
			$signed    = $this->logic->fetch($member['uid']);
			$last_days = isset($signed['cons']) ? $signed['cons'] : 0;
		}

		include(APP_ROOT . '/template/signin/index.php');
	}

	/**
	 * @api {get} /index.php?mod=signin&action=check 检查是否已签到
	 * @apiName signcheck
	 * @apiGroup Signin
	 * @apiVersion 2.0.0
	 * @apiDescription 检查当前用户是否已签到
	 * @apiPermission Logined
	 * @apiSuccess (response) {Number} code success 200
	 * @apiSuccess (response) {json} data 详细数据
	 * @apiSuccessExample {json} Response 200
	 *
	 *{
	 * "code": 200,
	 * "data": {
	 *  "signed": 0, //是否已签到 1已签到 0否
	 *  "last_days": "2" //  连续签到天数
	 * }
	 *}
	 * 
	*/
	public function check()
	{
		$this->throw_error(!$this->cur_member['uid'], array('code'=>401, 'message'=>'请先登录！'));
		
		$check  = 0;
		$signed = $this->logic->fetch($this->cur_member['uid']);

		if(!empty($signed)){
		    if(date('Ymd') == date('Ymd', $signed['lasttime'])){
		        $check = 1;
		    }
		}

		$data = array(
			'signed'    => $check,
			'avatar'    => $this->cur_member['avatar'],
			'last_days' =>isset($signed['cons']) ? $signed['cons'] : 0
		);

		$this->render_json(array('code'=>200, 'data'=>$data));
	}

	//检查是否已签到
	public function tips()
	{
		$this->throw_error(!$this->cur_member['uid'], array('code'=>401, 'message'=>'请先登录！'));
		
		$data = $this->logic->get_tips($this->cur_member['uid']);

		$this->render_json(array('code'=>200, 'data'=>$data));
	}


	/**
	 * @api {post} /index.php?mod=signin&action=sign 签到
	 * @apiName sign
	 * @apiGroup Signin
	 * @apiVersion 2.0.0
	 * @apiDescription 签到
	 * @apiPermission Logined
	 * @apiSuccess (response) {Number} code success 200
	 * @apiSuccess (response) {json} message 提示信息
	 * @apiSuccessExample {json} Response 200
	 *
	 *{
	 * "code": 200,
	 * "message": "ok"
	 * "last_days": 10
	 *}
	 * 
	*/
	public function sign()
	{
		$result = self::$WEB_SUCCESS_RT;

		try{
			MzException::throw_exception(!$this->cur_member['uid'], array('code'=>401, 'message'=>'请先登录！'));

			$last_days           = null;
			$msg                 = $this->logic->sign($last_days);
			$result['message']   = $msg;
			$result['last_days'] = $last_days;       
        } catch (MzException $e) {
            $this->_log_data = $result = $e;
        }

        $this->render_json($result);	
	}

	/**
	 * @api {post} /index.php?mod=signin&action=resign 补签
	 * @apiName resign
	 * @apiGroup Signin
	 * @apiVersion 2.0.0
	 * @apiDescription 补签
	 * @apiPermission Logined
	 * @apiParam {String} date 补签日期	
	 * @apiSuccess (response) {Int} code success 200
	 * @apiSuccess (response) {String} message 提示信息
	 * @apiSuccessExample {json} Response 200
	 *
	 *{
	 * "code": 200,
	 * "message": "ok"
	 *}
	 * 
	*/
	public function resign()
	{
		$result = self::$WEB_SUCCESS_RT;
		try{
			$date = $this->get_param('date');
			MzException::throw_exception(!$date, array('code'=>400, 'message'=>'参数错误！'));
			MzException::throw_exception(!$this->cur_member['uid'], array('code'=>401, 'message'=>'请先登录！'));

			$last_days = null;
			$msg       = $this->logic->resign($this->cur_member['uid'], $date, $last_days);
			$result['message']   = $msg;
			$result['last_days'] = $last_days;
        } catch (MzException $e) {
            $this->_log_data = $result = $e;
        }

        $this->render_json($result);	
	}

	/**
	 * @api {get} /index.php?mod=signin&action=signed_list 签到列表
	 * @apiName signedlist
	 * @apiGroup Signin
	 * @apiVersion 2.0.0
	 * @apiDescription 签到列表
	 * @apiPermission Logined
	 * @apiSuccess (response) {Number} code success 200
	 * @apiSuccess (response) {json} data 详细数据
	 * @apiSuccessExample {json} Response 200
	 *
	 *{
	 * "code": 200,
	 * "data": {
	 * "list": [
	 * {
	 *  "date": "2017-03-01",  //日期
	 *  "signed": 0,           //是否已签到
	 *  "need": "30煤球"       //补签需要煤球数
	 * }
	 * ],
	 * }
	 *}
	 * 
	*/
	public function signed_list()
	{
		$result = self::$WEB_SUCCESS_RT;
		try{
			$from_date = $this->get_param('from_date', 0);

			MzException::throw_exception(!$this->cur_member['uid'], array('code'=>401, 'message'=>'请先登录！'));
			
			$result['data']         = array();
			$result['data']['list'] = $this->logic->get_signed_list($this->cur_member['uid'], $from_date);
			
        } catch (MzException $e) {
            $this->_log_data = $result = $e;
        }

		$this->render_json($result);
	}

	public function after_action($controller, $data = array())
	{
		$data = $this->_log_data;
		if($data instanceof Exception){
			$data = array(
				'code'    => $data->getCode(),
				'message' => $data->getMessage(),
			);
		}		
		$this->logic->save_log($data);
	}


}
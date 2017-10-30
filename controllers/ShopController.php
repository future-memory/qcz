<?php

class ShopController extends BaseController 
{
	public $_my_limit     = 40;
	public $order_status_array = array(
		9 => '未审核',	
		1 => '审核通过', 
		2 => '审核不通过', 
		3 => '已发货', 
		4 => '已收货',
		5 => '已完成',
		8 => '已取消', 
	);

    public function __construct()
    {
        $this->logic = ObjectCreater::create('ShopLogic');
    }

	/**
	 * @api {get} /index.php?mod=shop 兑换商城首页
	 * @apiName shopindex
	 * @apiGroup Shop
	 * @apiVersion 2.0.0
	 * @apiDescription 兑换商城首页
	 * 
	*/
	public function index()
	{
		//幻灯片
		$member  = ObjectCreater::create('MemberLogic')->get_current_member();
	
		$promote_data = ObjectCreater::create('MiscLogic')->get_data_list('shop_top_slide', 0, 20);
		include(APP_ROOT . '/template/shop/index.php');
	}

	/**
	 * @api {get} /index.php?mod=shop&action=address 收货地址页
	 * @apiName shopaddress
	 * @apiGroup Shop
	 * @apiVersion 2.0.0
	 * @apiDescription 收货地址页
	 * 
	*/
	public function address()
	{
		//幻灯片
		include(APP_ROOT . '/template/shop/address.php');
	}

	/**
	 * @api {get} /index.php?mod=shop&action=view&id=xxx 商品详情页
	 * @apiName shopview
	 * @apiGroup Shop
	 * @apiVersion 2.0.0
	 * @apiDescription 商品详情页
	 * 
	*/
	public function view()
	{
		$id  = (int)$this->get_param('id', 0);

		$member  = ObjectCreater::create('MemberLogic')->get_current_member();
		$count   = $member['uid'] ? ObjectCreater::create('MemberLogic')->get_member_count($member['uid']) : array();
		$user_mb = isset($count['extcredits3']) ? intval($count['extcredits3']) : 0;
		$info    = $this->logic->get_goods_info($id);

		if(!$info['is_online']){
			header("location: /index.php?mod=shop");
			exit();
		}

		include(APP_ROOT . '/template/shop/view.php');
	}


	/**
	 * @api {get} /index.php?mod=shop&action=detail&id=xxx 商品详情
	 * @apiName shopdetial
	 * @apiGroup Shop
	 * @apiVersion 2.0.0
	 * @apiDescription 商品详情
	 * 
	*/
	public function detail()
	{
		$id = (int)$this->get_param('id', 0);
		$this->throw_error(!$id, array('code'=>400, 'message'=>'参数错误'));

		$info = $this->logic->get_goods_info($id);

		$this->render_json(array('code'=>200, 'data'=>$info));	
	}

	/**
	 * @api {get} /index.php?mod=shop&action=goods_list 商品列表
	 * @apiName goodslist
	 * @apiGroup Shop
	 * @apiVersion 2.0.0
	 * @apiDescription 商品列表
	 * @apiParam {Number} type 类型
	 * @apiUse PAGE_PARAM
	 * @apiUse RES_DATA
	 * @apiSuccessExample {json} Response 200
	 *
	 *{
	 * "code": 200,
	 * "data": {
	 * "list": [
	 * {
	 *  "id": "8",
	 *  "name": "笔记本",
	 *  "price": "2599",
	 *  "orig_price": "0",
	 *  "count": "6",
	 *  "cover_pic": "https://test.com/shop/182413j8kj9qk1odufrhb5.jpg",
	 * },
	 * ],
	 * "last_page": true
	 * }
	 *}
	 * 
	*/
	public function goods_list()
	{
		$type        = (int)$this->get_param('type', 0);
		$page        = (int)$this->get_param('page', 1);
		$limit       = 10;

		$start       = $page > 1 ? ($page-1) * $limit : 0;
		$price_range = $this->get_param('price_range');
		$price_array = explode('-', $price_range);
		$price_start = isset($price_array[0]) ? intval($price_array[0]) : 0;
		$price_end   = isset($price_array[1]) ? intval($price_array[1]) : 0;

		$list        = $this->logic->get_goods_list($type, $price_start, $price_end, $start, $limit);

		$last_page   = count($list) < $limit;

		$this->render_json(array('code'=>200, 'data'=>array('list'=>$list, 'last_page'=>$last_page)));		
	}

	/**
	 * @api {get} /index.php?mod=shop&action=goods_info&ids=1,3,8 商品信息
	 * @apiName goodsinfo
	 * @apiGroup Shop
	 * @apiVersion 2.0.0
	 * @apiDescription 商品信息
	 * @apiParam {Sting} ids 商品id，多个使用逗号分隔
	 * @apiUse RES_DATA
	 * @apiSuccessExample {json} Response 200
	 *
	 *{
	 * "code": 200,
	 * "data": {
	 * "list": [
	 * {
	 *  "id": "8",
	 *  "name": "笔记本",
	 *  "price": "2599",
	 *  "orig_price": "0",
	 *  "count": "6",
	 *  "cover_pic": "https://test.com/shop/182413j8kj9qk1odufrhb5.jpg",
	 * },
	 * ],
	 * }
	 *}
	 * 
	*/
	public function goods_info()
	{
		$ids  = trim($this->get_param('ids'));
		$this->throw_error(!$ids, array('code'=>400, 'message'=>'参数错误'));	

		$ids  = is_array($ids) ? $ids : ($ids ? explode(',', $ids) : array());
		$ids  = array_map("intval", $ids);

		$list = $this->logic->get_goods_infos($ids);

		$this->render_json(array('code'=>200, 'data'=>array('list'=>$list)));		
	}

	/**
	 * @api {get} /index.php?mod=shop&action=goods_types 商品类型列表
	 * @apiName goodstypes
	 * @apiGroup Shop
	 * @apiVersion 2.0.0
	 * @apiDescription 商品类型列表
	 * @apiUse PAGE_PARAM
	 * @apiSuccess (response) {Number} code success 200
	 * @apiSuccess (response) {json} data 
	 * @apiSuccessExample {json} Response 200
	 *
	 *{
	 * "code": 200,
	 * "data": {
	 * "list": [
	 * {
	 *   "id": "1",
	 *   "name": "礼品兑换"
	 * },
	 * ]
	 * }
	 *}
	 * 
	*/
	public function goods_types()
	{
		$page  = (int)$this->get_param('page', 1);
		$limit = 10;
		$start = $page > 1 ? ($page-1) * $limit : 0;

		$list  = $this->logic->get_goods_type_list($start, $limit);

		$this->render_json(array('code'=>200, 'data'=>array('list'=>$list)));		
	}	

	/**
	 * @api {get} /index.php?mod=shop&action=goods_count 获取商品库存
	 * @apiName goodscount
	 * @apiGroup Shop
	 * @apiVersion 2.0.0
	 * @apiDescription 获取商品库存
	 * @apiParam {Number} id   商品id
	 * @apiSuccess (response) {Number} code success 200
	 * @apiSuccess (response) {json} data 
	 * @apiSuccessExample {json} Response 200
	 *
	 *{
	 * "code": 200,
	 * "data": {
	 * "count": "49"
	 * }
	 *}
	 * 
	*/
	public function goods_count()
	{
		$id  = (int)$this->get_param('id');
		$info = $this->logic->fetch($id);

		$this->render_json(array('code'=>200, 'data'=>array('count'=>$info['count'])));	
	}

	/**
	 * @api {post} /index.php?mod=shop&action=submit_order 提交订单
	 * @apiName submitorder
	 * @apiGroup Shop
	 * @apiVersion 2.0.0
	 * @apiDescription 提交订单
	 * @apiPermission Logined
	 * @apiParam {Number} goods_list 商品列表，数组，格式如 [{'id': 7, 'count': 1}]
	 * @apiParam {Number} name   姓名		 
	 * @apiParam {String} phone  电话
	 * @apiParam {String} address  地址
	 * @apiParam {String} belong  归属，0社区 2lifekit
	 * @apiSuccess (response) {Number} code success 200
	 * @apiSuccess (response) {json} message 提示信息
	 * @apiSuccessExample {json} Response 200
	 *
	 *{
	 * "code": 200,
	 * "message": "ok"
	 *}
	 * 
	*/
	public function submit_order()
	{
		$member = ObjectCreater::create('MemberLogic')->get_current_member();
		//判断是否登录
		$this->throw_error(!$member['uid'], array('code'=>401, 'message'=>'请先登录！'));	

		//参数获取及校验
		$goods_list = $this->get_param('goods_list');
		$goods_list = is_array($goods_list) ? $goods_list : @json_decode($goods_list, true);
		
		$name       = htmlspecialchars($this->get_param('name'));
		$phone      = htmlspecialchars($this->get_param('phone'));	
		$address    = htmlspecialchars($this->get_param('address'));

		$this->throw_error(!$name || !$phone || !$address, array('code'=>400, 'message'=>'参数错误！'));
		$this->throw_error(strlen($name)>60, array('code'=>408, 'message'=>'您输入的姓名超出长度限制！'));
		$this->throw_error(strlen($address)>490, array('code'=>406, 'message'=>'您输入的地址超出长度限制！'));

		$goods_cnt = $goods_ids = array();
		foreach($goods_list as $goods){
			$goods['count'] = intval($goods['count']);
			if(isset($goods['id']) && $goods['id'] && $goods['count']>0){
				$goods['id']             = intval($goods['id']);
				$goods_ids[]             = intval($goods['id']);
				$goods_cnt[$goods['id']] = $goods['count'];				
			}
		}
		$this->throw_error(empty($goods_ids), array('code'=>409, 'message'=>'参数错误！'));

		//提交
		$this->logic->submit_order($goods_ids, $goods_cnt, $name, $phone, $address, $belong);

		$this->render_json(array('code'=>200, 'message'=>'订单提交成功！'));		
	}


	/**
	 * @api {post} /index.php?mod=shop&action=update_address 更新常用地址
	 * @apiName updateaddress
	 * @apiGroup Shop
	 * @apiVersion 2.0.0
	 * @apiDescription 更新常用地址
	 * @apiPermission Logined
	 * @apiParam {Number} name   姓名		 
	 * @apiParam {String} phone  电话
	 * @apiParam {String} address  地址
	 * @apiSuccess (response) {Number} code success 200
	 * @apiSuccess (response) {json} message 提示信息
	 * @apiSuccessExample {json} Response 200
	 *
	 *{
	 * "code": 200,
	 * "message": "ok"
	 *}
	 * 
	*/
	public function update_address()
	{
		$member = ObjectCreater::create('MemberLogic')->get_current_member();
		$member['uid'] = isset($member['uid']) ? floatval($member['uid']) : 0;
		//判断是否登录
		$this->throw_error(!$member['uid'], array('code'=>401, 'message'=>'请先登录！'));	

		//判断来路
		$this->throw_error(!isset($_SERVER["HTTP_REFERER"]) || !HelperBiz::check_redirect_url($_SERVER["HTTP_REFERER"]), array('code'=>400, 'message'=>'来源地址不明确，请联系论坛管理人员！'));
		
		$name    = htmlspecialchars($this->get_param('name'));
		$phone   = htmlspecialchars($this->get_param('phone'));	
		$address = htmlspecialchars($this->get_param('address'));

		$this->throw_error(!$name || !$phone || !$address, array('code'=>400, 'message'=>'参数错误！'));
		$this->throw_error(strlen($name)>49, array('code'=>406, 'message'=>'您输入的姓名超出长度限制！'));
		$this->throw_error(strlen($address)>490, array('code'=>407, 'message'=>'您输入的地址超出长度限制！'));

		$this->logic->update_address($name, $phone, $address);

		$this->render_json(array('code'=>200, 'message'=>'保存成功！'));
	}

	/**
	 * @api {get} /index.php?mod=shop&action=get_address 获取地址
	 * @apiName getaddress
	 * @apiGroup Shop
	 * @apiVersion 2.0.0
	 * @apiDescription 获取地址
	 * @apiPermission Logined
	 * @apiSuccess (response) {Number} code success 200
	 * @apiSuccess (response) {json} data 
	 * @apiSuccessExample {json} Response 200
	 *
	 *{
	 * "code": 200,
	 * "data": {
	 *   "id": "6631",
	 *   "uid": "7680184",
	 *   "name": "fasdf",
	 *   "address": "adfaf",
	 *   "phone": "18677876787",
	 *   "dateline": "0"
	 * }
	 *}
	 * 
	*/
	public function get_address()
	{
		$member = ObjectCreater::create('MemberLogic')->get_current_member();
		$member['uid'] = isset($member['uid']) ? floatval($member['uid']) : 0;
		//判断是否登录
		$this->throw_error(!$member['uid'], array('code'=>401, 'message'=>'请先登录！'));	

		$data = $this->logic->get_address($member['uid']);

		$this->render_json(array('code'=>200, 'data'=>$data));
	}

	/**
	 * @api {post} /index.php?mod=shop&action=cancel_order 取消订单
	 * @apiName cancelorder
	 * @apiGroup Shop
	 * @apiVersion 2.0.0
	 * @apiDescription 取消订单
	 * @apiPermission Logined
	 * @apiParam {Number} id   订单id		 
	 * @apiParam {String} page  『我的订单』中的页码数，用于清除缓存
	 * @apiSuccess (response) {Number} code success 200
	 * @apiSuccess (response) {json} message 提示信息
	 * @apiSuccessExample {json} Response 200
	 *
	 *{
	 * "code": 200,
	 * "message": "ok"
	 *}
	 * 
	*/
	public function cancel_order()
	{
		$member = ObjectCreater::create('MemberLogic')->get_current_member();
		$member['uid'] = isset($member['uid']) ? floatval($member['uid']) : 0;
		//判断是否登录
		$this->throw_error(!$member['uid'], array('code'=>401, 'message'=>'请先登录！'));	

		$id   = (int)$this->get_param('id', 0);
		$page = (int)$this->get_param('page', 1);
		$this->throw_error(!$id, array('code'=>400, 'message'=>'参数错误！'));	

		//提交
		$this->logic->submit_order($id, $page);
		
		$this->render_json(array('code'=>200, 'message'=>'取消成功！'));
	}

	/**
	 * @api {post} /index.php?mod=shop&action=taken_order 确认收货
	 * @apiName takenorder
	 * @apiGroup Shop
	 * @apiVersion 2.0.0
	 * @apiDescription 确认收货
	 * @apiPermission Logined
	 * @apiParam {Number} id   订单id		 
	 * @apiParam {String} page  『我的订单』中的页码数，用于清除缓存
	 * @apiSuccess (response) {Number} code success 200
	 * @apiSuccess (response) {json} message 提示信息
	 * @apiSuccessExample {json} Response 200
	 *
	 *{
	 * "code": 200,
	 * "message": "ok"
	 *}
	 * 
	*/
	public function taken_order()
	{
		$member = ObjectCreater::create('MemberLogic')->get_current_member();
		//判断是否登录
		$this->throw_error(!$member['uid'], array('code'=>401, 'message'=>'请先登录！'));	

		$id   = (int)$this->get_param('id', 0);
		$page = (int)$this->get_param('page', 1);
		$this->throw_error(!$id, array('code'=>400, 'message'=>'参数错误！'));	

		$this->logic->taken_order($id);
		
		$this->render_json(array('code'=>200, 'message'=>'操作成功！'));
	}

	/**
	 * @api {get} /index.php?mod=shop&action=my 我的订单列表
	 * @apiName myorder
	 * @apiGroup Shop
	 * @apiVersion 2.0.0
	 * @apiDescription 我的订单列表
	 * @apiPermission Logined
	 * @apiUse PAGE_PARAM
	 * @apiSuccess (response) {Number} code success 200
	 * @apiSuccess (response) {json} data 
	 * @apiSuccessExample {json} Response 200
	 *
	 *{
	 * "code": 200,
	 * "data": {
	 *  "list": [
	 *  {
	 *   "id": "1675",
	 *   "uid": "7680184",
	 *   "dateline": "1458099212",
	 *   "address_id": "0",
	 *   "amount": "1",
	 *   "status": null,
	 *   "cancel_time": "0",
	 *   "auditor": "0",
	 *   "audit_time": "0",
	 *   "delivery_time": "0",
	 *   "deliver": null,
	 *   "delivery_sn": null,
	 *   "name": "chen",
	 *   "address": "dizhi",
	 *   "phone": "18622363265",
	 *   "goods_name": "",
	 *   "price": "0",
	 *   "count": "0",
	 *   "belong": "0",
	 *   "goods": [
	 *   {
	 *    "name": "青年良品帆布袋",
	 *    "order_id": "1675",
	 *    "goods_id": "13",
	 *    "cover_pic": "https://test.com/shop/183025x3qlwthdftqtqtrz.jpg",
	 *    "price": "1",
	 *    "count": "1"
	 *   }
	 *   ]
	 *  },
	 * }
	 *}
	 * 
	*/
	public function my()
	{
		$member = ObjectCreater::create('MemberLogic')->get_current_member();
		//判断是否登录
		$this->throw_error(!$member['uid'], array('code'=>401, 'message'=>'请先登录！'));	

		$page  = (int)$this->get_param('page', 1);
		$limit = 20;
		$start = $page > 1 ? ($page-1) * $limit : 0;
		
		$list  = $this->logic->get_my_orders($start, $limit);

		$last_page = count($list) < $limit;

		$this->render_json(array('code'=>200, 'data'=>array('list'=>$list, 'last_page'=>$last_page)));
	}

	/**
	 * @api {get} /index.php?mod=shop&action=mq 我的煤球数
	 * @apiName mymq
	 * @apiGroup Shop
	 * @apiVersion 2.0.0
	 * @apiDescription 我的煤球数
	 * @apiPermission Logined
	 * @apiSuccess (response) {Number} code success 200
	 * @apiSuccess (response) {json} data 
	 * @apiSuccessExample {json} Response 200
	 *
	 *{
	 * "code": 200,
	 * "data": {
	 * "mq": "111000"
	 * }
	 *}
	 * 
	*/
	public function mq()
	{
		$member = ObjectCreater::create('MemberLogic')->get_current_member();
		//判断是否登录
		$this->throw_error(!$member['uid'], array('code'=>401, 'message'=>'请先登录！'));	
		
		$callback   = $this->get_param('callback');
		
		$count =  ObjectCreater::create('MemberLogic')->get_member_count($member['uid']);
		$mq    = $count['extcredits3']>0 ? $count['extcredits3'] : 0;
		$data  = array('code'=>200, 'data'=>array('mq'=>$mq));
		
		$callback ? $this->render_jsonp($data, $callback) : $this->render_json($data);
	}

	/**
	 * @api {get} /index.php?mod=shop&action=mycoin 我的煤球页
	 * @apiName mycoin
	 * @apiGroup Shop
	 * @apiVersion 2.0.0
	 * @apiDescription 我的煤球页面
	 * @apiPermission Logined
	 * 
	*/
	public function mycoin()
	{
		$member = ObjectCreater::create('MemberLogic')->get_current_member();
		//判断是否登录
		$this->throw_error(!$member['uid'], array('code'=>401, 'message'=>'请先登录！'));	

		$count =  ObjectCreater::create('MemberLogic')->get_member_count($member['uid']);
		$mq    = $count['extcredits3']>0 ? $count['extcredits3'] : 0;

		include(APP_ROOT . '/template/shop/mycoin.php');
	}

	/**
	 * @api {get} /index.php?mod=shop&action=mycoin_list 煤球记录
	 * @apiName mycoinlist
	 * @apiGroup Shop
	 * @apiVersion 1.0.0
	 * @apiDescription 我的煤球记录
	 * @apiPermission Logined
	 * @apiUse PAGE_PARAM
	 * @apiUse RES_DATA
	 * @apiSuccessExample {json} Response 200
	 * 
	 *{
	 *  "code": 200, 
	 *  "date": {
	 *    "list": [
	 *      {
	 *        "mq": 1, 
	 *        "timestamp": 1489737061, 
	 *        "description": "帖子被评分"
	 *      }
	 *    ], 
	 *    "last_page": false
	 *  }
	 *}
	 */
	public function mycoin_list() {
		$member = ObjectCreater::create('MemberLogic')->get_current_member();
		//判断是否登录
		$this->throw_error(!$member['uid'], array('code'=>401, 'message'=>'请先登录！'));	
		$page  = (int)$this->get_param('page', 1);
		$limit = 20;
		$start = $page > 1 ? ($page - 1) * $limit : 0;		

		$result = ObjectCreater::create('CreditLogic')->get_mq_list_by_uid($member['uid'], $start, $limit);
		$this->render_json(array(
			'code' => 200,
			'date' => $result,
			));
	}

	/**
	 * @api {get} /index.php?mod=shop&action=myorders 我的订单页
	 * @apiName myorders
	 * @apiGroup Shop
	 * @apiVersion 2.0.0
	 * @apiDescription 我的订单页面
	 * @apiPermission Logined
	 * 
	*/
	public function myorders()
	{
		$member = ObjectCreater::create('MemberLogic')->get_current_member();
		//判断是否登录
		$this->throw_error(!$member['uid'], array('code'=>401, 'message'=>'请先登录！'));	

		$page  = (int)$this->get_param('page', 1);
		$limit = 20;
		$start = $page > 1 ? ($page-1) * $limit : 0;
		
		$list  = $this->logic->get_my_orders($start, $limit);

		include(APP_ROOT . '/template/shop/myorders.php');
	}

}
<?php
class ShopLogic extends Logic
{
	//我的订单 每页显示数
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
		$this->_dao = ObjectCreater::create('ShopGoodsDao');
	}

	//获取用户地址
	public function get_address()
	{
        $member = ObjectCreater::create('MemberLogic')->get_current_member();

        $this->throw_exception(!$member['uid'], array('code'=>401, 'message'=>'请先登录！'));

        return ObjectCreater::create('ShopUserAddressDao')->fetch($member['uid']);
	}

	//更新用户地址
	public function update_address($name, $phone, $address)
	{
		$member = ObjectCreater::create('MemberLogic')->get_current_member();
		$this->throw_exception(!$member['uid'], array('code'=>401, 'message'=>'请先登录！'));

		$data = array(
			'uid'     => $member['uid'],   
			'name'    => $name,    
			'phone'   => $phone,  
			'address' => $address 
		);

		return ObjectCreater::create('ShopUserAddressDao')->insert_or_update($data);		
	}	

	//获取单个商品信息 
	public function get_goods_info($goods_id)
	{
		$info         = $this->_dao->fetch($goods_id);
		//$info['cover_pic'] = HelperUtils::get_pic_url($info['cover_pic'], 'shop');
		$info['pics'] = array(HelperUtils::get_pic_url($info['goods_pic'], 'shop'));

		return $info;		
	}

	//获取多个商品信息 
	public function get_goods_infos($goods_ids)
	{
		$list = $this->_dao->fetch_all($goods_ids);
		$tmp = array();		
		foreach($list as $k=>$v){
			$pic   = HelperUtils::get_pic_url($v['cover_pic'], 'shop');
			$tmp[] = array(
				'id'          => $v['id'],
				'name'        => $v['name'],
				'price'       => $v['price'],
				'orig_price'  => $v['orig_price'],
				'count'       => $v['count'],
				'cover_pic'   => $pic,
			);
		}

		return $tmp;		
	}

	//获取商品列表
	public function get_goods_list($type, $price_start, $price_end, $start, $limit)
	{
		$list  = $this->_dao->get_list($type, $price_start, $price_end, $start, $limit);

		$tmp = array();		
		foreach($list as $k=>$v){
			$pic   = HelperUtils::get_pic_url($v['cover_pic'], 'shop');
			$tmp[] = array(
				'id'          => $v['id'],
				'name'        => $v['name'],
				'price'       => $v['price'],
				'orig_price'  => $v['orig_price'],
				'count'       => $v['count'],
				'cover_pic'   => $pic,
			);
		}

		return $tmp;		
	}

	//获取商品类型列表
	public function get_goods_type_list($start, $limit)
	{
		$list  = ObjectCreater::create('ShopGoodsTypeDao')->range($start, $limit);
		$list  = array_values($list);
		return $list;	
	}

	//提交订单
	public function submit_order($goods_ids, $goods_cnt, $name, $phone, $address)
	{
		$amount = 0;
		$member = ObjectCreater::create('MemberLogic')->get_current_member();
		$this->throw_exception(!$member['uid'], array('code'=>401, 'message'=>'请先登录！'));	

		$goods_count = array();
		$goods_info_list = $this->_dao->fetch_all($goods_ids);

		foreach($goods_info_list as $goods_info){
			$this->throw_exception($goods_info['limit'] && $goods_cnt[$goods_info['id']]>$goods_info['limit'], array('code'=>407, 'message'=>'商品兑换数量大于限兑数！'));
			$this->throw_exception($goods_cnt[$goods_info['id']]>$goods_info['count'], array('code'=>408, 'message'=>'商品库存不足！'));
			$this->throw_exception(!$goods_info['is_online'], array('code'=>406, 'message'=>'该商品已下架！'));
			$goods_info['price'] = isset($goods_info['app_price']) && $goods_info['app_price'] ? $goods_info['app_price'] : $goods_info['price'];

			$amount += $goods_info['price'] * $goods_cnt[$goods_info['id']];

			$goods_count[$goods_info['id']] = $goods_cnt[$goods_info['id']];
		}

		//判断煤球数  从数据库里取 这里不能信任缓存
		$user_count = ObjectCreater::create('MemberLogic')->get_member_count($member['uid'], $forcedb=true);
		$this->throw_exception($amount>$user_count['extcredits3'], array('code'=>403, 'message'=>'煤球数量不足，还不能兑换哦！'));

		//事务
		ObjectCreater::create('ShopOrderDao')->begin();
		//保存地址
		$data = array(
			'uid'     => $member['uid'],   
			'name'    => $name,    
			'phone'   => $phone,  
			'address' => $address 
		);
		$res = ObjectCreater::create('ShopUserAddressDao')->insert_or_update($data);
		//保存订单信息
		$data = array(
			'uid'        => $member['uid'],   
			'amount'     => $amount,  
			'name'       => $name,    
			'phone'      => $phone,  
			'address'    => $address,
			'goods_name' => '',//$goods_info['name'],
			'price'      => '',//$goods_info['price'],
			'count'      => 0,//$count,
			'dateline'   => TIMESTAMP 
		);

		$order_id = ObjectCreater::create('ShopOrderDao')->insert($data, true);

		//保存商品信息
		$ret      = true;
		if($order_id){
			foreach($goods_count as $goods_id=>$count){
				$goods_info_list[$goods_id]['price'] = isset($goods_info_list[$goods_id]['app_price']) && $goods_info_list[$goods_id]['app_price'] ? $goods_info_list[$goods_id]['app_price'] : $goods_info_list[$goods_id]['price'];
				$data = array(
					'order_id' => $order_id,
					'goods_id' => $goods_id,
					'price'    => $goods_info_list[$goods_id]['price'],
					'count'    => $count,
					'extra_flag'=>($goods_info_list[$goods_id]['just_for_app'] ? 2 : ($goods_info_list[$goods_id]['app_price'] ? 1 : 0))
				);
				$ret = ObjectCreater::create('ShopOrderGoodsDao')->insert($data);
				if(!$ret){
					break;
				}
			}
		}

		//扣煤球
		$res2 = true;
		if($amount>0){
			$pay  = -1 * abs($amount);
			$res2 = ObjectCreater::create('MemberLogic')->send_mq($member['uid'], $pay, 'SHP', $order_id);

		}

		//减库存
		$res3 = true;
		foreach($goods_cnt as $gid=>$cnt){
			$cnt = -1 * abs($cnt);
			$tmp = $this->_dao->increase_goods_count($gid, $cnt);
			if($tmp==false){
				$res3 = false;
			}
		}

		if($res && $order_id && $ret && $res2 && $res3){
			ObjectCreater::create('ShopOrderDao')->commit();
			//删除缓存
			ObjectCreater::create('ShopOrderDao')->del_user_order_cache($member['uid']);			
		}else{
			ObjectCreater::create('ShopOrderDao')->rollback();
			$this->throw_exception(true, array('code'=>501, 'message'=>'订单保存失败，请稍后再试！'));
		}

		return true;		
	}

	//取消订单
	public function cancel_order($id, $page)
	{
		$member = ObjectCreater::create('MemberLogic')->get_current_member();
		$this->throw_exception(!$member['uid'], array('code'=>401, 'message'=>'请先登录！'));	

		$info = ObjectCreater::create('ShopOrderDao')->fetch($id);
		$this->throw_exception(empty($info), array('code'=>404, 'message'=>'参数错误！'));
		$this->throw_exception(floatval($info['uid'])!==$member['uid'], array('code'=>403, 'message'=>'您无权取消该兑换单！'));	

		$info['status'] = intval($info['status']);
		$this->throw_exception($info['status']!==9, array('code'=>405, 'message'=>'该兑换单不能取消！'));

		$data = array('status'=>8, 'cancel_time'=>TIMESTAMP);

		//未进行退煤球和恢复库存的
		if($info['status']===9){
			ObjectCreater::create('ShopOrderDao')->begin();
			//退煤球 --为什么用if：有可能0煤球
			$res = true;
			if($info['amount']){
				$res = ObjectCreater::create('MemberLogic')->send_mq($member['uid'],$info['amount'],'SOC');
			}
			
			//恢复库存
			$goods_list = ObjectCreater::create('ShopOrderGoodsDao')->get_list_by_orderid($id);
			$res2       = true;
			foreach($goods_list as $goods){
				$res_tmp = $this->_dao->increase_goods_count($goods['goods_id'], $goods['count']);
				if(!$res_tmp){
					$res2 = false;
				}
			}
			//更新状态
			$res3 = ObjectCreater::create('ShopOrderDao')->update($id, $data);
			if($res3 && $res2 && $res){
				ObjectCreater::create('ShopOrderDao')->commit();
				$start = $page>1 ? ($page - 1) * $this->_my_limit : 0;
				ObjectCreater::create('ShopOrderDao')->del_user_order_cache($info['uid'], $start);
			}else{
				ObjectCreater::create('ShopOrderDao')->rollback();
				$this->throw_exception(true, array('code'=>501, 'message'=>'订单取消失败，请稍后再试！'));
			}
		}		
	}

	//完成订单
	public function taken_order($id)
	{
		$info = ObjectCreater::create('ShopOrderDao')->fetch($id);
		$member = ObjectCreater::create('MemberLogic')->get_current_member();

		$this->throw_error(empty($info), array('code'=>404, 'message'=>'参数错误！'));
		$this->throw_error(intval($info['uid'])!==$member['uid'], array('code'=>403, 'message'=>'您无权操作该兑换单！'));

		$info['status'] = intval($info['status']);
		$this->throw_error($info['status']!==3, array('code'=>405, 'message'=>'兑换单状态操作错误！'));

		$data = array('status'=>4, 'cancel_time'=>TIMESTAMP);

		//未进行退煤球和恢复库存的
		if($info['status']===3){
			//更新状态
			$res = ObjectCreater::create('ShopOrderDao')->update($id, $data);
			if($res){
				$start = $page>1 ? ($page - 1) * $this->_my_limit : 0;
				ObjectCreater::create('ShopOrderDao')->del_user_order_cache($info['uid'], $start);
				return true;
			}

			$this->throw_error(!$res, array('code'=>500, 'message'=>'操作失败！'));
		}		
	}

	//获取我的订单
	public function get_my_orders($start, $limit)
	{
		$member = ObjectCreater::create('MemberLogic')->get_current_member();
		$list   = ObjectCreater::create('ShopOrderDao')->get_list_by_uid($member['uid'], $start, $limit);
		
		$orderids = array();
		foreach ($list as $item) {
			$orderids[] = $item['id'];	
		}
		$order_goods = ObjectCreater::create('ShopOrderGoodsDao')->get_list_by_orderids($orderids);

		$goods_list  = array();
		foreach($order_goods as $goods){
			$goods_list[$goods['order_id']]   = isset($goods_list[$goods['order_id']]) ? $goods_list[$goods['order_id']] : array();
			$goods['cover_pic']               = HelperUtils::get_pic_url($goods['cover_pic'], 'shop');
			$goods_list[$goods['order_id']][] = $goods;
		}

		foreach ($list as $key=>$value) {
			$list[$key]['goods'] = $goods_list[$value['id']];
			$list[$key]['status'] = $this->order_status_array[$value['status']];
		}

		return $list;		
	}

}